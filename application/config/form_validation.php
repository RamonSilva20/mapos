<?php $config = array (
  'clientes' => 
  array (
    0 => 
    array (
      'field' => 'nomeCliente',
      'label' => 'Nome',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'documento',
      'label' => 'CPF/CNPJ',
      'rules' => 'required',
    ),
    2 => 
    array (
      'field' => 'telefone',
      'label' => 'Telefone',
      'rules' => '',
    ),
    3 => 
    array (
      'field' => 'celular',
      'label' => 'Celular',
      'rules' => '',
    ),
    4 => 
    array (
      'field' => 'email',
      'label' => 'Email',
      'rules' => '|valid_email',
    ),
    5 => 
    array (
      'field' => 'rua',
      'label' => 'Rua',
      'rules' => '',
    ),
    6 => 
    array (
      'field' => 'numero',
      'label' => 'Número',
      'rules' => '',
    ),
    7 => 
    array (
      'field' => 'bairro',
      'label' => 'Bairro',
      'rules' => '',
    ),
    8 => 
    array (
      'field' => 'cidade',
      'label' => 'Cidade',
      'rules' => '',
    ),
    9 => 
    array (
      'field' => 'estado',
      'label' => 'Estado',
      'rules' => '',
    ),
    10 => 
    array (
      'field' => 'cep',
      'label' => 'CEP',
      'rules' => '',
    ),
  ),
  'servicos' => 
  array (
    0 => 
    array (
      'field' => 'nome',
      'label' => 'Nome',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'descricao',
      'label' => '',
      'rules' => 'trim',
    ),
    2 => 
    array (
      'field' => 'preco',
      'label' => '',
      'rules' => 'required',
    ),
  ),
  'produtos' => 
  array (
    0 => 
    array (
      'field' => 'descricao',
      'label' => '',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'unidade',
      'label' => 'Unidade',
      'rules' => 'required',
    ),
    2 => 
    array (
      'field' => 'precoCompra',
      'label' => 'Preo de Compra',
      'rules' => 'required',
    ),
    3 => 
    array (
      'field' => 'precoVenda',
      'label' => 'Preo de Venda',
      'rules' => 'required',
    ),
    4 => 
    array (
      'field' => 'estoque',
      'label' => 'Estoque',
      'rules' => 'required',
    ),
    5 => 
    array (
      'field' => 'estoqueMinimo',
      'label' => 'Estoque Mnimo',
      'rules' => 'trim',
    ),
  ),
  'usuarios' => 
  array (
    0 => 
    array (
      'field' => 'nome',
      'label' => 'Nome',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'rg',
      'label' => 'RG',
      'rules' => 'required',
    ),
    2 => 
    array (
      'field' => 'usuario',
      'label' => 'Usuário',
      'rules' => 'required',
    ),
    3 => 
    array (
      'field' => 'cpf',
      'label' => 'CPF',
      'rules' => 'required|is_unique[usuarios.cpf]',
    ),
    4 => 
    array (
      'field' => 'rua',
      'label' => 'Rua',
      'rules' => 'required',
    ),
    5 => 
    array (
      'field' => 'numero',
      'label' => 'Numero',
      'rules' => 'required',
    ),
    6 => 
    array (
      'field' => 'bairro',
      'label' => 'Bairro',
      'rules' => 'required',
    ),
    7 => 
    array (
      'field' => 'cidade',
      'label' => 'Cidade',
      'rules' => 'required',
    ),
    8 => 
    array (
      'field' => 'estado',
      'label' => 'Estado',
      'rules' => 'required',
    ),
    9 => 
    array (
      'field' => 'email',
      'label' => 'Email',
      'rules' => 'required|valid_email',
    ),
    10 => 
    array (
      'field' => 'senha',
      'label' => 'Senha',
      'rules' => 'required',
    ),
    11 => 
    array (
      'field' => 'telefone',
      'label' => 'Telefone',
      'rules' => 'required',
    ),
    12 => 
    array (
      'field' => 'situacao',
      'label' => 'Situacao',
      'rules' => 'required',
    ),
  ),
  'usuariosEdita' => 
  array (
    0 => 
    array (
      'field' => 'nome',
      'label' => 'Nome',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'rg',
      'label' => 'RG',
      'rules' => 'required',
    ),
    2 => 
    array (
      'field' => 'usuario',
      'label' => 'Usuário',
      'rules' => 'required',
    ),
    3 => 
    array (
      'field' => 'cpf',
      'label' => 'CPF',
      'rules' => 'required',
    ),
    4 => 
    array (
      'field' => 'rua',
      'label' => 'Rua',
      'rules' => 'required',
    ),
    5 => 
    array (
      'field' => 'numero',
      'label' => 'Numero',
      'rules' => 'required',
    ),
    6 => 
    array (
      'field' => 'bairro',
      'label' => 'Bairro',
      'rules' => 'required',
    ),
    7 => 
    array (
      'field' => 'cidade',
      'label' => 'Cidade',
      'rules' => 'required',
    ),
    8 => 
    array (
      'field' => 'estado',
      'label' => 'Estado',
      'rules' => 'required',
    ),
    9 => 
    array (
      'field' => 'email',
      'label' => 'Email',
      'rules' => 'required|valid_email',
    ),
    10 => 
    array (
      'field' => 'telefone',
      'label' => 'Telefone',
      'rules' => 'required',
    ),
    11 => 
    array (
      'field' => 'situacao',
      'label' => 'Situacao',
      'rules' => 'required',
    ),
  ),
  'os' => 
  array (
    0 => 
    array (
      'field' => 'dataInicial',
      'label' => 'DataInicial',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'dataFinal',
      'label' => 'DataFinal',
      'rules' => 'trim',
    ),
    2 => 
    array (
      'field' => 'garantia',
      'label' => 'Garantia',
      'rules' => 'trim',
    ),
    3 => 
    array (
      'field' => 'descricaoProduto',
      'label' => 'DescricaoProduto',
      'rules' => 'trim',
    ),
    4 => 
    array (
      'field' => 'defeito',
      'label' => 'Defeito',
      'rules' => 'trim',
    ),
    5 => 
    array (
      'field' => 'status',
      'label' => 'Status',
      'rules' => 'required',
    ),
    6 => 
    array (
      'field' => 'observacoes',
      'label' => 'Observacoes',
      'rules' => 'trim',
    ),
    7 => 
    array (
      'field' => 'clientes_id',
      'label' => 'clientes',
      'rules' => 'trim|required',
    ),
    8 => 
    array (
      'field' => 'usuarios_id',
      'label' => 'usuarios_id',
      'rules' => 'trim|required',
    ),
    9 => 
    array (
      'field' => 'laudoTecnico',
      'label' => 'Laudo Tecnico',
      'rules' => 'trim',
    ),
  ),
  'tiposUsuario' => 
  array (
    0 => 
    array (
      'field' => 'nomeTipo',
      'label' => 'NomeTipo',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'situacao',
      'label' => 'Situacao',
      'rules' => 'required',
    ),
  ),
  'receita' => 
  array (
    0 => 
    array (
      'field' => 'descricao',
      'label' => 'Descrição',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'valor',
      'label' => 'Valor',
      'rules' => 'required',
    ),
    2 => 
    array (
      'field' => 'vencimento',
      'label' => 'Data Vencimento',
      'rules' => 'required',
    ),
    3 => 
    array (
      'field' => 'cliente',
      'label' => 'Cliente',
      'rules' => 'required',
    ),
    4 => 
    array (
      'field' => 'tipo',
      'label' => 'Tipo',
      'rules' => 'required',
    ),
  ),
  'despesa' => 
  array (
    0 => 
    array (
      'field' => 'descricao',
      'label' => 'Descrição',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'valor',
      'label' => 'Valor',
      'rules' => 'required',
    ),
    2 => 
    array (
      'field' => 'vencimento',
      'label' => 'Data Vencimento',
      'rules' => 'required',
    ),
    3 => 
    array (
      'field' => 'fornecedor',
      'label' => 'Fornecedor',
      'rules' => 'required',
    ),
    4 => 
    array (
      'field' => 'tipo',
      'label' => 'Tipo',
      'rules' => 'required',
    ),
  ),
  'vendas' => 
  array (
    0 => 
    array (
      'field' => 'dataVenda',
      'label' => 'Data da Venda',
      'rules' => 'required',
    ),
    1 => 
    array (
      'field' => 'clientes_id',
      'label' => 'clientes',
      'rules' => 'trim|required',
    ),
    2 => 
    array (
      'field' => 'usuarios_id',
      'label' => 'usuarios_id',
      'rules' => 'trim|required',
    ),
  ),
);