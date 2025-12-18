# 🗺️ ROADMAP - MELHORIAS FINANCEIRAS

## 📌 Branch: `feature/melhorias-financeiro`

**Data de Início:** 17/12/2025  
**Objetivo:** Implementar melhorias no módulo financeiro do MAPOS

---

## 🎯 FASES DE IMPLEMENTAÇÃO

### ✅ **FASE 0: Preparação** (CONCLUÍDO)
- [x] Análise completa do módulo financeiro
- [x] Identificação de problemas e oportunidades
- [x] Criação de documentação (ANALISE_FINANCEIRO.md)
- [x] Criação da branch `feature/melhorias-financeiro`

---

### ✅ **FASE 1: Recibos de Pagamento** (CONCLUÍDO)
**Tempo estimado:** 1 dia  
**Status:** ✅ Concluído

#### Objetivos:
- [x] Criar view de impressão de recibo (`imprimirRecibo.php`)
- [x] Adicionar método no controller (`Financeiro::imprimirRecibo()`)
- [x] Adicionar método no model (`Financeiro_model::getLancamentoById()`)
- [x] Adicionar função helper `valorPorExtenso()` para converter valores
- [x] Adicionar botão "Imprimir Recibo" na lista de lançamentos
- [x] Implementar validação (só imprime se estiver pago)

#### Arquivos a criar/modificar:
```
application/
├── controllers/Financeiro.php (MODIFICAR)
├── models/Financeiro_model.php (MODIFICAR)
└── views/
    └── financeiro/
        └── imprimirRecibo.php (CRIAR)
```

#### Funcionalidades:
- ✅ Impressão A4 profissional
- ✅ Dados do emitente (logo, nome, endereço)
- ✅ Dados do pagador (cliente/fornecedor)
- ✅ Valor por extenso
- ✅ Descrição do pagamento
- ✅ Forma de pagamento
- ✅ Data do pagamento
- ✅ Assinatura
- ✅ Numeração do recibo

#### Critérios de Aceitação:
- [ ] Recibo só pode ser impresso se lançamento estiver marcado como "Pago"
- [ ] Layout profissional e limpo
- [ ] Informações completas e legíveis
- [ ] Compatível com impressão A4
- [ ] Botão visível apenas para lançamentos pagos

---

### ✅ **FASE 2: Dashboard Financeiro** (CONCLUÍDO)
**Tempo estimado:** 2-3 dias  
**Status:** ✅ Concluído

#### Objetivos:
- [x] Criar view do dashboard (`dashboard.php`)
- [x] Criar método no controller (`Financeiro::dashboard()`)
- [x] Criar métodos no model para estatísticas
- [x] Implementar cards com totais (4 cards)
- [x] Implementar gráficos básicos (2 gráficos)
- [x] Implementar lista de alertas (vencimentos)

#### Arquivos a criar/modificar:
```
application/
├── controllers/Financeiro.php (MODIFICAR)
├── models/Financeiro_model.php (MODIFICAR)
└── views/
    └── financeiro/
        └── dashboard.php (CRIAR)
```

#### Componentes do Dashboard:

**1. Cards Superiores:**
- [x] Total a Receber (pendente)
- [x] Total a Pagar (pendente)
- [x] Saldo Atual
- [x] Contas Vencidas (alerta vermelho)

**2. Gráficos:**
- [x] Receitas vs Despesas (mensal)
- [x] Fluxo de caixa (últimos 6 meses)
- [ ] Despesas por categoria (se implementado) - Futuro

**3. Tabelas de Alertas:**
- [x] Contas a vencer (próximos 7 dias)
- [x] Contas vencidas
- [ ] Maiores receitas do mês - Futuro
- [ ] Maiores despesas do mês - Futuro

#### Tecnologias:
- Chart.js ou similar para gráficos
- Bootstrap cards para layout
- AJAX para atualização dinâmica

#### Critérios de Aceitação:
- [ ] Dashboard carrega rapidamente (< 2 segundos)
- [ ] Informações precisas e atualizadas
- [ ] Visual limpo e profissional
- [ ] Responsivo (funciona em mobile)
- [ ] Links diretos para lançamentos

---

### ✅ **FASE 3: Simplificação da Interface** (CONCLUÍDO)
**Tempo estimado:** 1 dia  
**Status:** ✅ Concluído

#### Objetivos:
- [x] Reduzir colunas da tabela de lançamentos (12 → 8)
- [x] Melhorar visual com badges coloridos
- [x] Adicionar tooltips informativos
- [x] Implementar modal de detalhes
- [x] Melhorar CSS e visual geral

#### Arquivos a modificar:
```
application/
└── views/
    └── financeiro/
        └── lancamentos.php (MODIFICAR)
```

#### Mudanças na Tabela:

**Antes (12 colunas):**
```
# | Tipo | Cliente/Fornecedor | Descrição | Vencimento | Status | 
Observações | Forma Pgto | Valor(+) | Desconto(-) | Total(=) | Ações
```

**Depois (8 colunas):**
```
# | Tipo | Cliente/Fornecedor | Descrição | Vencimento | Valor | Status | Ações
```

#### Melhorias Visuais:
- [x] Badge verde para "Receita"
- [x] Badge vermelho para "Despesa"
- [x] Badge azul para "Pago"
- [x] Badge laranja para "Pendente"
- [x] Badge vermelho escuro para "Vencido"
- [x] Tooltip com detalhes ao passar mouse
- [x] Modal com informações completas ao clicar
- [x] Botão "Ver Detalhes" na coluna Ações

#### Critérios de Aceitação:
- [ ] Tabela mais limpa e legível
- [ ] Informações essenciais visíveis
- [ ] Detalhes acessíveis via tooltip/modal
- [ ] Mantém todas as funcionalidades

---

### ✅ **FASE 4: Categorias e Contas** (CONCLUÍDO)
**Tempo estimado:** 2-3 dias  
**Status:** ✅ Concluído

#### Objetivos:
- [x] Ativar uso de categorias na interface
- [x] Ativar uso de contas bancárias
- [x] Criar CRUD de categorias
- [x] Criar CRUD de contas
- [x] Implementar controle de saldo por conta
- [x] Implementar transferência entre contas

#### Arquivos a criar/modificar:
```
application/
├── controllers/
│   ├── Financeiro.php (MODIFICAR)
│   ├── Categorias.php (CRIAR)
│   └── Contas.php (CRIAR)
├── models/
│   ├── Categorias_model.php (CRIAR)
│   └── Contas_model.php (CRIAR)
└── views/
    ├── categorias/
    │   ├── categorias.php (CRIAR)
    │   └── ... (CRUD completo)
    └── contas/
        ├── contas.php (CRIAR)
        └── ... (CRUD completo)
```

#### Funcionalidades - Categorias:
- [x] Listar categorias
- [x] Adicionar categoria
- [x] Editar categoria
- [x] Excluir categoria (se não tiver lançamentos)
- [x] Filtrar por tipo (receita/despesa)
- [x] Dropdown de categorias no formulário de lançamento
- [ ] Relatório de lançamentos por categoria (futuro)

#### Funcionalidades - Contas:
- [x] Listar contas
- [x] Adicionar conta (banco, número, saldo inicial)
- [x] Editar conta
- [x] Excluir conta (se não tiver lançamentos)
- [x] Visualizar extrato por conta
- [x] Atualização automática de saldo
- [x] Transferência entre contas
- [x] Dropdown de contas no formulário de lançamento

#### Categorias Padrão (Sugestão):
**Receitas:**
- Vendas
- Serviços
- Outras Receitas

**Despesas:**
- Compras
- Salários
- Aluguel
- Impostos
- Energia
- Água
- Internet
- Telefone
- Outras Despesas

#### Critérios de Aceitação:
- [x] Categorias funcionando corretamente
- [x] Contas com saldo atualizado automaticamente
- [x] Extrato por conta funcional
- [x] Transferência entre contas registrada
- [ ] Relatórios por categoria precisos (futuro)

---

### ✅ **FASE 5: Integração Automática** (CONCLUÍDO PARCIALMENTE)
**Tempo estimado:** 3-4 dias (inclui pagamento parcial)  
**Status:** ⏳ Em andamento (5.1 e 5.2-OS concluídos)

#### Objetivos:
- [x] **5.1: Pagamento Parcial (Sinal)** - Implementar suporte a pagamentos parciais
- [x] Integrar OS → Lançamento automático (com forma de pagamento e parcelas)
- [ ] Integrar Vendas → Lançamento automático (com forma de pagamento e parcelas)
- [x] Adicionar checkbox de controle
- [x] Evitar duplicação

#### Arquivos a modificar:
```
application/
├── controllers/
│   ├── Os.php (MODIFICAR)
│   └── Vendas.php (MODIFICAR)
└── models/
    └── Financeiro_model.php (MODIFICAR)
```

#### Funcionalidades - Pagamento Parcial (5.1):
- [x] Adicionar campo `valor_pago` ou tabela `pagamentos_parciais`
- [x] Interface para registrar pagamentos parciais
- [x] Exibir progresso visual (barra de progresso)
- [x] Histórico de pagamentos parciais
- [ ] Atualizar dashboard para considerar pagamentos parciais
- [x] Status: Pendente / Parcial / Pago

#### Funcionalidades - OS:
- [x] Adicionar campos: `forma_pgto`, `parcelas`, `entrada` em OS
- [x] Ao mudar para "Faturado" → perguntar se cria lançamento
- [x] Modal com opções: forma de pagamento, parcelas, entrada
- [x] Preencher dados automaticamente:
  - Cliente (da OS)
  - Valor (total da OS)
  - Descrição ("Pagamento de OS #123")
  - Tipo: Receita
  - Data vencimento: data final da OS
  - **Forma de pagamento** (da OS)
  - **Parcelas** (se houver)
  - **Entrada** (se houver)
- [x] Criar múltiplos lançamentos se parcelado
- [x] Criar lançamento de entrada (pago) + parcelas (pendentes)
- [x] Vincular lançamento à OS (`os.lancamento`)
- [x] Evitar duplicação (verificar se já existe)

#### Funcionalidades - Vendas:
- [ ] Adicionar campos: `forma_pgto`, `parcelas`, `entrada` em Vendas
- [ ] Ao finalizar venda → perguntar se cria lançamento
- [ ] Modal com opções: forma de pagamento, parcelas, entrada
- [ ] Preencher dados automaticamente:
  - Cliente (da venda)
  - Valor (total da venda)
  - Descrição ("Pagamento de Venda #456")
  - Tipo: Receita
  - Data vencimento: data da venda
  - **Forma de pagamento** (da venda)
  - **Parcelas** (se houver)
  - **Entrada** (se houver)
- [ ] Criar múltiplos lançamentos se parcelado
- [ ] Criar lançamento de entrada (pago) + parcelas (pendentes)
- [ ] Vincular lançamento à venda (`vendas.lancamentos_id`)
- [ ] Evitar duplicação

#### Critérios de Aceitação:
- [ ] Pagamento parcial funcionando corretamente
- [ ] Integração opcional (usuário decide)
- [ ] Dados preenchidos corretamente (incluindo forma de pagamento e parcelas)
- [ ] Vinculação funcionando (OS/Venda → Financeiro)
- [ ] Não cria duplicatas
- [ ] Possível editar lançamento depois
- [ ] Suporte a entrada + parcelas
- [ ] Dashboard atualizado com pagamentos parciais

#### 📝 Notas:
- **Pagamento Parcial:** Necessário para suportar pagamento de sinal (ex: R$ 500 de R$ 1000)
- **Integração Futura:** Forma de pagamento e parcelas serão integradas automaticamente de OS/Vendas
- Ver documento `MELHORIAS_FUTURAS.md` para detalhes técnicos

---

### 🟠 **FASE 6: Alertas e Notificações** (PRIORIDADE BAIXA)
**Tempo estimado:** 1-2 dias  
**Status:** ⚪ Não iniciado

#### Objetivos:
- [ ] Sistema de alertas no dashboard
- [ ] Notificações de vencimento
- [ ] E-mails automáticos (opcional)
- [ ] Badge de notificações no menu

#### Funcionalidades:
- [ ] Alerta de contas a vencer (3 dias antes)
- [ ] Alerta de contas vencidas
- [ ] Badge no menu "Financeiro" com quantidade
- [ ] E-mail para cliente (opcional)
- [ ] Configuração de alertas

#### Critérios de Aceitação:
- [ ] Alertas precisos
- [ ] Notificações não invasivas
- [ ] E-mails opcionais
- [ ] Configurável

---

## 📊 PROGRESSO GERAL

### Status das Fases:
- [x] FASE 1: Recibos (100%) ✅
- [x] FASE 2: Dashboard (100%) ✅
- [x] FASE 3: Simplificação (100%) ✅
- [x] FASE 4: Categorias e Contas (100%) ✅
- [~] FASE 5: Integração (75%) ⏳ (falta Vendas e Dashboard)
- [ ] FASE 6: Alertas (0%)

### Progresso Total: 79.2%

---

## 🧪 TESTES

### Checklist de Testes por Fase:

#### FASE 1 - Recibos:
- [ ] Imprimir recibo de receita
- [ ] Imprimir recibo de despesa
- [ ] Verificar layout em impressora
- [ ] Testar com diferentes valores
- [ ] Testar valor por extenso
- [ ] Verificar dados do emitente
- [ ] Verificar botão só aparece para pagos

#### FASE 2 - Dashboard:
- [ ] Verificar totais corretos
- [ ] Testar gráficos
- [ ] Verificar alertas de vencimento
- [ ] Testar responsividade
- [ ] Verificar performance

#### FASE 3 - Interface:
- [ ] Verificar todas as colunas visíveis
- [ ] Testar tooltips
- [ ] Testar modal de detalhes
- [ ] Verificar badges coloridos
- [ ] Testar filtros

#### FASE 4 - Categorias/Contas:
- [ ] CRUD de categorias completo
- [ ] CRUD de contas completo
- [ ] Atualização de saldo automática
- [ ] Relatórios por categoria
- [ ] Extrato por conta
- [ ] Transferência entre contas

#### FASE 5 - Integração:
- [ ] OS → Lançamento
- [ ] Vendas → Lançamento
- [ ] Verificar vinculação
- [ ] Testar não duplicação
- [ ] Edição de lançamento vinculado

#### FASE 6 - Alertas:
- [ ] Alertas de vencimento
- [ ] Badge no menu
- [ ] E-mails (se implementado)
- [ ] Configurações

---

## 📝 NOTAS DE DESENVOLVIMENTO

### Convenções:
- Seguir padrão CodeIgniter 3
- Comentários em português
- Commits descritivos em português
- Testar antes de commitar

### Branches:
- `feature/melhorias-financeiro` - branch principal
- Criar sub-branches se necessário para features grandes

### Commits:
- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `refactor:` - Refatoração
- `style:` - Mudanças de estilo/formatação
- `docs:` - Documentação
- `test:` - Testes

### Exemplo de Commit:
```
feat: Adiciona impressão de recibos de pagamento

- Cria view imprimirRecibo.php
- Adiciona método no controller
- Adiciona botão na lista de lançamentos
- Implementa valor por extenso
```

---

## 🎯 PRÓXIMOS PASSOS

1. **Iniciar FASE 1** - Recibos de Pagamento
2. Testar em ambiente de desenvolvimento
3. Solicitar feedback do usuário
4. Ajustar conforme necessário
5. Avançar para FASE 2

---

## 📞 SUPORTE

Dúvidas ou problemas durante a implementação:
- Consultar `ANALISE_FINANCEIRO.md`
- Consultar documentação do CodeIgniter
- Revisar código existente

---

**Última atualização:** 17/12/2025  
**Versão:** 1.0  
**Branch:** feature/melhorias-financeiro

