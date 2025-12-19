# 🚀 MELHORIAS FUTURAS - MÓDULO FINANCEIRO

**Data de Criação:** 17/12/2025  
**Status:** 📝 Documentado para implementação futura

---

## 💰 PAGAMENTO PARCIAL (SINAL)

### 📋 **Descrição:**
Atualmente, o sistema financeiro só permite marcar um lançamento como "Pago" (100%) ou "Pendente" (0%). Não há suporte para pagamentos parciais, como pagamento de sinal.

### 🎯 **Necessidade:**
Permitir registrar pagamentos parciais em um lançamento, por exemplo:
- Lançamento de **R$ 1.000,00**
- Cliente pagou **R$ 500,00** (sinal)
- Sistema deve mostrar: **R$ 500,00 pago** | **R$ 500,00 pendente**

### 🔧 **Implementação Proposta:**

#### 1. **Alterações no Banco de Dados:**
```sql
-- Adicionar campo para valor pago
ALTER TABLE `lancamentos` 
ADD COLUMN `valor_pago` DECIMAL(10,2) DEFAULT 0.00 AFTER `valor_desconto`;

-- Ou criar tabela de pagamentos parciais (melhor opção)
CREATE TABLE `pagamentos_parciais` (
  `idPagamentos` INT AUTO_INCREMENT PRIMARY KEY,
  `lancamentos_id` INT NOT NULL,
  `valor_pago` DECIMAL(10,2) NOT NULL,
  `data_pagamento` DATE NOT NULL,
  `forma_pgto` VARCHAR(100),
  `observacoes` TEXT,
  `usuarios_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`lancamentos_id`) REFERENCES `lancamentos`(`idLancamentos`),
  FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios`(`idUsuarios`)
);
```

#### 2. **Lógica de Status:**
- **Pendente:** `valor_pago = 0` ou `valor_pago < valor_desconto`
- **Parcial:** `0 < valor_pago < valor_desconto`
- **Pago:** `valor_pago >= valor_desconto`

#### 3. **Interface:**
- Botão "Registrar Pagamento Parcial" na lista de lançamentos
- Modal para inserir valor pago, data e forma de pagamento
- Exibir progresso visual (barra de progresso)
- Mostrar histórico de pagamentos parciais

#### 4. **Dashboard:**
- Atualizar cards para considerar pagamentos parciais
- Mostrar "Receitas Parcialmente Pagas"
- Calcular saldo considerando valores parciais

---

## 🔗 INTEGRAÇÃO AUTOMÁTICA OS/VENDAS → FINANCEIRO

### 📋 **Descrição:**
Atualmente, os lançamentos financeiros são criados manualmente. O sistema deve permitir integração automática com OS e Vendas, incluindo forma de pagamento e parcelas.

### 🎯 **Necessidade:**
Quando uma OS ou Venda for finalizada/faturada, o sistema deve:
1. **Perguntar ao usuário** se deseja gerar lançamento financeiro
2. **Preencher automaticamente:**
   - Cliente/Fornecedor
   - Valor total
   - Descrição (ex: "Pagamento de OS #123")
   - Tipo (Receita/Despesa)
   - Data de vencimento
   - **Forma de pagamento** (da OS/Venda)
   - **Parcelas** (se houver)
3. **Criar lançamentos:**
   - Se parcelado: criar múltiplos lançamentos (um por parcela)
   - Se entrada: criar lançamento de entrada (pago) + parcelas (pendentes)
   - Se pagamento parcial: criar lançamento com valor parcial

### 🔧 **Implementação Proposta:**

#### 1. **Campos Adicionais em OS/Vendas:**
```sql
-- Adicionar campos de pagamento em OS
ALTER TABLE `os` 
ADD COLUMN `forma_pgto` VARCHAR(100) AFTER `totalServicos`,
ADD COLUMN `parcelas` INT DEFAULT 1 AFTER `forma_pgto`,
ADD COLUMN `entrada` DECIMAL(10,2) DEFAULT 0.00 AFTER `parcelas`,
ADD COLUMN `lancamento_gerado` TINYINT(1) DEFAULT 0 AFTER `entrada`;

-- Adicionar campos de pagamento em Vendas
ALTER TABLE `vendas` 
ADD COLUMN `forma_pgto` VARCHAR(100) AFTER `valorTotal`,
ADD COLUMN `parcelas` INT DEFAULT 1 AFTER `forma_pgto`,
ADD COLUMN `entrada` DECIMAL(10,2) DEFAULT 0.00 AFTER `parcelas`,
ADD COLUMN `lancamento_gerado` TINYINT(1) DEFAULT 0 AFTER `entrada`;
```

#### 2. **Fluxo de Integração:**

**OS → Financeiro:**
```
1. OS muda para status "Faturado"
2. Sistema pergunta: "Gerar lançamento financeiro?"
3. Se SIM:
   - Abre modal com opções:
     * Forma de pagamento (dropdown)
     * Parcelas (1x, 2x, 3x...)
     * Entrada (opcional)
     * Data de vencimento (primeira parcela)
   - Cria lançamento(s) automaticamente
   - Marca OS como `lancamento_gerado = 1`
4. Se NÃO:
   - Usuário pode criar manualmente depois
```

**Vendas → Financeiro:**
```
1. Venda finalizada
2. Sistema pergunta: "Gerar lançamento financeiro?"
3. Mesmo fluxo da OS
```

#### 3. **Criação de Lançamentos:**

**Cenário 1: Pagamento à Vista**
- 1 lançamento: valor total, pendente

**Cenário 2: Pagamento Parcelado (sem entrada)**
- N lançamentos: um por parcela, todos pendentes

**Cenário 3: Pagamento com Entrada**
- 1 lançamento de entrada: valor da entrada, PAGO
- N lançamentos de parcelas: valor das parcelas, PENDENTES

**Cenário 4: Pagamento Parcial (Sinal)**
- 1 lançamento: valor total, com pagamento parcial registrado

#### 4. **Prevenção de Duplicação:**
- Verificar se `lancamento_gerado = 1` antes de criar
- Se já existe, perguntar se deseja criar novamente
- Vincular lançamento à OS/Venda (`os.lancamentos_id` ou `vendas.lancamentos_id`)

---

## 📊 IMPACTO DAS MELHORIAS

### ✅ **Benefícios:**
1. **Controle mais preciso** de recebimentos
2. **Automação** reduz trabalho manual
3. **Rastreabilidade** completa (OS/Venda → Financeiro)
4. **Relatórios mais precisos** (considerando pagamentos parciais)
5. **Dashboard atualizado** em tempo real

### ⚠️ **Considerações:**
- Implementar pagamento parcial primeiro (base para integração)
- Testar bem a lógica de parcelas e entrada
- Garantir que não haja duplicação de lançamentos
- Manter compatibilidade com lançamentos manuais existentes

---

## 📅 PRIORIZAÇÃO

### **FASE 5.1: Pagamento Parcial** (ANTES da integração)
- Implementar suporte a pagamentos parciais
- Interface para registrar pagamentos
- Atualizar dashboard e relatórios

### **FASE 5.2: Integração Automática** (DEPOIS)
- Adicionar campos em OS/Vendas
- Implementar fluxo de integração
- Criar lançamentos automaticamente
- Vincular OS/Vendas → Financeiro

---

## 🔗 RELACIONAMENTO COM OUTRAS FASES

- **FASE 3:** Simplificação da Interface ✅ (já implementada)
- **FASE 4:** Categorias e Contas (preparar estrutura)
- **FASE 5:** Integração Automática (inclui pagamento parcial)
- **FASE 6:** Alertas e Notificações (incluir alertas de pagamentos parciais)

---

**Documento criado em:** 17/12/2025  
**Última atualização:** 17/12/2025  
**Status:** 📝 Documentado - Aguardando implementação


