<?php

namespace Libraries\Api\Recursos;

use Libraries\Api\Schema\Schema;

final class Usuario
{
    private static ?Schema $schema = null;

    public static function schema(): Schema
    {
        return self::$schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio()->exemplo(3),
            'nome' => Schema::texto()->obrigatorio()->exemplo('Maria Técnica'),
            'email' => Schema::email()->obrigatorio()->exemplo('maria@empresa.com.br'),
            'cpf' => Schema::texto()->obrigatorio()->exemplo('529.982.247-25'),
            'rg' => Schema::texto()->anulavel(),
            'telefone' => Schema::texto()->obrigatorio(),
            'celular' => Schema::texto()->anulavel(),
            'endereco' => Endereco::schema()->obrigatorio(),
            'ativo' => Schema::booleano()->obrigatorio(),
            'grupo' => Schema::objeto([
                'id' => Schema::inteiro()->obrigatorio(),
                'nome' => Schema::texto()->anulavel()->obrigatorio(),
            ])->obrigatorio(),
            'data_cadastro' => Schema::data()->anulavel()->obrigatorio(),
            'data_expiracao' => Schema::data()->anulavel()->obrigatorio()->descricao('Depois desta data a conta não entra mais. Nulo: não expira.'),
            'dois_fatores_ativo' => Schema::booleano()->obrigatorio(),
            'imagem_url' => Schema::url()->anulavel()->obrigatorio(),
            'tema' => Schema::objeto([
                'modo' => Schema::enum(['claro', 'escuro', 'sistema'])->anulavel()->obrigatorio(),
                'cor' => Schema::texto()->anulavel()->obrigatorio()->exemplo('padrao'),
            ])->obrigatorio()->descricao('Preferência visual do painel. Nulo segue a configuração do sistema.'),
        ])->nome('Usuario');
    }

    public static function de(object $linha, ?string $nomeDoGrupo = null): array
    {
        return [
            'id' => (int) $linha->idUsuarios,
            'nome' => (string) $linha->nome,
            'email' => (string) $linha->email,
            'cpf' => (string) $linha->cpf,
            'rg' => Valor::textoOuNulo($linha->rg ?? null),
            'telefone' => (string) $linha->telefone,
            'celular' => Valor::textoOuNulo($linha->celular ?? null),
            'endereco' => Endereco::de($linha->cep ?? null, $linha->rua ?? null, $linha->numero ?? null, $linha->bairro ?? null, $linha->cidade ?? null, $linha->estado ?? null),
            'ativo' => (int) $linha->situacao === 1,
            'grupo' => ['id' => (int) $linha->permissoes_id, 'nome' => $nomeDoGrupo ?? Valor::textoOuNulo($linha->permissao ?? null)],
            'data_cadastro' => Valor::data($linha->dataCadastro ?? null),
            'data_expiracao' => Valor::data($linha->dataExpiracao ?? null),
            'dois_fatores_ativo' => ! empty($linha->totp_ativado_em),
            'imagem_url' => self::urlDaImagem($linha->url_image_user ?? null),
            'tema' => [
                'modo' => in_array($linha->tema_modo ?? null, ['claro', 'escuro', 'sistema'], true) ? $linha->tema_modo : null,
                'cor' => Valor::textoOuNulo($linha->tema_cor ?? null),
            ],
        ];
    }

    public static function urlDaImagem(?string $arquivo): ?string
    {
        $nome = basename((string) $arquivo);
        if ($nome === '' || $nome === '.' || ! is_file(FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'userImage' . DIRECTORY_SEPARATOR . $nome)) {
            return null;
        }

        return base_url('assets/userImage/' . rawurlencode($nome));
    }
}
