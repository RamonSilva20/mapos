# Módulo de Notas de Entrada

## Instalação

### 1. Criar as Tabelas no Banco de Dados

Execute o script SQL para criar as tabelas necessárias:

```bash
mysql -u root -p mapos < install_notas_entrada.sql
```

Ou execute manualmente no MySQL:

```sql
USE mapos;
source install_notas_entrada.sql;
```

### 2. Configurar Permissões

O módulo utiliza as seguintes permissões (que precisam ser criadas no sistema de permissões):

- `vNotaEntrada` - Visualizar notas de entrada
- `aNotaEntrada` - Adicionar notas de entrada
- `eNotaEntrada` - Editar/Excluir notas de entrada

**Nota:** Você precisará adicionar essas permissões manualmente no banco de dados na tabela `permissoes` ou através do sistema de permissões do Map-OS.

### 3. Verificar Diretório de Upload

Certifique-se de que o diretório existe e tem permissões de escrita:

```bash
mkdir -p assets/arquivos/xml
chmod -R 775 assets/arquivos/xml
```

## Funcionalidades

### Upload de XML

1. Acesse **Notas de Entrada > Nova Nota**
2. Na aba "Upload de XML", selecione o arquivo XML da NFe
3. O sistema irá:
   - Validar o XML
   - Extrair todos os dados da nota
   - Associar automaticamente com fornecedores (pelo CNPJ)
   - Associar produtos (pelo código de barras)
   - Salvar todos os itens da nota

### Busca na SEFAZ

1. Acesse **Notas de Entrada > Nova Nota**
2. Na aba "Buscar na SEFAZ", digite a chave de acesso (44 caracteres)
3. O sistema irá consultar a SEFAZ (usando API pública)
4. **Nota:** Para processar completamente, será necessário fazer upload do XML

## Estrutura de Dados

### Tabela `notas_entrada`

Armazena os dados principais da nota fiscal:
- Identificação (número, série, chave de acesso)
- Emitente e Destinatário
- Valores totais (produtos, ICMS, IPI, frete, desconto)
- Caminho do arquivo XML
- Status da nota

### Tabela `notas_entrada_itens`

Armazena os itens da nota:
- Código e descrição do produto
- NCM, CEST, CFOP
- Quantidade e valores
- Impostos (ICMS, IPI)
- Associação com produtos do sistema (quando encontrado)

## Integração com Outros Módulos

### Fornecedores

O sistema tenta associar automaticamente o emitente da nota com um fornecedor cadastrado, comparando o CNPJ.

### Produtos

O sistema tenta associar os itens da nota com produtos cadastrados, comparando o código de barras. Se encontrar, atualiza automaticamente os campos fiscais (NCM, CEST, CFOP, etc.).

## Melhorias Futuras

- Integração completa com SEFAZ usando certificado digital
- Atualização automática de estoque ao processar nota
- Geração de relatórios de entrada
- Exportação para Excel/PDF
- Validação de duplicidade mais robusta
- Processamento em lote de múltiplas notas

## Observações Importantes

1. **Certificado Digital:** Para produção, é recomendado usar integração oficial com certificado digital A1 ou A3 para consultas na SEFAZ.

2. **API Pública:** A busca na SEFAZ atualmente usa uma API pública que pode ter limitações. Para uso em produção, considere implementar integração oficial.

3. **Validação:** O sistema valida a estrutura do XML, mas não valida assinatura digital. Para validação completa, use ferramentas oficiais.

4. **Performance:** Para grandes volumes de notas, considere otimizar o processamento de XML e implementar filas de processamento.

