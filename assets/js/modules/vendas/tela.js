// Tela da venda (views/vendas/visualizar.php, #2843).
//
// Usa o mesmo motor da tela da OS: os formulários [data-os-acao] (adicionar
// produto, desconto, exclusão confirmada em modal-confirm, faturar e gerar
// cobrança) são enviados sem recarregar a página, e o servidor responde JSON
// {result, message, erros?, html?, redirecionar?} com os trechos da tela
// ([data-os-parte]) renderizados de novo. Os atributos data-os-* são o
// contrato do módulo, e valem aqui também.

export { default } from '../os/tela.js';
