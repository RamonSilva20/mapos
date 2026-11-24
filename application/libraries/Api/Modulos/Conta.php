<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Usuario;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Schema;
use Libraries\Api\Upload;
use Limite_login;
use Totp;

final class Conta extends Modulo
{
    private const CONFIGURACAO_2FA = 'configuracao_2fa';

    public static function rotas(): array
    {
        $tag = 'Minha conta';
        $senha = Schema::senha()->obrigatorio()->max(200)->descricao('Senha atual da conta.');

        return [
            Rota::get('/conta', [self::class, 'ver'])->liberadaCom2faPendente()->tag($tag)
                ->resumo('Dados da conta logada', 'Traz o usuário, as permissões do grupo e a situação do 2FA. Use as permissões para mostrar ou esconder ações no app; o servidor confere de novo em cada rota.')
                ->dados(self::schemaConta()),

            Rota::put('/conta/senha', [self::class, 'senha'])->tag($tag)
                ->resumo('Troca a própria senha', 'Exige a senha atual, com o mesmo limite de tentativas do login. Encerra as outras sessões da conta (no painel e na API); a sessão desta chamada continua valendo.')
                ->corpo(Schema::objeto([
                    'senha_atual' => (clone $senha),
                    'nova_senha' => Schema::senha()->obrigatorio()->min(8)->max(72),
                ]))
                ->semConteudo('Senha alterada.')
                ->erros(429),

            Rota::patch('/conta/tema', [self::class, 'tema'])->tag($tag)
                ->resumo('Preferências visuais do painel')
                ->corpo(Schema::objeto([
                    'modo' => Schema::enum(TEMA_MODOS),
                    'cor' => Schema::enum(TEMA_CORES),
                ]))
                ->dados(Usuario::schema()),

            Rota::put('/conta/imagem', [self::class, 'imagem'])->permissao('cUsuario')->tag($tag)
                ->resumo('Troca a foto da conta', 'PNG, JPG ou BMP de até 2 MB.')
                ->corpo(Schema::objeto(['arquivo' => Schema::arquivo()->obrigatorio()]), true)
                ->dados(Usuario::schema()),

            Rota::post('/conta/dois-fatores/configuracao', [self::class, 'configurarDoisFatores'])->liberadaCom2faPendente()->tag($tag)
                ->resumo('Começa a ativar o 2FA', 'Gera um segredo novo para o aplicativo autenticador. Mostre o QR code (ou o segredo) e peça o código; depois chame `POST /conta/dois-fatores` com a `configuracao` recebida, que vale 10 minutos. Nada é gravado até a ativação.')
                ->dados(Schema::objeto([
                    'segredo' => Schema::texto()->obrigatorio()->exemplo('JBSW Y3DP EHPK 3PXP'),
                    'otpauth_uri' => Schema::texto()->obrigatorio(),
                    'qr_code' => Schema::texto()->obrigatorio()->descricao('Imagem do QR code como data URI (PNG).'),
                    'configuracao' => Schema::texto()->obrigatorio(),
                    'expires_in' => Schema::inteiro()->obrigatorio(),
                ]))
                ->erros(409),

            Rota::post('/conta/dois-fatores', [self::class, 'ativarDoisFatores'])->liberadaCom2faPendente()->tag($tag)
                ->resumo('Ativa o 2FA', 'Confere a senha e o código do aplicativo. Devolve os códigos de recuperação: eles só aparecem agora.')
                ->corpo(Schema::objeto([
                    'configuracao' => Schema::texto()->obrigatorio()->max(4000),
                    'codigo' => Schema::texto()->obrigatorio()->max(10)->exemplo('123456'),
                    'senha' => (clone $senha),
                ]))
                ->dados(self::schemaCodigos())
                ->erros(409, 429),

            Rota::post('/conta/dois-fatores/codigos', [self::class, 'novosCodigos'])->tag($tag)
                ->resumo('Gera novos códigos de recuperação', 'Os códigos anteriores deixam de valer.')
                ->corpo(Schema::objeto(['senha' => (clone $senha)]))
                ->dados(self::schemaCodigos())
                ->erros(409, 429),

            Rota::post('/conta/dois-fatores/desativar', [self::class, 'desativarDoisFatores'])->tag($tag)
                ->resumo('Desativa o 2FA', 'Não é possível quando o grupo de permissão exige o 2FA.')
                ->corpo(Schema::objeto(['senha' => (clone $senha)]))
                ->semConteudo('2FA desativado.')
                ->erros(409, 429),
        ];
    }

    private static function schemaConta(): Schema
    {
        return Schema::objeto([
            'usuario' => Usuario::schema()->obrigatorio(),
            'permissoes' => Schema::lista(Schema::texto())->obrigatorio()->descricao('Códigos das permissões do grupo, como `vCliente` e `aOs`.')->exemplo(['vCliente', 'aCliente', 'vOs']),
            'dois_fatores' => Schema::objeto([
                'ativo' => Schema::booleano()->obrigatorio(),
                'exigido_pelo_grupo' => Schema::booleano()->obrigatorio(),
                'pendente' => Schema::booleano()->obrigatorio()->descricao('Exigido e ainda não ativado: só as rotas de conta e sessão respondem.'),
                'codigos_restantes' => Schema::inteiro()->obrigatorio(),
            ])->obrigatorio(),
        ])->nome('Conta');
    }

    private static function schemaCodigos(): Schema
    {
        return Schema::objeto([
            'codigos_recuperacao' => Schema::lista(Schema::texto())->obrigatorio()->descricao('Cada código vale uma vez no lugar do código do aplicativo.'),
        ])->nome('CodigosDeRecuperacao');
    }

    public function ver(): Resposta
    {
        $titular = $this->ctx->titular();
        $usuario = $this->usuario();
        $exigido = $this->ctx->modelo('Mapos_model')->grupoExige2fa((int) $usuario->permissoes_id);
        $ativo = ! empty($usuario->totp_ativado_em);

        return Resposta::dados([
            'usuario' => Usuario::de($usuario),
            'permissoes' => $titular->permissoes,
            'dois_fatores' => [
                'ativo' => $ativo,
                'exigido_pelo_grupo' => $exigido,
                'pendente' => $exigido && ! $ativo,
                'codigos_restantes' => $ativo ? $this->ctx->modelo('Mapos_model')->codigosDeRecuperacaoRestantes((int) $usuario->idUsuarios) : 0,
            ],
        ]);
    }

    public function senha(): Resposta
    {
        $dados = $this->ctx->corpo();
        $usuario = $this->usuario();
        $this->confirmarSenha($usuario, $dados['senha_atual'], 'senha_atual', 'A senha atual não confere.');

        $id = (int) $usuario->idUsuarios;
        $this->atualizarLinha('usuarios', 'idUsuarios', $id, ['senha' => password_hash($dados['nova_senha'], PASSWORD_DEFAULT)]);
        $this->ctx->modelo('Mapos_model')->invalidarSessoes($id);
        $this->ctx->sessoes->encerrarDoTitular(Rota::USUARIO, $id, 'senha_alterada', $this->ctx->titular()->sessao);
        $this->ctx->auditar('Alterou a própria senha.');

        return Resposta::semConteudo();
    }

    public function tema(): Resposta
    {
        $dados = array_filter($this->ctx->corpo(), static fn ($v) => $v !== null);
        $mapos = $this->ctx->modelo('Mapos_model');
        $id = $this->ctx->titular()->id;

        if (isset($dados['modo']) && ! $mapos->salvarTemaModo($id, $dados['modo'])) {
            throw HttpErro::indisponivel('Atualize o banco de dados para gravar o tema.', 'banco_desatualizado');
        }
        if (isset($dados['cor']) && ! $mapos->salvarTemaCor($id, $dados['cor'])) {
            throw HttpErro::indisponivel('Atualize o banco de dados para gravar o tema.', 'banco_desatualizado');
        }

        return Resposta::dados(Usuario::de($this->usuario()));
    }

    public function imagem(): Resposta
    {
        $dados = $this->ctx->corpo();
        $usuario = $this->usuario();
        $pasta = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'userImage';
        if (! is_dir($pasta) && ! @mkdir($pasta, 0755, true) && ! is_dir($pasta)) {
            throw HttpErro::interno('Não foi possível criar a pasta de imagens no servidor.');
        }
        $this->ctx->helper('armazenamento');
        armazenamentoProtegerPastaPublica($pasta);

        $enviado = (new Upload($this->ctx))->receber($dados['arquivo'], $pasta, ['png', 'jpg', 'jpeg', 'bmp'], 2048);

        $antiga = $pasta . DIRECTORY_SEPARATOR . basename((string) $usuario->url_image_user);
        if ((string) $usuario->url_image_user !== '' && is_file($antiga)) {
            @unlink($antiga);
        }
        $this->ctx->modelo('Mapos_model')->editImageUser((int) $usuario->idUsuarios, $enviado['nome']);
        $this->ctx->auditar('Alterou a Imagem do Usuario.');

        return Resposta::dados(Usuario::de($this->usuario()));
    }

    public function configurarDoisFatores(): Resposta
    {
        $usuario = $this->usuario();
        if (! empty($usuario->totp_ativado_em)) {
            throw HttpErro::conflito('A autenticação em dois fatores já está ativa.', 'dois_fatores_ja_ativo');
        }

        $totp = $this->totp();
        $segredo = $totp->criarSegredo();
        $duracao = 600;
        $configuracao = $this->ctx->tokens->emitirTemporario(self::CONFIGURACAO_2FA, (int) $usuario->idUsuarios, $duracao, ['seg' => $totp->cifrar($segredo)]);

        return Resposta::dados([
            'segredo' => Totp::formatarSegredo($segredo),
            'otpauth_uri' => $totp->uri((string) $usuario->email, $segredo),
            'qr_code' => $totp->qrCodeDataUri((string) $usuario->email, $segredo),
            'configuracao' => $configuracao['token'],
            'expires_in' => $duracao,
        ]);
    }

    public function ativarDoisFatores(): Resposta
    {
        $dados = $this->ctx->corpo();
        $usuario = $this->usuario();
        if (! empty($usuario->totp_ativado_em)) {
            throw HttpErro::conflito('A autenticação em dois fatores já está ativa.', 'dois_fatores_ja_ativo');
        }

        $claims = $this->ctx->tokens->validarTemporario($dados['configuracao'], self::CONFIGURACAO_2FA, 'A configuração expirou. Comece de novo em POST /conta/dois-fatores/configuracao.', 'configuracao_expirada');
        if ((int) $claims->sub !== (int) $usuario->idUsuarios) {
            throw HttpErro::regraDeNegocio('Esta configuração é de outra conta.', 'configuracao_expirada');
        }

        $this->confirmarSenha($usuario, $dados['senha'], 'senha');

        $totp = $this->totp();
        $segredo = $totp->decifrar((string) ($claims->seg ?? ''));
        $passo = $segredo === null ? null : $totp->verificar($segredo, $dados['codigo']);
        if ($passo === null) {
            throw HttpErro::validacao(['codigo' => 'Código incorreto. Confira o aplicativo e tente de novo.']);
        }

        $codigos = $totp->gerarCodigosDeRecuperacao();
        if (! $this->ctx->modelo('Mapos_model')->ativarTotp((int) $usuario->idUsuarios, $totp->cifrar($segredo), array_map([Totp::class, 'hashDoCodigo'], $codigos), $passo)) {
            throw HttpErro::interno('Não foi possível ativar. Tente de novo.');
        }
        $this->ctx->auditar('Ativou a autenticação em dois fatores.');

        return Resposta::dados(['codigos_recuperacao' => $codigos]);
    }

    public function novosCodigos(): Resposta
    {
        $dados = $this->ctx->corpo();
        $usuario = $this->usuario();
        if (empty($usuario->totp_ativado_em)) {
            throw HttpErro::conflito('A autenticação em dois fatores não está ativa.', 'dois_fatores_inativo');
        }
        $this->confirmarSenha($usuario, $dados['senha'], 'senha');

        $codigos = $this->totp()->gerarCodigosDeRecuperacao();
        $this->ctx->modelo('Mapos_model')->substituirCodigosDeRecuperacao((int) $usuario->idUsuarios, array_map([Totp::class, 'hashDoCodigo'], $codigos));
        $this->ctx->auditar('Gerou novos códigos de recuperação do 2FA.');

        return Resposta::dados(['codigos_recuperacao' => $codigos]);
    }

    public function desativarDoisFatores(): Resposta
    {
        $dados = $this->ctx->corpo();
        $usuario = $this->usuario();
        if (empty($usuario->totp_ativado_em)) {
            throw HttpErro::conflito('A autenticação em dois fatores não está ativa.', 'dois_fatores_inativo');
        }
        $mapos = $this->ctx->modelo('Mapos_model');
        if ($mapos->grupoExige2fa((int) $usuario->permissoes_id)) {
            throw HttpErro::conflito('O seu grupo de permissão exige o 2FA: não é possível desativar.', 'dois_fatores_exigido');
        }
        $this->confirmarSenha($usuario, $dados['senha'], 'senha');

        $mapos->desativarTotp((int) $usuario->idUsuarios);
        $this->ctx->auditar('Desativou a autenticação em dois fatores.');

        return Resposta::semConteudo();
    }

    private function usuario(): object
    {
        $usuario = $this->ctx->modelo('Mapos_model')->getById($this->ctx->titular()->id);
        if (! $usuario) {
            throw HttpErro::naoAutenticado('Faça o login novamente.', 'conta_desativada');
        }

        return $usuario;
    }

    private function totp(): Totp
    {
        return $this->ctx->biblioteca('Totp');
    }

    private function confirmarSenha(object $usuario, string $senha, string $campo, string $mensagem = 'A senha não confere.'): void
    {
        $limite = $this->ctx->biblioteca('Limite_login');
        $resultado = $limite->confirmarSenha((string) $usuario->email, $this->ctx->requisicao->ip, $senha, (string) $usuario->senha);

        if ($resultado === Limite_login::SENHA_BLOQUEADA) {
            throw HttpErro::muitasRequisicoes(60, Limite_login::MENSAGEM);
        }
        if ($resultado !== Limite_login::SENHA_OK) {
            throw HttpErro::validacao([$campo => $mensagem]);
        }
    }
}
