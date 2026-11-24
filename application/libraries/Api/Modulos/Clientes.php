<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Cliente;
use Libraries\Api\Recursos\Endereco;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Recursos\Venda;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Clientes extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Clientes';

        return [
            Rota::get('/clientes', [self::class, 'listar'])->permissao('vCliente')->tag($tag)
                ->resumo('Lista clientes e fornecedores', 'Do cadastro mais recente para o mais antigo.')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Procura em nome, documento, e-mail, telefone e celular.'),
                    'tipo' => Schema::enum(['cliente', 'fornecedor']),
                ]))
                ->lista(Cliente::schema()),

            Rota::post('/clientes', [self::class, 'criar'])->permissao('aCliente')->tag($tag)
                ->resumo('Cadastra um cliente', 'Sem `senha`, a senha da área do cliente fica sendo o documento só com os números.')
                ->corpo(self::entrada())
                ->criado(Cliente::schema()),

            Rota::get('/clientes/{id}', [self::class, 'ver'])->permissao('vCliente')->tag($tag)
                ->resumo('Mostra um cliente', 'Inclui o total de OS e de vendas do cliente.')
                ->dados(Cliente::schema()->com([
                    'total_os' => Schema::inteiro()->obrigatorio(),
                    'total_vendas' => Schema::inteiro()->obrigatorio(),
                ]))
                ->erros(404),

            Rota::patch('/clientes/{id}', [self::class, 'atualizar'])->permissao('eCliente')->tag($tag)
                ->resumo('Altera um cliente', 'Envie só os campos que mudam. `endereco` é substituído inteiro quando enviado.')
                ->corpo(self::entrada()->semObrigatorios())
                ->dados(Cliente::schema())
                ->erros(404),

            Rota::delete('/clientes/{id}', [self::class, 'excluir'])->permissao('dCliente')->tag($tag)
                ->resumo('Exclui um cliente', 'Como no painel, as OS e as vendas do cliente são excluídas junto. A ação não pode ser desfeita.')
                ->semConteudo('Cliente excluído.')
                ->erros(404),

            Rota::get('/clientes/{id}/os', [self::class, 'ordens'])->todasAsPermissoes('vCliente', 'vOs')->tag($tag)
                ->resumo('OS do cliente')
                ->consulta(Comuns::paginacaoConsulta())
                ->lista(OrdemDeServico::schemaResumo())
                ->erros(404),

            Rota::get('/clientes/{id}/vendas', [self::class, 'vendas'])->todasAsPermissoes('vCliente', 'vVenda')->tag($tag)
                ->resumo('Vendas do cliente')
                ->consulta(Comuns::paginacaoConsulta())
                ->lista(Venda::schemaResumo())
                ->erros(404),
        ];
    }

    private static function entrada(): Schema
    {
        return Schema::objeto([
            'nome' => Schema::texto()->obrigatorio()->max(255)->exemplo('Ana Souza'),
            'documento' => Schema::texto()->max(20)->descricao('CPF ou CNPJ, com ou sem pontuação. Não pode repetir.')->exemplo('529.982.247-25')
                ->regra(static fn (string $v): ?string => $v === '' || verific_cpf_cnpj($v) ? null : 'CPF ou CNPJ inválido.'),
            'fornecedor' => Schema::booleano()->padrao(false),
            'contato' => Schema::texto()->max(45),
            'email' => Schema::email()->max(100)->descricao('Login da área do cliente. Não pode repetir.'),
            'telefone' => Schema::texto()->max(20),
            'celular' => Schema::texto()->max(20),
            'senha' => Schema::senha()->min(6)->max(72)->descricao('Senha da área do cliente.'),
            'endereco' => Endereco::entrada(),
        ])->nome('ClienteEntrada');
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();
        $pagina = $this->ctx->paginacao();
        $filtros = array_filter(['pesquisa' => $consulta['busca'] ?? '', 'tipo' => $consulta['tipo'] ?? ''], static fn ($v) => $v !== '');
        $modelo = $this->ctx->modelo('Clientes_model');

        $total = $modelo->contar($filtros);
        $linhas = $modelo->listar($filtros, $pagina['por_pagina'], $pagina['deslocamento']);

        return Resposta::lista(array_map([Cliente::class, 'de'], $linhas), ['pagina' => $pagina['pagina'], 'por_pagina' => $pagina['por_pagina'], 'total' => $total]);
    }

    public function ver(): Resposta
    {
        $cliente = $this->cliente();
        $modelo = $this->ctx->modelo('Clientes_model');
        $id = (int) $cliente->idClientes;

        return Resposta::dados(Cliente::de($cliente) + [
            'total_os' => $modelo->contarOsDoCliente($id),
            'total_vendas' => $modelo->contarVendasDoCliente($id),
        ]);
    }

    public function criar(): Resposta
    {
        $dados = $this->ctx->corpo();
        $this->conferirUnicos($dados, null);

        $id = $this->inserirLinha('clientes', $this->paraBanco($dados, true) + ['dataCadastro' => date('Y-m-d')]);
        $this->ctx->auditar('Adicionou um cliente.');

        return Resposta::criado(Cliente::de($this->cliente($id)), $this->ctx->url('clientes/' . $id));
    }

    public function atualizar(): Resposta
    {
        $cliente = $this->cliente();
        $id = (int) $cliente->idClientes;
        [$dados] = $this->ctx->corpoParcial(self::paraEntrada($cliente));
        $this->conferirUnicos($dados, $id);

        $this->ctx->modelo('Clientes_model')->edit('clientes', $this->paraBanco($dados, false), 'idClientes', $id);
        $this->ctx->auditar('Alterou um cliente. ID ' . $id);

        return Resposta::dados(Cliente::de($this->cliente($id)));
    }

    public function excluir(): Resposta
    {
        $id = (int) $this->cliente()->idClientes;
        $modelo = $this->ctx->modelo('Clientes_model');

        $this->transacao(function () use ($modelo, $id): void {
            if ($os = $modelo->getAllOsByClient($id)) {
                $modelo->removeClientOs($os);
            }
            if ($vendas = $modelo->getAllVendasByClient($id)) {
                $modelo->removeClientVendas($vendas);
            }
            $this->removerLinha('clientes', 'idClientes', $id);
        });
        $this->ctx->auditar('Removeu um cliente. ID' . $id);

        return Resposta::semConteudo();
    }

    public function ordens(): Resposta
    {
        $id = (int) $this->cliente()->idClientes;
        $pagina = $this->ctx->paginacao();
        $total = (int) $this->db->where('clientes_id', $id)->count_all_results('os');
        $linhas = OrdemDeServico::consultaResumo($this->db)->where('os.clientes_id', $id)->order_by('os.idOs', 'DESC')->limit($pagina['por_pagina'], $pagina['deslocamento'])->get()->result();

        return Resposta::lista(array_map([OrdemDeServico::class, 'resumoDe'], $linhas), ['pagina' => $pagina['pagina'], 'por_pagina' => $pagina['por_pagina'], 'total' => $total]);
    }

    public function vendas(): Resposta
    {
        $id = (int) $this->cliente()->idClientes;
        $pagina = $this->ctx->paginacao();
        $total = (int) $this->db->where('clientes_id', $id)->count_all_results('vendas');
        $linhas = Venda::consultaResumo($this->db)->where('vendas.clientes_id', $id)->order_by('vendas.idVendas', 'DESC')->limit($pagina['por_pagina'], $pagina['deslocamento'])->get()->result();

        return Resposta::lista(array_map([Venda::class, 'resumoDe'], $linhas), ['pagina' => $pagina['pagina'], 'por_pagina' => $pagina['por_pagina'], 'total' => $total]);
    }

    private function cliente(?int $id = null): object
    {
        return $this->encontrar('clientes', 'idClientes', $id ?? $this->ctx->id(), 'Cliente não encontrado.');
    }

    private function conferirUnicos(array $dados, ?int $id): void
    {
        $erros = [];
        if (($dados['email'] ?? '') !== '' && $this->existe('clientes', 'email', $dados['email'], $id, 'idClientes')) {
            $erros['email'] = 'Este e-mail já está sendo usado por outro cliente.';
        }
        if (($dados['documento'] ?? '') !== '' && $this->existe('clientes', 'documento', $dados['documento'], $id, 'idClientes')) {
            $erros['documento'] = 'Já existe um cliente com este documento.';
        }
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }
    }

    /** @return array<string, mixed> */
    public static function paraEntrada(object $cliente): array
    {
        return [
            'nome' => (string) $cliente->nomeCliente,
            'documento' => (string) $cliente->documento,
            'fornecedor' => (int) $cliente->fornecedor === 1,
            'contato' => (string) $cliente->contato,
            'email' => (string) $cliente->email,
            'telefone' => (string) $cliente->telefone,
            'celular' => (string) $cliente->celular,
            'endereco' => Endereco::de($cliente->cep, $cliente->rua, $cliente->numero, $cliente->bairro, $cliente->cidade, $cliente->estado, $cliente->complemento),
        ];
    }

    private function paraBanco(array $dados, bool $novo): array
    {
        $endereco = $dados['endereco'] ?? [];
        $formulario = [
            'nomeCliente' => $dados['nome'],
            'documento' => $dados['documento'] ?? '',
            'contato' => $dados['contato'] ?? '',
            'email' => $dados['email'] ?? '',
            'telefone' => $dados['telefone'] ?? '',
            'celular' => $dados['celular'] ?? '',
            'fornecedor' => ! empty($dados['fornecedor']) ? '1' : '',
            'senha' => $dados['senha'] ?? '',
        ] + Endereco::paraBanco(is_array($endereco) ? $endereco : [], true);

        $this->ctx->helper('clientes');

        return clienteDadosDoFormulario(array_map(static fn ($v) => $v === null ? '' : (string) $v, $formulario), $novo);
    }
}
