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

### 🟡 **FASE 2: Dashboard Financeiro** (PRIORIDADE ALTA)
**Tempo estimado:** 2-3 dias  
**Status:** ⚪ Não iniciado

#### Objetivos:
- [ ] Criar view do dashboard (`dashboard.php`)
- [ ] Criar método no controller (`Financeiro::dashboard()`)
- [ ] Criar métodos no model para estatísticas
- [ ] Implementar cards com totais
- [ ] Implementar gráficos básicos
- [ ] Implementar lista de alertas

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
- [ ] Total a Receber (pendente)
- [ ] Total a Pagar (pendente)
- [ ] Saldo Atual
- [ ] Contas Vencidas (alerta vermelho)

**2. Gráficos:**
- [ ] Receitas vs Despesas (mensal)
- [ ] Fluxo de caixa (últimos 6 meses)
- [ ] Despesas por categoria (se implementado)

**3. Tabelas de Alertas:**
- [ ] Contas a vencer (próximos 7 dias)
- [ ] Contas vencidas
- [ ] Maiores receitas do mês
- [ ] Maiores despesas do mês

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

### 🟢 **FASE 3: Simplificação da Interface** (PRIORIDADE MÉDIA)
**Tempo estimado:** 1 dia  
**Status:** ⚪ Não iniciado

#### Objetivos:
- [ ] Reduzir colunas da tabela de lançamentos
- [ ] Melhorar visual com badges coloridos
- [ ] Adicionar tooltips informativos
- [ ] Implementar modal de detalhes
- [ ] Melhorar filtros

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

**Depois (7 colunas):**
```
# | Tipo | Cliente/Fornecedor | Descrição | Vencimento | Valor | Status | Ações
```

#### Melhorias Visuais:
- [ ] Badge verde para "Receita"
- [ ] Badge vermelho para "Despesa"
- [ ] Badge azul para "Pago"
- [ ] Badge laranja para "Pendente"
- [ ] Badge vermelho escuro para "Vencido"
- [ ] Tooltip com detalhes ao passar mouse
- [ ] Modal com informações completas ao clicar

#### Critérios de Aceitação:
- [ ] Tabela mais limpa e legível
- [ ] Informações essenciais visíveis
- [ ] Detalhes acessíveis via tooltip/modal
- [ ] Mantém todas as funcionalidades

---

### 🔵 **FASE 4: Categorias e Contas** (PRIORIDADE MÉDIA)
**Tempo estimado:** 2-3 dias  
**Status:** ⚪ Não iniciado

#### Objetivos:
- [ ] Ativar uso de categorias na interface
- [ ] Ativar uso de contas bancárias
- [ ] Criar CRUD de categorias
- [ ] Criar CRUD de contas
- [ ] Implementar relatórios por categoria
- [ ] Implementar controle de saldo por conta

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
- [ ] Listar categorias
- [ ] Adicionar categoria
- [ ] Editar categoria
- [ ] Excluir categoria (se não tiver lançamentos)
- [ ] Filtrar por tipo (receita/despesa)
- [ ] Dropdown de categorias no formulário de lançamento
- [ ] Relatório de lançamentos por categoria

#### Funcionalidades - Contas:
- [ ] Listar contas
- [ ] Adicionar conta (banco, número, saldo inicial)
- [ ] Editar conta
- [ ] Excluir conta (se não tiver lançamentos)
- [ ] Visualizar extrato por conta
- [ ] Atualização automática de saldo
- [ ] Transferência entre contas
- [ ] Dropdown de contas no formulário de lançamento

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
- [ ] Categorias funcionando corretamente
- [ ] Contas com saldo atualizado automaticamente
- [ ] Relatórios por categoria precisos
- [ ] Extrato por conta funcional
- [ ] Transferência entre contas registrada

---

### 🟣 **FASE 5: Integração Automática** (PRIORIDADE MÉDIA)
**Tempo estimado:** 1-2 dias  
**Status:** ⚪ Não iniciado

#### Objetivos:
- [ ] Integrar OS → Lançamento automático
- [ ] Integrar Vendas → Lançamento automático
- [ ] Adicionar checkbox de controle
- [ ] Evitar duplicação

#### Arquivos a modificar:
```
application/
├── controllers/
│   ├── Os.php (MODIFICAR)
│   └── Vendas.php (MODIFICAR)
└── models/
    └── Financeiro_model.php (MODIFICAR)
```

#### Funcionalidades - OS:
- [ ] Ao mudar para "Faturado" → perguntar se cria lançamento
- [ ] Checkbox: "Gerar lançamento financeiro"
- [ ] Preencher dados automaticamente:
  - Cliente (da OS)
  - Valor (total da OS)
  - Descrição ("Pagamento de OS #123")
  - Tipo: Receita
  - Data vencimento: data final da OS
- [ ] Vincular lançamento à OS (`os.lancamento`)
- [ ] Evitar duplicação (verificar se já existe)

#### Funcionalidades - Vendas:
- [ ] Ao finalizar venda → perguntar se cria lançamento
- [ ] Checkbox: "Gerar lançamento financeiro"
- [ ] Preencher dados automaticamente:
  - Cliente (da venda)
  - Valor (total da venda)
  - Descrição ("Pagamento de Venda #456")
  - Tipo: Receita
  - Data vencimento: data da venda
- [ ] Vincular lançamento à venda (`vendas.lancamentos_id`)
- [ ] Evitar duplicação

#### Critérios de Aceitação:
- [ ] Integração opcional (usuário decide)
- [ ] Dados preenchidos corretamente
- [ ] Vinculação funcionando
- [ ] Não cria duplicatas
- [ ] Possível editar lançamento depois

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
- [ ] FASE 2: Dashboard (0%)
- [ ] FASE 3: Simplificação (0%)
- [ ] FASE 4: Categorias e Contas (0%)
- [ ] FASE 5: Integração (0%)
- [ ] FASE 6: Alertas (0%)

### Progresso Total: 16.7%

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

