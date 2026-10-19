<?php

use Piggly\Pix\Parser;

if (! function_exists('convertUrlToUploadsPath')) {
    function convertUrlToUploadsPath($url)
    {
        if (! $url) {
            return;
        }

        return FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($url);
    }
}

if (! function_exists('apiHabilitada')) {
    /**
     * A API só existe quando API_ENABLED está ligada: as rotas (routes.php) e a
     * classe base dos controllers de api/, que cobre o acesso direto por URL.
     */
    function apiHabilitada(): bool
    {
        return filter_var($_ENV['API_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('senhaConfere')) {
    /**
     * Confere a senha em tempo parecido com e sem conta: sem hash (e-mail
     * inexistente) ela é verificada contra um hash fictício e fixo, de custo 12
     * (o PASSWORD_DEFAULT do PHP 8.4+), e o tempo de resposta deixa de revelar
     * quais e-mails têm cadastro (#2879). Sempre falso quando não há hash. O
     * hash é fixo porque gerar um a cada requisição dobraria o tempo do caminho
     * sem conta. Contas antigas, com hash de custo 10, ainda respondem mais
     * rápido que as demais.
     */
    function senhaConfere(string $senha, ?string $hash): bool
    {
        if ($hash === null || $hash === '') {
            password_verify($senha, '$2y$12$as5wptyRExe36.CMu5o7vODVOzM1KVZ.PLep3qTOd6G.AFhrLAu5u');

            return false;
        }

        return password_verify($senha, $hash);
    }
}

if (! function_exists('contaExpirada')) {
    /**
     * Diz se a conta está expirada pela data de usuarios.dataExpiracao.
     * Sem data cadastrada (nulo ou só espaços) a conta não expira.
     */
    function contaExpirada(?string $dataExpiracao): bool
    {
        if (trim((string) $dataExpiracao) === '') {
            return false;
        }

        return new DateTime($dataExpiracao) < new DateTime('now');
    }
}

if (! function_exists('chaveInsegura')) {
    /**
     * Chave vazia, igual ao marcador do .env.example ou mais curta que o
     * mínimo: quem copiou o exemplo sem trocar o valor tem uma chave pública.
     */
    function chaveInsegura(?string $valor, string $marcador, int $minimo = 0): bool
    {
        $valor = trim((string) $valor);

        return $valor === '' || $valor === $marcador || strlen($valor) < $minimo;
    }
}

if (! function_exists('apiChaveJwtInsegura')) {
    /** API_JWT_KEY precisa ter pelo menos 32 caracteres e não ser o marcador do .env.example. */
    function apiChaveJwtInsegura(): bool
    {
        return chaveInsegura($_ENV['API_JWT_KEY'] ?? '', 'enter_jwt_key', 32);
    }
}

if (! function_exists('limitarTexto')) {
    function limitarTexto($texto, $limite)
    {
        $contador = strlen($texto);

        if ($contador >= $limite) {
            $texto = substr($texto, 0, strrpos(substr($texto, 0, $limite), ' ')) . '...';

            return $texto;
        } else {
            return $texto;
        }
    }
}

if (! function_exists('getMoneyAsCents')) {
    function getMoneyAsCents($value)
    {
        // make sure we are dealing with a proper number now, no +.4393 or 3...304 or 76.5895,94
        if (! is_numeric($value)) {
            throw new \InvalidArgumentException('A entrada deve ser numérica!');
        }

        // Arredonda depois de multiplicar: intval(1.15 * 100) dava 114 (#3014).
        return (int) round(floatval($value) * 100);
    }
}

if (! function_exists('getCobrancaTransactionStatus')) {
    function getCobrancaTransactionStatus($paymentGatewaysConfig, $paymentGateway, $status)
    {
        return $paymentGatewaysConfig[$paymentGateway]['transaction_status'][$status];
    }
}

if (! function_exists('getPixKeyType')) {
    function getPixKeyType($value)
    {
        if (Parser::validateDocument($value)) {
            return Parser::KEY_TYPE_DOCUMENT;
        }

        if (Parser::validateEmail($value)) {
            return Parser::KEY_TYPE_EMAIL;
        }

        if (Parser::validatePhone($value)) {
            return Parser::KEY_TYPE_PHONE;
        }

        if (Parser::validateRandom($value)) {
            return Parser::KEY_TYPE_RANDOM;
        }

        return null;
    }
}

if (! function_exists('getAmount')) {
    function getAmount($money)
    {
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot);

        return floatval(str_replace(',', '.', $removedThousandSeparator));
    }
}

if (! function_exists('json_decode_legacy')) {
    function json_decode_legacy(string $raw): mixed
    {
        $decoded = json_decode($raw, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            $decoded = unserialize($raw, ['allowed_classes' => false]);
        }

        return $decoded;
    }
}

if (! function_exists('printSafeHtml')) {
    /**
     * HTML confiável filtrado por HTMLPurifier. Só http, https, mailto e tel
     * ficam nos links. Com $semRecursosExternos (relatórios em PDF), imagens e
     * outros recursos de outro host também saem, para o mPDF no servidor não
     * buscar URL escolhida por quem digitou a descrição (#2879).
     */
    function printSafeHtml(?string $html, bool $semRecursosExternos = false): string
    {
        static $purifiers = [];

        // Colunas de texto opcionais (ex. os.defeito) chegam como null.
        if ($html === null || $html === '') {
            return '';
        }

        $chave = $semRecursosExternos ? 'pdf' : 'padrao';

        if (! isset($purifiers[$chave])) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
            if ($semRecursosExternos) {
                $config->set('URI.DisableExternalResources', true);
            }
            $purifiers[$chave] = new HTMLPurifier($config);
        }

        return $purifiers[$chave]->purify($html);
    }
}

if (! function_exists('protegerDiretorioUpload')) {
    /**
     * Cria um index.html nos diretórios de upload informados para impedir a
     * listagem do conteúdo quando o servidor web permite indexação.
     */
    function protegerDiretorioUpload(array $diretorios)
    {
        foreach ($diretorios as $diretorio) {
            $index = rtrim($diretorio, '/\\') . DIRECTORY_SEPARATOR . 'index.html';

            if (is_dir($diretorio) && ! file_exists($index)) {
                @file_put_contents($index, "<html>\n<head>\n\t<title>403 Forbidden</title>\n</head>\n<body>\n\n<p>Directory access is forbidden.</p>\n\n</body>\n</html>\n");
            }
        }
    }
}

if (! function_exists('dataIsoParaYmd')) {
    /**
     * Data em AAAA-MM-DD a partir de um parâmetro de requisição, ou null se
     * faltar ou for inválida. Aceita a data pura ou um ISO 8601 com hora e fuso
     * (2026-10-01T00:00:00-03:00, como o FullCalendar envia), do qual só a
     * data interessa.
     */
    function dataIsoParaYmd($valor): ?string
    {
        if (! is_string($valor) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:$|T)/', $valor, $partes)) {
            return null;
        }

        if (! checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            return null;
        }

        return "{$partes[1]}-{$partes[2]}-{$partes[3]}";
    }
}

if (! function_exists('tamanhoDeArquivo')) {
    /**
     * Tamanho de arquivo para a tela, a partir dos KB que o upload guarda
     * (file_size): "0,08 KB" abaixo de 10 KB, "512 KB" acima, "1,5 MB" a
     * partir de 1024 KB.
     */
    function tamanhoDeArquivo($kb): string
    {
        $kb = max(0.0, (float) $kb);

        if ($kb >= 1024) {
            return number_format($kb / 1024, 1, ',', '.') . ' MB';
        }

        return number_format($kb, $kb < 10 ? 2 : 0, ',', '.') . ' KB';
    }
}

if (! function_exists('backupNomeArquivo')) {
    /**
     * Nome do arquivo de download do backup: backup-AAAA-MM-DD-HHhMM.zip.
     * Sem ":" (inválido em nomes de arquivo no Windows) e com minutos, e não o
     * mês, depois da hora.
     */
    function backupNomeArquivo(int $instante): string
    {
        return 'backup-' . date('Y-m-d-H\\hi', $instante) . '.zip';
    }
}

if (! function_exists('tokenRecuperacaoGerar')) {
    /**
     * Token de recuperação de senha da área do cliente (#2875): 32 bytes
     * aleatórios em hexadecimal. Devolve [token em claro, hash para o banco];
     * o token em claro só vai no e-mail.
     *
     * @return array{0: string, 1: string}
     */
    function tokenRecuperacaoGerar(): array
    {
        $token = bin2hex(random_bytes(32));

        return [$token, tokenRecuperacaoHash($token)];
    }
}

if (! function_exists('tokenRecuperacaoHash')) {
    /**
     * SHA-256 do token de recuperação, como guardado em resets_de_senha.token,
     * ou null se o valor não tiver o formato de um token (64 hex). Recusar o
     * formato antes evita consultar o banco com qualquer coisa vinda da URL.
     */
    function tokenRecuperacaoHash($token): ?string
    {
        if (! is_string($token) || ! preg_match('/^[0-9a-f]{64}$/', $token)) {
            return null;
        }

        return hash('sha256', $token);
    }
}

if (! function_exists('ufsDoBrasil')) {
    /**
     * Unidades federativas, sigla => nome, para selects de endereço.
     *
     * @return array<string, string>
     */
    function ufsDoBrasil(): array
    {
        return [
            'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas', 'BA' => 'Bahia',
            'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo', 'GO' => 'Goiás',
            'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
            'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco', 'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina', 'SP' => 'São Paulo',
            'SE' => 'Sergipe', 'TO' => 'Tocantins',
        ];
    }
}
