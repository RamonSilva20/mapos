<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Mapa de permissões das rotas do painel
| -------------------------------------------------------------------------
|
| Todo controller que estende MY_Controller passa por este mapa antes de o
| método rodar. Método público que não está aqui é negado, então criar uma
| rota nova exige declarar quem pode acessá-la.
|
| Chaves: nome do controller => nome do método, como aparecem no código. A
| comparação ignora maiúsculas e minúsculas, do mesmo jeito que o roteador do
| CodeIgniter.
|
| Valores aceitos:
|
|   'vCliente'                       exige essa permissão
|   ['aOs', 'eOs']                   exige qualquer uma das permissões
|   ['todas' => ['rVenda', 'rOs']]   exige todas as permissões
|   '*'                              qualquer usuário logado
|   false                            não é rota: método público usado só
|                                    internamente, negado quando chamado
|                                    pela URL
|
| As checagens dentro de cada método continuam no lugar. Este mapa é a
| primeira barreira, não a única. tests/Unit/PermissionsMapTest.php confere
| que todo método público está mapeado e que a regra daqui bate com a
| checagem que o método já faz.
*/

$config['permissions_map'] = [
    'Arquivos' => [
        'index' => 'vArquivo',
        'gerenciar' => 'vArquivo',
        'adicionar' => 'aArquivo',
        'editar' => 'eArquivo',
        'download' => 'vArquivo',
        'excluir' => 'dArquivo',
    ],

    // O construtor exige cAuditoria para todos os métodos.
    'Auditoria' => [
        'index' => 'cAuditoria',
        'clean' => 'cAuditoria',
    ],

    'Clientes' => [
        'index' => 'vCliente',
        'gerenciar' => 'vCliente',
        'adicionar' => 'aCliente',
        'editar' => 'eCliente',
        'visualizar' => 'vCliente',
        'excluir' => 'dCliente',
    ],

    // Catálogo da biblioteca de componentes. O construtor responde 404 fora
    // de development, antes da checagem de login.
    'Componentes' => [
        'index' => '*',
    ],

    'Cobrancas' => [
        'index' => 'vCobranca',
        'cobrancas' => 'vCobranca',
        'visualizar' => 'vCobranca',
        'enviarEmail' => 'vCobranca',
        'adicionar' => 'aCobranca',
        'atualizar' => 'eCobranca',
        'confirmarPagamento' => 'eCobranca',
        'cancelar' => 'eCobranca',
        'excluir' => 'dCobranca',
    ],

    'Financeiro' => [
        'index' => 'vLancamento',
        'lancamentos' => 'vLancamento',
        'autoCompleteClienteFornecedor' => ['vLancamento'],
        'autoCompleteClienteAddReceita' => ['vLancamento'],
        'adicionarReceita' => 'aLancamento',
        'adicionarReceita_parc' => 'aLancamento',
        'adicionarDespesa' => 'aLancamento',
        'editar' => 'eLancamento',
        'excluirLancamento' => 'dLancamento',
    ],

    'Garantias' => [
        'index' => 'vGarantia',
        'gerenciar' => 'vGarantia',
        'visualizar' => 'vGarantia',
        'imprimir' => 'vGarantia',
        'imprimirGarantiaOs' => 'vGarantia',
        'adicionar' => 'aGarantia',
        'editar' => 'eGarantia',
        'excluir' => 'dGarantia',
        'autoCompleteProduto' => ['aGarantia', 'eGarantia'],
        'autoCompleteCliente' => ['aGarantia', 'eGarantia'],
        'autoCompleteUsuario' => ['aGarantia', 'eGarantia'],
    ],

    'Mapos' => [
        // Painel, conta do próprio usuário e busca. A busca filtra cada grupo
        // de resultado pela permissão de visualização correspondente.
        'index' => '*',
        'minhaConta' => '*',
        'alterarSenha' => '*',
        'pesquisar' => '*',
        'calendario' => 'vOs',
        'uploadUserImage' => 'cUsuario',
        'backup' => 'cBackup',
        'emitente' => 'cEmitente',
        'cadastrarEmitente' => 'cEmitente',
        'editarEmitente' => 'cEmitente',
        'editarLogo' => 'cEmitente',
        'emails' => 'cEmail',
        'excluirEmail' => 'cEmail',
        'configurar' => 'cSistema',
        'atualizarBanco' => 'cSistema',
        'atualizarMapos' => 'cSistema',
    ],

    'Os' => [
        'index' => 'vOs',
        'gerenciar' => 'vOs',
        'visualizar' => 'vOs',
        'imprimir' => 'vOs',
        'imprimirTermica' => 'vOs',
        'enviar_email' => 'vOs',
        'downloadanexo' => 'vOs',
        'adicionar' => 'aOs',
        'editar' => 'eOs',
        'adicionarProduto' => 'eOs',
        'excluirProduto' => 'eOs',
        'adicionarServico' => 'eOs',
        'excluirServico' => 'eOs',
        'anexar' => 'eOs',
        'excluirAnexo' => 'eOs',
        'adicionarDesconto' => 'eOs',
        'faturar' => 'eOs',
        'adicionarAnotacao' => 'eOs',
        'excluirAnotacao' => 'eOs',
        'excluir' => 'dOs',
        'autoCompleteProduto' => ['aOs', 'eOs'],
        'autoCompleteTermoGarantia' => ['aOs', 'eOs'],
        'autoCompleteServico' => ['aOs', 'eOs'],
        'autoCompleteProdutoSaida' => ['aOs', 'eOs', 'aVenda', 'eVenda'],
        'autoCompleteCliente' => ['aOs', 'eOs', 'rOs', 'aVenda', 'eVenda', 'rVenda'],
        'autoCompleteUsuario' => ['aOs', 'eOs', 'rOs', 'aVenda', 'eVenda', 'rVenda'],
        // Auxiliares da geração do QR Code do PIX, chamados por visualizar()
        // e imprimir(). Não são rotas.
        'validarCPF' => false,
        'validarCNPJ' => false,
        'formatarChave' => false,
    ],

    // O construtor exige cPermissao para todos os métodos.
    'Permissoes' => [
        'index' => 'cPermissao',
        'gerenciar' => 'cPermissao',
        'adicionar' => 'cPermissao',
        'editar' => 'cPermissao',
        'desativar' => 'cPermissao',
    ],

    'Produtos' => [
        'index' => 'vProduto',
        'gerenciar' => 'vProduto',
        'visualizar' => 'vProduto',
        'adicionar' => 'aProduto',
        'editar' => 'eProduto',
        'atualizar_estoque' => 'eProduto',
        'excluir' => 'dProduto',
    ],

    'Relatorios' => [
        // index() só redireciona para o painel.
        'index' => '*',
        'clientes' => 'rCliente',
        'clientesCustom' => 'rCliente',
        'clientesRapid' => 'rCliente',
        'produtos' => 'rProduto',
        'produtosRapid' => 'rProduto',
        'produtosRapidMin' => 'rProduto',
        'produtosCustom' => 'rProduto',
        // Hoje não verifica permissão nenhuma. Mantido como está para não
        // mudar comportamento neste PR; ver a descrição do #2866.
        'produtosEtiquetas' => 'rProduto',
        'sku' => ['todas' => ['rVenda', 'rOs']],
        'skuRapid' => ['todas' => ['rVenda', 'rOs']],
        'skuCustom' => ['todas' => ['rVenda', 'rOs']],
        'servicos' => 'rServico',
        'servicosCustom' => 'rServico',
        'servicosRapid' => 'rServico',
        'os' => 'rOs',
        'osRapid' => 'rOs',
        'osCustom' => 'rOs',
        'financeiro' => 'rFinanceiro',
        'financeiroRapid' => 'rFinanceiro',
        'financeiroCustom' => 'rFinanceiro',
        'receitasBrutasMei' => 'rFinanceiro',
        'receitasBrutasRapid' => 'rFinanceiro',
        'receitasBrutasCustom' => 'rFinanceiro',
        'vendas' => 'rVenda',
        'vendasRapid' => 'rVenda',
        'vendasCustom' => 'rVenda',
    ],

    'Servicos' => [
        'index' => 'vServico',
        'gerenciar' => 'vServico',
        'adicionar' => 'aServico',
        'editar' => 'eServico',
        'excluir' => 'dServico',
    ],

    // O construtor exige cUsuario para todos os métodos.
    'Usuarios' => [
        'index' => 'cUsuario',
        'gerenciar' => 'cUsuario',
        'adicionar' => 'cUsuario',
        'editar' => 'cUsuario',
        'excluir' => 'cUsuario',
    ],

    'Vendas' => [
        'index' => 'vVenda',
        'gerenciar' => 'vVenda',
        'visualizar' => 'vVenda',
        'visualizarVenda' => 'vVenda',
        'imprimir' => 'vVenda',
        'imprimirTermica' => 'vVenda',
        'imprimirVendaOrcamento' => 'vVenda',
        'adicionar' => 'aVenda',
        'editar' => 'eVenda',
        'adicionarProduto' => 'eVenda',
        'excluirProduto' => 'eVenda',
        'adicionarDesconto' => 'eVenda',
        'faturar' => 'eVenda',
        'excluir' => 'dVenda',
        'autoCompleteProduto' => ['aVenda', 'eVenda'],
        'autoCompleteCliente' => ['aVenda', 'eVenda'],
        'autoCompleteUsuario' => ['aVenda', 'eVenda'],
        // Auxiliares da geração do QR Code do PIX. Não são rotas.
        'validarCPF' => false,
        'validarCNPJ' => false,
        'formatarChave' => false,
    ],
];
