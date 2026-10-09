<?php

defined('BASEPATH') or exit('No direct script access allowed');

$config = [
    'clientes' => [
        [
            'field' => 'nomeCliente',
            'label' => 'Nome ou razão social',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'documento',
            'label' => 'CPF ou CNPJ',
            'rules' => 'trim|verific_cpf_cnpj|unique[clientes.documento.' . get_instance()->uri->segment(3) . '.idClientes]',
            'errors' => [
                'verific_cpf_cnpj' => 'O campo %s não é um CPF ou CNPJ válido.',
            ],
        ],
        [
            'field' => 'telefone',
            'label' => 'Telefone',
            'rules' => 'trim',
        ],
        [
            'field' => 'email',
            'label' => 'E-mail',
            'rules' => 'trim|valid_email|unique[clientes.email.' . get_instance()->uri->segment(3) . '.idClientes]',
        ],
        [
            'field' => 'rua',
            'label' => 'Rua',
            'rules' => 'trim',
        ],
        [
            'field' => 'numero',
            'label' => 'Número',
            'rules' => 'trim',
        ],
        [
            'field' => 'bairro',
            'label' => 'Bairro',
            'rules' => 'trim',
        ],
        [
            'field' => 'cidade',
            'label' => 'Cidade',
            'rules' => 'trim',
        ],
        [
            'field' => 'estado',
            'label' => 'Estado',
            'rules' => 'trim',
        ],
        [
            'field' => 'cep',
            'label' => 'CEP',
            'rules' => 'trim',
        ],
    ],
    'servicos' => [
        [
            'field' => 'nome',
            'label' => 'Nome',
            'rules' => 'required|trim|max_length[45]',
        ],
        [
            'field' => 'descricao',
            'label' => 'Descrição',
            'rules' => 'trim|max_length[45]',
        ],
        [
            'field' => 'preco',
            'label' => 'Preço',
            'rules' => 'required|trim',
        ],
    ],
    'produtos' => [
        [
            'field' => 'codDeBarra',
            'label' => 'Código de barras',
            'rules' => 'trim|max_length[70]',
        ],
        [
            'field' => 'descricao',
            'label' => 'Descrição',
            'rules' => 'required|trim|max_length[80]',
        ],
        [
            'field' => 'unidade',
            'label' => 'Unidade',
            'rules' => 'required|trim|max_length[10]',
        ],
        [
            'field' => 'precoCompra',
            'label' => 'Preço de compra',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'precoVenda',
            'label' => 'Preço de venda',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'estoque',
            'label' => 'Estoque',
            'rules' => 'required|trim|integer|greater_than_equal_to[0]|less_than_equal_to[99999999]',
        ],
        [
            'field' => 'estoqueMinimo',
            'label' => 'Estoque mínimo',
            'rules' => 'trim|integer|greater_than_equal_to[0]|less_than_equal_to[99999999]',
        ],
    ],
    'usuarios' => [
        [
            'field' => 'nome',
            'label' => 'Nome',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'rg',
            'label' => 'RG',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'cpf',
            'label' => 'CPF',
            'rules' => 'required|trim|verific_cpf_cnpj|is_unique[usuarios.cpf]',
            'errors' => [
                'verific_cpf_cnpj' => 'O campo %s não é um CPF válido.',
            ],
        ],
        [
            'field' => 'rua',
            'label' => 'Rua',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'numero',
            'label' => 'Numero',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'bairro',
            'label' => 'Bairro',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'cidade',
            'label' => 'Cidade',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'estado',
            'label' => 'Estado',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'cep',
            'label' => 'CEP',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'required|trim|valid_email|is_unique[usuarios.email]',
        ],
        [
            'field' => 'senha',
            'label' => 'Senha',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'telefone',
            'label' => 'Telefone',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'situacao',
            'label' => 'Situacao',
            'rules' => 'required|trim',
        ],
    ],
    'os' => [
        [
            'field' => 'dataInicial',
            'label' => 'Data inicial',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'dataFinal',
            'label' => 'Data final',
            'rules' => 'trim|required',
        ],
        [
            'field' => 'garantia',
            'label' => 'Garantia',
            'rules' => 'trim|numeric',
            'errors' => [
                'numeric' => 'Informe a garantia em dias, só com números.',
            ],
        ],
        [
            'field' => 'termoGarantia',
            'label' => 'Termo de garantia',
            'rules' => 'trim',
        ],
        [
            'field' => 'descricaoProduto',
            'label' => 'Descrição do produto ou serviço',
            'rules' => 'trim',
        ],
        [
            'field' => 'defeito',
            'label' => 'Defeito',
            'rules' => 'trim',
        ],
        [
            'field' => 'status',
            'label' => 'Status',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'observacoes',
            'label' => 'Observações',
            'rules' => 'trim',
        ],
        [
            'field' => 'clientes_id',
            'label' => 'Cliente',
            'rules' => 'trim|required',
        ],
        [
            'field' => 'usuarios_id',
            'label' => 'Técnico responsável',
            'rules' => 'trim|required',
        ],
        [
            'field' => 'laudoTecnico',
            'label' => 'Laudo técnico',
            'rules' => 'trim',
        ],
    ],
    'tiposUsuario' => [
        [
            'field' => 'nomeTipo',
            'label' => 'NomeTipo',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'situacao',
            'label' => 'Situacao',
            'rules' => 'required|trim',
        ],
    ],
    'receita' => [
        [
            'field' => 'descricao',
            'label' => 'Descrição',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'valor',
            'label' => 'Valor',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'vencimento',
            'label' => 'Data Vencimento',
            'rules' => 'required|trim',
        ],

        [
            'field' => 'cliente',
            'label' => 'Cliente',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'tipo',
            'label' => 'Tipo',
            'rules' => 'required|trim',
        ],
    ],
    'garantias' => [
        [
            'field' => 'dataGarantia',
            'label' => 'dataGarantia',
            'rules' => 'trim',
        ],
        [
            'field' => 'usuarios_id',
            'label' => 'usuarios_id',
            'rules' => 'trim',
        ],
        [
            'field' => 'refGarantia',
            'label' => 'refGarantia',
            'rules' => 'trim',
        ],
        [
            'field' => 'textoGarantia',
            'label' => 'textoGarantia',
            'rules' => 'required|trim',
        ],
    ],
    'pagamentos' => [
        [
            'field' => 'Nome',
            'label' => 'nomePag',
            'rules' => 'trim',
        ],
        [
            'field' => 'clientId',
            'label' => 'clientId',
            'rules' => 'trim',
        ],
        [
            'field' => 'clientSecret',
            'label' => 'clientSecret',
            'rules' => 'trim',
        ],
        [
            'field' => 'publicKey',
            'label' => 'publicKey',
            'rules' => 'trim',
        ],
        [
            'field' => 'accessToken',
            'label' => 'accessToken',
            'rules' => 'trim',
        ],
    ],
    // Formulário de usuário da v5 (#2846). O grupo "usuarios", com is_unique,
    // continua para a API. Endereço e RG passam a ser opcionais; a senha é
    // conferida em usuarioDadosDoFormulario() (obrigatória só ao cadastrar).
    'usuarios_formulario' => [
        [
            'field' => 'nome',
            'label' => 'Nome',
            'rules' => 'required|trim|max_length[80]',
        ],
        [
            'field' => 'cpf',
            'label' => 'CPF',
            'rules' => 'required|trim|max_length[20]|verific_cpf_cnpj|unique[usuarios.cpf.' . get_instance()->uri->segment(3) . '.idUsuarios]',
            'errors' => [
                'verific_cpf_cnpj' => 'Informe um CPF válido.',
            ],
        ],
        [
            'field' => 'email',
            'label' => 'E-mail',
            'rules' => 'required|trim|max_length[80]|valid_email|unique[usuarios.email.' . get_instance()->uri->segment(3) . '.idUsuarios]',
        ],
        [
            'field' => 'telefone',
            'label' => 'Telefone',
            'rules' => 'required|trim|max_length[20]',
        ],
        [
            'field' => 'celular',
            'label' => 'Celular',
            'rules' => 'trim|max_length[20]',
        ],
        [
            'field' => 'rg',
            'label' => 'RG',
            'rules' => 'trim|max_length[20]',
        ],
        [
            'field' => 'cep',
            'label' => 'CEP',
            'rules' => 'trim|max_length[9]',
        ],
        [
            'field' => 'rua',
            'label' => 'Rua',
            'rules' => 'trim|max_length[70]',
        ],
        [
            'field' => 'numero',
            'label' => 'Número',
            'rules' => 'trim|max_length[15]',
        ],
        [
            'field' => 'bairro',
            'label' => 'Bairro',
            'rules' => 'trim|max_length[45]',
        ],
        [
            'field' => 'cidade',
            'label' => 'Cidade',
            'rules' => 'trim|max_length[45]',
        ],
        [
            'field' => 'situacao',
            'label' => 'Situação',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'permissoes_id',
            'label' => 'Grupo de permissão',
            'rules' => 'required|trim',
        ],
    ],
    'vendas' => [
        [
            'field' => 'dataVenda',
            'label' => 'Data da venda',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'garantia',
            'label' => 'Garantia',
            'rules' => 'trim|numeric',
            'errors' => [
                'numeric' => 'Informe a garantia em dias, só com números.',
            ],
        ],
        [
            'field' => 'status',
            'label' => 'Status',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'observacoes',
            'label' => 'Observações internas',
            'rules' => 'trim',
        ],
        [
            'field' => 'observacoes_cliente',
            'label' => 'Observações ao cliente',
            'rules' => 'trim',
        ],
        [
            'field' => 'clientes_id',
            'label' => 'Cliente',
            'rules' => 'trim|required',
        ],
        [
            'field' => 'usuarios_id',
            'label' => 'Vendedor',
            'rules' => 'trim|required',
        ],
    ],
    'anotacoes_os' => [
        [
            'field' => 'anotacao',
            'label' => 'Anotação',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'os_id',
            'label' => 'ID Os',
            'rules' => 'trim|required|integer',
        ],
    ],
    'adicionar_produto_os' => [
        [
            'field' => 'idProduto',
            'label' => 'idProduto',
            'rules' => 'trim|required|numeric',
        ],
        [
            'field' => 'quantidade',
            'label' => 'quantidade',
            'rules' => 'trim|required|numeric|greater_than[0]',
        ],
        [
            'field' => 'preco',
            'label' => 'preco',
            'rules' => 'trim|required|numeric|greater_than[-1]',
        ],
        [
            'field' => 'idOsProduto',
            'label' => 'idOsProduto',
            'rules' => 'trim|required|numeric',
        ],
    ],
    'adicionar_servico_os' => [
        [
            'field' => 'idServico',
            'label' => 'idServico',
            'rules' => 'trim|required|numeric',
        ],
        [
            'field' => 'quantidade',
            'label' => 'quantidade',
            'rules' => 'trim|required|numeric|greater_than[0]',
        ],
        [
            'field' => 'preco',
            'label' => 'preco',
            'rules' => 'trim|required|numeric|greater_than[-1]',
        ],
        [
            'field' => 'idOsServico',
            'label' => 'idOsServico',
            'rules' => 'trim|required|numeric',
        ],
    ],
    'cobrancas' => [
        [
            'field' => 'id',
            'label' => 'id',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'tipo',
            'label' => 'tipo',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'forma_pagamento',
            'label' => 'forma_pagamento',
            'rules' => 'required|trim',
        ],
        [
            'field' => 'gateway_de_pagamento',
            'label' => 'gateway_de_pagamento',
            'rules' => 'required|trim',
        ],
    ],
];
