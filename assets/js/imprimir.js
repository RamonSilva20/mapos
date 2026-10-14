// Abre o diálogo de impressão das views de impressão (OS, vendas, garantias,
// cupons e área do cliente). Arquivo próprio, e não <script> inline, para a
// CSP em enforce não precisar de 'unsafe-inline' (#2878). Espera a página e as
// imagens (logo, anexos) carregarem, para o que vai ao papel estar completo.
window.addEventListener('load', () => window.print());
