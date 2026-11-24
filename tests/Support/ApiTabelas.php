<?php

final class ApiTabelas
{
    public static function criar(object $db): void
    {
        $db->query('CREATE TABLE api_sessoes (id INTEGER PRIMARY KEY AUTOINCREMENT, identificador TEXT UNIQUE, tipo TEXT, titular_id INTEGER, dispositivo TEXT, ip TEXT, user_agent TEXT,
            criada_em TEXT, ultimo_uso_em TEXT, expira_em TEXT, revogada_em TEXT, motivo TEXT)');
        $db->query('CREATE TABLE api_tokens_renovacao (id INTEGER PRIMARY KEY AUTOINCREMENT, sessao_id INTEGER, token_hash TEXT UNIQUE, criado_em TEXT, expira_em TEXT, usado_em TEXT)');
        $db->query('CREATE TABLE api_rate_limit (chave TEXT, janela INTEGER, total INTEGER DEFAULT 0, PRIMARY KEY (chave, janela))');
        $db->query('CREATE TABLE logs (idLogs INTEGER PRIMARY KEY AUTOINCREMENT, usuario TEXT, tarefa TEXT, data TEXT, hora TEXT, ip TEXT)');
        $db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, permissoes TEXT, situacao INTEGER, data TEXT, exigir_2fa INTEGER DEFAULT 0)');
        $db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, rg TEXT, cpf TEXT, cep TEXT, rua TEXT, numero TEXT, bairro TEXT, cidade TEXT, estado TEXT,
            email TEXT, senha TEXT, telefone TEXT, celular TEXT, situacao INTEGER, dataCadastro TEXT, permissoes_id INTEGER, dataExpiracao TEXT, url_image_user TEXT,
            totp_secreto TEXT, totp_ativado_em TEXT, totp_ultimo_passo INTEGER, tema_modo TEXT, tema_cor TEXT, sessao_versao INTEGER DEFAULT 0)');
        $db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, sexo TEXT, pessoa_fisica INTEGER DEFAULT 1, documento TEXT, telefone TEXT, celular TEXT,
            email TEXT, senha TEXT, dataCadastro TEXT, rua TEXT, numero TEXT, bairro TEXT, cidade TEXT, estado TEXT, cep TEXT, contato TEXT, complemento TEXT, fornecedor INTEGER DEFAULT 0)');
    }

    /** @param  list<string>  $permissoes */
    public static function grupo(object $db, string $nome, array $permissoes, int $situacao = 1, int $exigir2fa = 0): int
    {
        $db->insert('permissoes', ['nome' => $nome, 'permissoes' => json_encode(array_fill_keys($permissoes, '1')), 'situacao' => $situacao, 'exigir_2fa' => $exigir2fa]);

        return (int) $db->insert_id();
    }

    public static function usuario(object $db, string $email, int $grupo, array $extras = []): int
    {
        $db->insert('usuarios', $extras + [
            'nome' => 'Usuário ' . $email,
            'cpf' => '529.982.247-25',
            'email' => $email,
            'senha' => password_hash('senha-certa-123', PASSWORD_BCRYPT, ['cost' => 4]),
            'telefone' => '1199999999',
            'situacao' => 1,
            'dataCadastro' => '2026-01-01',
            'permissoes_id' => $grupo,
        ]);

        return (int) $db->insert_id();
    }
}
