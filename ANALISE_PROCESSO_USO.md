# 📊 ANÁLISE DO SISTEMA MAPOS - PROCESSO DE USO

## 🔍 Visão Geral do Sistema

O MAPOS possui dois módulos principais para gestão de negócios:
1. **Ordens de Serviço (OS)** - Foco em serviços técnicos e assistência
2. **Vendas** - Foco em vendas diretas de produtos

---

## 📋 DIFERENÇAS ENTRE OS E VENDAS

### 🔧 ORDENS DE SERVIÇO (OS)

#### Características:
- **Foco**: Serviços técnicos, reparos, manutenções
- **Estrutura completa**:
  - Defeito apresentado
  - Laudo técnico
  - Descrição do serviço
  - Observações técnicas
  - Controle de garantia (com data de início baseada em status)
  - Status detalhados (Orçamento, Negociação, Aprovado, Em Andamento, etc.)
  
#### Campos específicos:
- Descrição do produto/serviço
- Defeito apresentado
- Laudo técnico
- Observações técnicas
- Data inicial e data final
- Vencimento de garantia
- Termo de garantia

#### Gestão de Estoque em OS:
- **Status que CONSOMEM estoque**:
  - Aprovado
  - Em Andamento
  - Aguardando Peças
  - Finalizado
  - Faturado

- **Status que NÃO consomem estoque**:
  - Orçamento
  - Negociação
  - Aberto
  - Cancelado

#### Impressões disponíveis:
1. **Impressão de OS (A4)** - Documento completo com todos os campos
2. **Proposta Comercial** - Versão simplificada sem informações técnicas da OS
   - Não exibe: N° da OS, status, datas, vencimento de garantia
   - Exibe: Produtos, serviços, valores, dados do cliente
   - Campos opcionais de impressão: Descrição, Defeito, Observações, Laudo

---

### 💰 VENDAS

#### Características:
- **Foco**: Vendas diretas de produtos
- **Estrutura simplificada**:
  - Cliente
  - Produtos/Quantidade
  - Valores
  - Garantia (período de validade do orçamento)
  - Status (menos complexo que OS)

#### Campos específicos:
- Data da venda
- Observações do cliente
- Validade do orçamento
- Desconto

#### Gestão de Estoque em Vendas:
- **Consumo imediato**: Ao adicionar produto na venda, o estoque é consumido
- **Sem controle de status**: Não há lógica de status que controla estoque
- **Mais simples**: Ideal para vendas diretas sem necessidade de orçamentos complexos

#### Impressões disponíveis:
1. **Impressão de Venda (A4)** - Documento completo
2. **Orçamento de Venda** - Proposta comercial
3. **Impressão Térmica** - Para impressoras térmicas

---

## 🎯 CENÁRIOS DE USO RECOMENDADOS

### ✅ Use ORDENS DE SERVIÇO quando:

1. **Você presta SERVIÇOS técnicos**
   - Consertos, reparos, manutenções
   - Precisa documentar defeito e laudo técnico
   - Necessita controle de garantia
   
2. **Processo de ORÇAMENTO → APROVAÇÃO → EXECUÇÃO**
   - Cliente solicita orçamento (Status: Orçamento)
   - Você negocia valores (Status: Negociação)
   - Cliente aprova (Status: Aprovado) → **Aqui o estoque é consumido**
   - Você executa o serviço (Status: Em Andamento)
   - Finaliza o trabalho (Status: Finalizado)

3. **Precisa de PROPOSTA COMERCIAL antes da execução**
   - Crie a OS com status "Orçamento" (não consome estoque)
   - Imprima a "Proposta Comercial" para o cliente
   - Após aprovação, mude para "Aprovado" (consome estoque)
   - Execute o serviço

4. **Necessita rastrear HISTÓRICO completo**
   - Todas as etapas ficam registradas
   - Mudanças de status são controladas
   - Garantia calculada automaticamente

### ✅ Use VENDAS quando:

1. **Você vende PRODUTOS diretos**
   - Vendas no balcão
   - Vendas online
   - Sem necessidade de laudo técnico

2. **Processo SIMPLES e RÁPIDO**
   - Cliente quer comprar
   - Você registra a venda
   - Emite o documento
   - Pronto!

3. **Não precisa de STATUS complexos**
   - Venda realizada ou não
   - Sem etapas intermediárias

---

## 💡 RECOMENDAÇÃO PARA SEU CASO

### Situação Atual:
- Você trabalha com **propostas comerciais** que precisam de **aprovação**
- Após aprovação, você executava a venda (no sistema anterior)
- Não há emissão de nota fiscal neste sistema

### ✨ PROCESSO RECOMENDADO (Usando OS):

#### **FLUXO COMPLETO:**

```
1. ORÇAMENTO
   ├─ Criar OS com status "Orçamento"
   ├─ Adicionar produtos/serviços
   ├─ Produtos NÃO consomem estoque ainda
   ├─ Imprimir "Proposta Comercial"
   └─ Enviar para cliente

2. AGUARDANDO APROVAÇÃO
   ├─ Status: "Negociação" (se houver negociação)
   └─ Estoque ainda NÃO consumido

3. APROVAÇÃO DO CLIENTE
   ├─ Mudar status para "Aprovado"
   ├─ ⚠️ AQUI o estoque É CONSUMIDO automaticamente
   ├─ Garantia INICIA a contagem
   └─ Sistema bloqueia alterações se configurado

4. EXECUÇÃO
   ├─ Status: "Em Andamento" (se aplicável)
   ├─ Executar o serviço/entrega
   └─ Mudar para "Finalizado"

5. FECHAMENTO
   ├─ Status: "Faturado" (se emitir documento)
   └─ OS completa
```

#### **VANTAGENS deste fluxo:**

✅ **Controle de estoque inteligente**
   - Orçamento não consome estoque (cliente pode desistir)
   - Aprovação consome estoque (cliente confirmou)
   - Cancelamento devolve estoque automaticamente

✅ **Rastreabilidade completa**
   - Histórico de quando foi orçado
   - Quando foi aprovado
   - Quando foi executado
   - Garantia calculada automaticamente

✅ **Flexibilidade**
   - Pode fazer N orçamentos sem consumir estoque
   - Pode cancelar sem problemas
   - Pode negociar valores antes de aprovar

✅ **Proposta Comercial profissional**
   - Documento limpo para o cliente
   - Sem informações técnicas desnecessárias
   - Personalizável (escolhe o que imprimir)

---

## 🔄 ALTERNATIVA (Usando Vendas):

Se você preferir trabalhar com **VENDAS** ao invés de OS:

### Fluxo alternativo:

```
1. ORÇAMENTO
   ├─ Criar Venda com status "Aberto" ou "Orçamento"
   ├─ ⚠️ Estoque é consumido IMEDIATAMENTE
   └─ Imprimir "Orçamento de Venda"

2. APROVAÇÃO
   ├─ Cliente aprova
   └─ Mudar status para "Faturado"

3. NÃO APROVAÇÃO
   ├─ Cliente não aprova
   ├─ Precisa EXCLUIR a venda
   └─ Estoque é devolvido
```

### ⚠️ DESVANTAGENS:

❌ Consumo imediato de estoque (mesmo para orçamento)
❌ Precisa excluir a venda se cliente não aprovar
❌ Menos rastreabilidade
❌ Sem controle de garantia por status
❌ Sem campos de defeito/laudo técnico

---

## 🎓 COMPARAÇÃO COM SEU SISTEMA ANTERIOR

### Sistema Anterior:
```
Proposta → Aprovação → Venda → Nota Fiscal
```

### MAPOS com OS (RECOMENDADO):
```
OS (Orçamento) → Aprovação → OS (Aprovado/Em Andamento) → OS (Finalizado/Faturado)
                                      ↓
                              Consome estoque aqui
```

### MAPOS com Vendas (Alternativo):
```
Venda (Orçamento) → Aprovação → Venda (Faturada)
         ↓
 Consome estoque aqui (problema!)
```

---

## 📊 QUADRO COMPARATIVO

| Critério | OS (Orçamento→Aprovado) | Vendas |
|----------|------------------------|--------|
| **Controle de estoque inteligente** | ✅ Sim (baseado em status) | ❌ Não (consumo imediato) |
| **Proposta comercial** | ✅ Excelente (customizável) | ✅ Boa (simples) |
| **Rastreabilidade** | ✅ Completa | ⚠️ Limitada |
| **Garantia automática** | ✅ Sim (por status) | ⚠️ Simples |
| **Campos técnicos** | ✅ Defeito, Laudo, etc. | ❌ Não possui |
| **Facilidade de uso** | ⚠️ Requer entendimento de status | ✅ Muito simples |
| **Cancelamento sem impacto** | ✅ Devolve estoque automático | ⚠️ Precisa excluir |
| **Múltiplos orçamentos** | ✅ Sem problemas | ❌ Consome estoque |

---

## 🎯 DECISÃO FINAL

### Para seu negócio, RECOMENDO usar **ORDENS DE SERVIÇO**:

#### Motivos:
1. **Você precisa de aprovação antes de consumir estoque**
2. **Trabalha com propostas comerciais**
3. **Necessita rastreabilidade do processo**
4. **Pode fazer múltiplos orçamentos sem impacto**
5. **Controle automático de garantia**

#### Como implementar:

1. **Configure seu processo**:
   - Status inicial: "Orçamento"
   - Após aprovação: "Aprovado" ou "Em Andamento"
   - Finalização: "Finalizado" ou "Faturado"

2. **Treine sua equipe**:
   - Explicar que "Orçamento" NÃO consome estoque
   - "Aprovado" em diante CONSOME estoque
   - Importância de mudar o status corretamente

3. **Customize as impressões**:
   - Use "Proposta Comercial" para cliente
   - Use "Impressão de OS" para controle interno
   - Marque os campos que deseja imprimir

---

## 📝 NOTAS IMPORTANTES

### Sobre Nota Fiscal:
- O MAPOS não possui módulo de emissão de NF-e nativo
- Você pode integrar com sistemas externos de emissão
- Ou usar o sistema apenas para gestão interna
- O status "Faturado" serve como controle de que foi emitido documento fiscal externamente

### Sobre Configurações:
- Verifique em **Configurações** se o controle de estoque está ativado
- Configure os status que devem aparecer na lista de OS
- Defina se OS "Faturadas" podem ser editadas

---

## 🚀 PRÓXIMOS PASSOS

1. **Teste o fluxo recomendado**:
   - Crie uma OS de teste com status "Orçamento"
   - Adicione produtos
   - Verifique que o estoque NÃO foi consumido
   - Mude para "Aprovado"
   - Verifique que o estoque FOI consumido

2. **Ajuste seu processo**:
   - Documente internamente
   - Treine a equipe
   - Faça ajustes conforme necessidade

3. **Avalie após 1 mês**:
   - O processo está funcionando?
   - Há necessidade de ajustes?
   - A equipe está confortável?

---

## 📞 SUPORTE

Se precisar de ajustes adicionais no sistema ou no fluxo, estou à disposição!

---

**Documento criado em:** <?php echo date('d/m/Y H:i:s'); ?>
**Versão do sistema:** MAPOS
**Branch:** feature/implementacoes

