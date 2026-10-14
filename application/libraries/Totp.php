<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use Mpdf\QrCode\Output\Png;
use Mpdf\QrCode\QrCode;
use RobThree\Auth\Algorithm;
use RobThree\Auth\Providers\Qr\IQRCodeProvider;
use RobThree\Auth\TwoFactorAuth;

/**
 * Autenticação em dois fatores por TOTP (RFC 6238), opcional por usuário (#2876).
 *
 *     $segredo = $this->totp->criarSegredo();
 *     $png = $this->totp->qrCodeDataUri($email, $segredo);          // QR gerado aqui, sem serviço externo
 *     $passo = $this->totp->verificar($segredo, $codigo, $ultimoPasso);  // int da janela aceita, ou null
 *     $cifrado = $this->totp->cifrar($segredo);                     // o que vai para usuarios.totp_secreto
 *     $codigos = $this->totp->gerarCodigosDeRecuperacao();         // 10 códigos, mostrados uma vez
 *
 * O segredo nunca sai do servidor: o QR é desenhado com o mpdf/qrcode, que já
 * é dependência do projeto, e o segredo é guardado cifrado (AES-256-GCM, chave
 * derivada da encryption_key). O código só vale uma vez: verificar() recusa
 * uma janela de 30 s igual ou anterior à última aceita.
 */
class Totp
{
    public const QUANTIDADE_CODIGOS = 10;

    private const DISCREPANCIA = 1;

    private ?TwoFactorAuth $tfa = null;

    private string $chave;

    private string $emissor;

    /**
     * @param  array{chave?: string, emissor?: string}|null  $opcoes  Para os testes.
     */
    public function __construct($opcoes = null)
    {
        $opcoes = is_array($opcoes) ? $opcoes : [];

        $segredoDaInstalacao = (string) ($opcoes['chave'] ?? config_item('encryption_key'));

        // Com a chave do .env.example (pública) o segredo cifrado seria legível.
        if (! isset($opcoes['chave']) && defined('ENVIRONMENT') && ENVIRONMENT === 'production' && chaveInsegura($segredoDaInstalacao, 'enter_encryption_key')) {
            throw new RuntimeException('APP_ENCRYPTION_KEY não foi configurada: o 2FA não pode guardar segredos com a chave do .env.example.');
        }

        $this->chave = hash_hkdf('sha256', $segredoDaInstalacao, 32, 'mapos-totp-v1');
        $this->emissor = (string) ($opcoes['emissor'] ?? (config_item('app_name') ?: 'Map-OS'));
    }

    public function criarSegredo(): string
    {
        return $this->tfa()->createSecret(160);
    }

    /** Segredo em grupos de 4 letras, para digitar à mão no aplicativo. */
    public static function formatarSegredo(string $segredo): string
    {
        return trim(chunk_split($segredo, 4, ' '));
    }

    /** URI otpauth:// que o aplicativo autenticador lê. */
    public function uri(string $conta, string $segredo): string
    {
        return $this->tfa()->getQRText($conta, $segredo);
    }

    /** QR code em PNG (data URI), desenhado localmente. */
    public function qrCodeDataUri(string $conta, string $segredo, int $tamanho = 200): string
    {
        return $this->tfa()->getQRCodeImageAsDataUri($conta, $segredo, $tamanho);
    }

    /**
     * Confere o código de 6 dígitos (aceita 30 s antes e depois, por causa de
     * relógios um pouco fora).
     *
     * @return int|null o passo (janela de 30 s) aceito, para gravar; null se errado ou já usado
     */
    public function verificar(string $segredo, string $codigo, ?int $ultimoPasso = null, ?int $agora = null): ?int
    {
        $codigo = preg_replace('/\s+/', '', $codigo) ?? '';
        if (! preg_match('/^\d{6}$/', $codigo)) {
            return null;
        }

        $passo = 0;
        if (! $this->tfa()->verifyCode($segredo, $codigo, self::DISCREPANCIA, $agora, $passo) || $passo <= 0) {
            return null;
        }

        return $ultimoPasso !== null && $passo <= $ultimoPasso ? null : $passo;
    }

    /**
     * Confere o código de um usuário que está entrando: de recuperação (uso
     * único) ou do aplicativo (sem reuso da mesma janela de 30 s). Quem chama
     * conta a falha no Limite_login.
     *
     * @param  object  $modelo   Mapos_model (usarCodigoDeRecuperacao, registrarPassoTotp)
     * @param  object  $usuario  Linha de usuarios, com os campos totp_*
     */
    public function aceitar(object $modelo, object $usuario, string $codigo): bool
    {
        if (self::pareceCodigoDeRecuperacao($codigo)) {
            return $modelo->usarCodigoDeRecuperacao((int) $usuario->idUsuarios, self::hashDoCodigo($codigo));
        }

        $segredo = $this->decifrar($usuario->totp_secreto ?? null);
        if ($segredo === null) {
            return false;
        }

        $passo = $this->verificar($segredo, $codigo, ($usuario->totp_ultimo_passo ?? null) !== null ? (int) $usuario->totp_ultimo_passo : null);

        return $passo !== null && $modelo->registrarPassoTotp((int) $usuario->idUsuarios, $passo);
    }

    /** Cifra o segredo para o banco: base64(nonce . tag . texto cifrado). */
    public function cifrar(string $segredo): string
    {
        $nonce = random_bytes(12);
        $tag = '';
        $cifrado = openssl_encrypt($segredo, 'aes-256-gcm', $this->chave, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($cifrado === false) {
            throw new RuntimeException('Não foi possível cifrar o segredo do 2FA.');
        }

        return base64_encode($nonce . $tag . $cifrado);
    }

    /** Devolve o segredo, ou null se o valor estiver adulterado ou for de outra chave. */
    public function decifrar(?string $valor): ?string
    {
        $bruto = base64_decode((string) $valor, true);
        if ($bruto === false || strlen($bruto) < 29) {
            return null;
        }

        $segredo = openssl_decrypt(substr($bruto, 28), 'aes-256-gcm', $this->chave, OPENSSL_RAW_DATA, substr($bruto, 0, 12), substr($bruto, 12, 16));

        return $segredo === false ? null : $segredo;
    }

    /**
     * Códigos de recuperação, no formato abcd-efgh-ijkl (12 letras e números
     * sem os confundíveis).
     *
     * @return list<string>
     */
    public function gerarCodigosDeRecuperacao(int $quantidade = self::QUANTIDADE_CODIGOS): array
    {
        $alfabeto = 'abcdefghjkmnpqrstuvwxyz23456789';
        $codigos = [];

        while (count($codigos) < $quantidade) {
            $letras = '';
            for ($i = 0; $i < 12; $i++) {
                $letras .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
            $codigos[$letras] = implode('-', str_split($letras, 4));
        }

        return array_values($codigos);
    }

    /** Hash do código normalizado (minúsculas, sem traços e espaços). */
    public static function hashDoCodigo(string $codigo): string
    {
        return hash('sha256', self::normalizarCodigo($codigo));
    }

    public static function normalizarCodigo(string $codigo): string
    {
        return strtolower(preg_replace('/[\s\-]+/', '', $codigo) ?? '');
    }

    /** Diz se o texto tem a forma de um código de recuperação (12 letras e números). */
    public static function pareceCodigoDeRecuperacao(string $codigo): bool
    {
        return (bool) preg_match('/^[a-z2-9]{12}$/', self::normalizarCodigo($codigo));
    }

    private function tfa(): TwoFactorAuth
    {
        return $this->tfa ??= new TwoFactorAuth(new class() implements IQRCodeProvider {
            public function getQRCodeImage(string $qrText, int $size): string
            {
                return (new Png())->output(new QrCode($qrText, 'M'), $size, [255, 255, 255], [0, 0, 0], 9);
            }

            public function getMimeType(): string
            {
                return 'image/png';
            }
        }, $this->emissor, 6, 30, Algorithm::Sha1);
    }
}
