# Documentação da API Map-OS v1

## Índice

1. [Introdução](#introdução)
2. [Autenticação](#autenticação)
3. [Base URL](#base-url)
4. [Status da Aplicação (Health Check)](#status-da-aplicação-health-check)
5. [Endpoints Administrativos](#endpoints-administrativos)
6. [Endpoints de Clientes](#endpoints-de-clientes)
6.1. [Propostas Comerciais](#propostas-comerciais)
7. [Códigos de Status HTTP](#códigos-de-status-http)
8. [Formato de Resposta](#formato-de-resposta)
9. [Exemplos de Uso](#exemplos-de-uso)
10. [Troubleshooting – Aplicação offline](#troubleshooting--aplica%C3%A7%C3%A3o-offline)

---

## Introdução

A API do Map-OS é uma API RESTful que permite integração com o sistema de gestão de ordens de serviço. A API utiliza autenticação baseada em JWT (JSON Web Tokens) e retorna dados no formato JSON.

**Versão da API:** v1  
**Formato de Dados:** JSON  
**Autenticação:** JWT Token (Bearer Token)

---

## Autenticação

A API utiliza autenticação via JWT (JSON Web Token). Para acessar os endpoints protegidos, é necessário incluir o token no header da requisição.

### Header de Autenticação

```
Authorization: Bearer {seu_token_jwt}
```

ou

```
x-api-key: {seu_token_jwt}
```

### Obter Token de Acesso

#### Login de Usuário Administrativo

**Endpoint:** `POST /api/v1/login`

**Body:**
```json
{
  "email": "usuario@exemplo.com",
  "password": "senha123"
}
```

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Login realizado com sucesso!",
  "result": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "permissions": [...]
  }
}
```

#### Login de Cliente

**Endpoint:** `POST /api/v1/client/auth`

**Body:**
```json
{
  "email": "cliente@exemplo.com",
  "password": "senha123"
}
```

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Login bem-sucedido",
  "result": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

### Regenerar Token

**Endpoint:** `GET /api/v1/reGenToken`

**Headers:**
```
Authorization: Bearer {token_atual}
```

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Login realizado com sucesso!",
  "result": {
    "access_token": "novo_token...",
    "permissions": [...]
  }
}
```

---

## Base URL

A base URL da API depende da configuração do servidor. Exemplos:

- Desenvolvimento: `http://localhost/mapos/api/v1`
- Produção: `https://seudominio.com/api/v1`

---

## Status da Aplicação (Health Check)

**Endpoints:** `GET /api/v1/status` ou `GET /api/v1/Mapos/status`

**Autenticação:** Não requerida.

Útil para verificar se a API está online e se o banco de dados responde (monitores, uptime, integrações).

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Map-OS online",
  "online": true,
  "result": {
    "application": "Map-OS",
    "database": "ok"
  }
}
```

Se o MySQL estiver indisponível, `result.database` será `"error"`.

---

## Endpoints Administrativos

### Dashboard

**Endpoint:** `GET /api/v1`

**Autenticação:** Requerida (Usuário Administrativo)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Dashboard",
  "result": {
    "countOs": 150,
    "clientes": 45,
    "produtos": 320,
    "servicos": 25,
    "garantias": 12,
    "vendas": 89,
    "osAbertas": [...],
    "osAndamento": [...],
    "estoqueBaixo": [...]
  }
}
```

### Calendário de OS

**Endpoint:** `GET /api/v1/calendario`

**Autenticação:** Requerida (Usuário Administrativo)

**Parâmetros Query:**
- `status` (opcional): Filtrar por status da OS
- `start` (opcional): Data inicial (formato: YYYY-MM-DD)
- `end` (opcional): Data final (formato: YYYY-MM-DD)

**Exemplo:**
```
GET /api/v1/calendario?status=Aberto&start=2024-01-01&end=2024-01-31
```

### Dados do Emitente

**Endpoint:** `GET /api/v1/emitente`

**Autenticação:** Requerida

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Dados do Map-OS",
  "result": {
    "appName": "Map-OS",
    "emitente": {
      "nome": "Empresa Exemplo",
      "cnpj": "12.345.678/0001-90",
      "email": "contato@exemplo.com",
      ...
    }
  }
}
```

### Auditoria

**Endpoint:** `GET /api/v1/audit`

**Autenticação:** Requerida (Permissão: cAuditoria)

**Parâmetros Query:**
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

---

## Clientes

### Listar Clientes

**Endpoint:** `GET /api/v1/clientes`

**Autenticação:** Requerida (Permissão: vCliente)

**Parâmetros Query:**
- `search` (opcional): Buscar por nome, documento, telefone, email ou contato
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

**Exemplo:**
```
GET /api/v1/clientes?search=João&perPage=10&page=0
```

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Lista de Clientes",
  "result": [
    {
      "idClientes": 1,
      "nomeCliente": "João Silva",
      "documento": "123.456.789-00",
      "email": "joao@exemplo.com",
      "telefone": "(11) 1234-5678",
      ...
    }
  ]
}
```

### Obter Cliente por ID

**Endpoint:** `GET /api/v1/clientes/{id}`

**Autenticação:** Requerida (Permissão: vCliente)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Detalhes do Cliente",
  "result": {
    "idClientes": 1,
    "nomeCliente": "João Silva",
    "ordensServicos": [...],
    ...
  }
}
```

### Criar Cliente

**Endpoint:** `POST /api/v1/clientes`

**Autenticação:** Requerida (Permissão: aCliente)

**Body:**
```json
{
  "nomeCliente": "João Silva",
  "contato": "Maria Silva",
  "documento": "123.456.789-00",
  "telefone": "(11) 1234-5678",
  "celular": "(11) 98765-4321",
  "email": "joao@exemplo.com",
  "senha": "senha123",
  "rua": "Rua Exemplo",
  "numero": "123",
  "complemento": "Apto 45",
  "bairro": "Centro",
  "cidade": "São Paulo",
  "estado": "SP",
  "cep": "01234-567",
  "fornecedor": false
}
```

**Resposta de Sucesso (201 Created):**
```json
{
  "status": true,
  "message": "Cliente adicionado com sucesso!",
  "result": {
    "idClientes": 206,
    "nomeCliente": "Novo Cliente Ltda"
  }
}
```

### Atualizar Cliente

**Endpoint:** `PUT /api/v1/clientes/{id}`

**Autenticação:** Requerida (Permissão: eCliente)

**Body:** (mesmos campos do POST, todos opcionais; envie `senha` apenas se quiser alterá-la)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Cliente editado com sucesso!",
  "result": {
    "idClientes": 206,
    "nomeCliente": "Novo Cliente Ltda"
  }
}
```

### Excluir Cliente

**Endpoint:** `DELETE /api/v1/clientes/{id}`

**Autenticação:** Requerida (Permissão: dCliente)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Cliente excluído com sucesso!"
}
```

---

## Produtos

### Listar Produtos

**Endpoint:** `GET /api/v1/produtos`

**Autenticação:** Requerida (Permissão: vProduto)

**Parâmetros Query:**
- `search` (opcional): Busca por **palavras ou partes** do nome ou do código de barras. Você pode enviar uma ou várias palavras separadas por espaço; cada uma é buscada com `LIKE` em `codDeBarra` ou `descricao`, e todos os termos devem bater (AND). Ex.: `teclado`, `not gamer`, `789` — ideal para consultar preços (`precoVenda`) de produtos.
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

**Resposta:** `result` é um array de produtos. Cada item inclui, entre outros, `idProdutos`, `descricao`, `codDeBarra`, `precoVenda`, `precoCompra`, `estoque`, `unidade`. Use `precoVenda` para preço de venda.

**Exemplos de busca:**
```http
GET /api/v1/produtos?search=teclado
GET /api/v1/produtos?search=not+gamer
GET /api/v1/produtos?search=789
GET /api/v1/produtos?search=monitor+led&perPage=10
```

### Obter Produto por ID

**Endpoint:** `GET /api/v1/produtos/{id}`

**Autenticação:** Requerida (Permissão: vProduto)

### Criar Produto

**Endpoint:** `POST /api/v1/produtos`

**Autenticação:** Requerida (Permissão: aProduto)

**Body:**
```json
{
  "codDeBarra": "7891234567890",
  "descricao": "Produto Exemplo",
  "unidade": "UN",
  "precoCompra": "10.50",
  "precoVenda": "15.00",
  "estoque": 100,
  "estoqueMinimo": 10,
  "saida": 0,
  "entrada": 0
}
```

### Atualizar Produto

**Endpoint:** `PUT /api/v1/produtos/{id}`

**Autenticação:** Requerida (Permissão: eProduto)

**Body:**
```json
{
  "descricao": "Produto Atualizado",
  "unidade": "UN",
  "precoCompra": "11.00",
  "precoVenda": "16.00",
  "estoque": 95,
  "estoqueMinimo": 10
}
```

### Excluir Produto

**Endpoint:** `DELETE /api/v1/produtos/{id}`

**Autenticação:** Requerida (Permissão: dProduto)

---

## Serviços

### Listar Serviços

**Endpoint:** `GET /api/v1/servicos`

**Autenticação:** Requerida (Permissão: vServico)

**Parâmetros Query:**
- `search` (opcional): Buscar por nome ou descrição
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

### Obter Serviço por ID

**Endpoint:** `GET /api/v1/servicos/{id}`

**Autenticação:** Requerida (Permissão: vServico)

### Criar Serviço

**Endpoint:** `POST /api/v1/servicos`

**Autenticação:** Requerida (Permissão: aServico)

**Body:**
```json
{
  "nome": "Serviço de Manutenção",
  "descricao": "Descrição do serviço",
  "preco": "50.00"
}
```

### Atualizar Serviço

**Endpoint:** `PUT /api/v1/servicos/{id}`

**Autenticação:** Requerida (Permissão: eServico)

### Excluir Serviço

**Endpoint:** `DELETE /api/v1/servicos/{id}`

**Autenticação:** Requerida (Permissão: dServico)

---

## Ordens de Serviço (OS)

### Listar OS

**Endpoint:** `GET /api/v1/os`

**Autenticação:** Requerida (Permissão: vOs)

**Parâmetros Query:**
- `search` (opcional): Buscar por termo
- `status` (opcional): Filtrar por status
- `from` (opcional): Data inicial (formato: DD/MM/YYYY)
- `to` (opcional): Data final (formato: DD/MM/YYYY)
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

### Obter OS por ID

**Endpoint:** `GET /api/v1/os/{id}`

**Autenticação:** Requerida (Permissão: vOs)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Detalhes da OS",
  "result": {
    "idOs": 1,
    "dataInicial": "2024-01-15",
    "dataFinal": "2024-01-20",
    "status": "Em Andamento",
    "produtos": [...],
    "servicos": [...],
    "anexos": [...],
    "anotacoes": [...],
    "totalProdutos": 150.00,
    "totalServicos": 80.00,
    "calcTotal": 230.00,
    "textoWhatsApp": "...",
    ...
  }
}
```

### Criar OS

**Endpoint:** `POST /api/v1/os`

**Autenticação:** Requerida (Permissão: aOs)

**Body:**
```json
{
  "dataInicial": "15/01/2024",
  "dataFinal": "20/01/2024",
  "clientes_id": 1,
  "usuarios_id": 1,
  "garantia": 90,
  "garantias_id": null,
  "descricaoProduto": "Notebook Dell",
  "defeito": "Não liga",
  "status": "Aberto",
  "observacoes": "Observações gerais",
  "laudoTecnico": "Laudo técnico inicial"
}
```

### Atualizar OS

**Endpoint:** `PUT /api/v1/os/{id}`

**Autenticação:** Requerida (Permissão: eOs)

**Body:** (mesmos campos do POST)

### Excluir OS

**Endpoint:** `DELETE /api/v1/os/{id}`

**Autenticação:** Requerida (Permissão: dServico)

### Adicionar Desconto na OS

**Endpoint:** `POST /api/v1/os/{id}/desconto`

**Autenticação:** Requerida

**Body:**
```json
{
  "desconto": 10.00,
  "valor_desconto": 23.00,
  "tipoDesconto": "real"
}
```

**Tipos de desconto:**
- `real`: Desconto em valor fixo
- `porcento`: Desconto em percentual

### Produtos da OS

#### Listar Produtos da OS

**Endpoint:** `GET /api/v1/os/{id}/produtos`

**Autenticação:** Requerida

#### Adicionar Produto à OS

**Endpoint:** `POST /api/v1/os/{id}/produtos`

**Autenticação:** Requerida

**Body:**
```json
{
  "idProduto": 1,
  "preco": 15.00,
  "quantidade": 2
}
```

#### Atualizar Produto da OS

**Endpoint:** `PUT /api/v1/os/{id}/produtos/{idProdutos_os}`

**Autenticação:** Requerida

**Body:**
```json
{
  "preco": 16.00,
  "quantidade": 3
}
```

#### Remover Produto da OS

**Endpoint:** `DELETE /api/v1/os/{id}/produtos/{idProdutos_os}`

**Autenticação:** Requerida

### Serviços da OS

#### Listar Serviços da OS

**Endpoint:** `GET /api/v1/os/{id}/servicos`

**Autenticação:** Requerida

#### Adicionar Serviço à OS

**Endpoint:** `POST /api/v1/os/{id}/servicos`

**Autenticação:** Requerida

**Body:**
```json
{
  "idServico": 1,
  "preco": 50.00,
  "quantidade": 1
}
```

#### Atualizar Serviço da OS

**Endpoint:** `PUT /api/v1/os/{id}/servicos/{idServicos_os}`

**Autenticação:** Requerida

#### Remover Serviço da OS

**Endpoint:** `DELETE /api/v1/os/{id}/servicos/{idServicos_os}`

**Autenticação:** Requerida

### Anotações da OS

#### Listar Anotações da OS

**Endpoint:** `GET /api/v1/os/{id}/anotacoes`

**Autenticação:** Requerida

#### Adicionar Anotação à OS

**Endpoint:** `POST /api/v1/os/{id}/anotacoes`

**Autenticação:** Requerida

**Body:**
```json
{
  "anotacao": "Cliente informou que o equipamento está funcionando normalmente"
}
```

#### Remover Anotação da OS

**Endpoint:** `DELETE /api/v1/os/{id}/anotacoes/{idAnotacao}`

**Autenticação:** Requerida

### Anexos da OS

#### Listar Anexos da OS

**Endpoint:** `GET /api/v1/os/{id}/anexos`

**Autenticação:** Requerida

#### Adicionar Anexo à OS

**Endpoint:** `POST /api/v1/os/{id}/anexos`

**Autenticação:** Requerida

**Content-Type:** `multipart/form-data`

**Body:** Arquivo(s) via formulário multipart

**Formatos aceitos:** jpg, png, gif, jpeg, pdf, cdr, docx, txt

#### Remover Anexo da OS

**Endpoint:** `DELETE /api/v1/os/{id}/anexos/{idAnexo}`

**Autenticação:** Requerida

---

## Usuários

### Listar Usuários

**Endpoint:** `GET /api/v1/usuarios`

**Autenticação:** Requerida (Permissão: cUsuario)

**Parâmetros Query:**
- `search` (opcional): Buscar por termo
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

### Obter Usuário por ID

**Endpoint:** `GET /api/v1/usuarios/{id}`

**Autenticação:** Requerida (Permissão: cUsuario)

### Criar Usuário

**Endpoint:** `POST /api/v1/usuarios`

**Autenticação:** Requerida (Permissão: cUsuario)

**Body:**
```json
{
  "nome": "João Silva",
  "rg": "12.345.678-9",
  "cpf": "123.456.789-00",
  "cep": "01234-567",
  "rua": "Rua Exemplo",
  "numero": "123",
  "bairro": "Centro",
  "cidade": "São Paulo",
  "estado": "SP",
  "email": "joao@exemplo.com",
  "senha": "senha123",
  "telefone": "(11) 1234-5678",
  "celular": "(11) 98765-4321",
  "dataExpiracao": "2024-12-31",
  "situacao": 1,
  "permissoes_id": 1
}
```

**Resposta de Sucesso (201 Created):**
```json
{
  "status": true,
  "message": "Usuário adicionado com sucesso!",
  "result": {
    "idUsuarios": 42,
    "nome": "João Silva",
    "email": "joao@exemplo.com"
  }
}
```

### Atualizar Usuário

**Endpoint:** `PUT /api/v1/usuarios/{id}`

**Autenticação:** Requerida (Permissão: cUsuario ou próprio usuário)

**Nota:** Usuários podem editar seus próprios dados, mas não podem alterar permissões ou situação. Envie `senha` no body apenas se quiser alterá-la (será hasheada).

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Usuário editado com sucesso!",
  "result": {
    "idUsuarios": 42,
    "nome": "João Silva",
    "email": "joao@exemplo.com"
  }
}
```

### Excluir Usuário

**Endpoint:** `DELETE /api/v1/usuarios/{id}`

**Autenticação:** Requerida (Permissão: cUsuario)

**Nota:** Não é possível excluir o usuário super admin (ID: 1) ou o próprio usuário logado.

### Dados da Conta

**Endpoint:** `GET /api/v1/conta`

**Autenticação:** Requerida

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Dados do Usuário!",
  "result": {
    "usuario": {
      "idUsuarios": 1,
      "nome": "João Silva",
      "email": "joao@exemplo.com",
      "url_image_user": "http://exemplo.com/assets/userImage/foto.jpg",
      ...
    },
    "permissions": [...]
  }
}
```

---

## Propostas Comerciais

### Listar Propostas

**Endpoint:** `GET /api/v1/propostas`

**Autenticação:** Requerida (Permissão: vPropostas)

**Parâmetros Query:**
- `search` (opcional): Buscar por nome do cliente ou documento
- `status` (opcional): Filtrar por status (ex.: Aberto, Faturado, Orçamento, etc.)
- `from` (opcional): Data inicial (formato: YYYY-MM-DD ou DD/MM/YYYY)
- `to` (opcional): Data final (formato: YYYY-MM-DD ou DD/MM/YYYY)
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Lista de Propostas Comerciais",
  "result": [
    {
      "idProposta": 1,
      "numero_proposta": "1",
      "data_proposta": "2024-01-15",
      "data_validade": "2024-02-15",
      "status": "Aberto",
      "nomeCliente": "Cliente Exemplo",
      "valor_total": "1500.00",
      ...
    }
  ]
}
```

### Obter Proposta por ID

**Endpoint:** `GET /api/v1/propostas/{id}`

**Autenticação:** Requerida (Permissão: vPropostas)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Detalhes da Proposta",
  "result": {
    "idProposta": 1,
    "numero_proposta": "1",
    "data_proposta": "2024-01-15",
    "data_validade": "2024-02-15",
    "status": "Aberto",
    "clientes_id": 1,
    "cliente_nome": null,
    "nomeCliente": "Cliente Exemplo",
    "observacoes": "Observações adicionais sobre a proposta...",
    "desconto": 0,
    "valor_total": "1500.00",
    "tipo_cond_comerc": "T",
    "cond_comerc_texto": "30% à vista, restante em 2x no cartão.",
    "validade_dias": 30,
    "prazo_entrega": "15 dias úteis",
    "produtos": [
      { "descricao": "Produto X", "quantidade": 1, "preco": 100.00, "subtotal": 100.00, "produtos_id": 1 }
    ],
    "servicos": [],
    "parcelas": [],
    "outros": [
      { "descricao": "Instalação e configuração", "preco": 50.00 }
    ],
    "totalProdutos": 100.00,
    "totalServicos": 0.00
  }
}
```

**Campos retornados (resumo):**
- **Produtos:** `produtos` – itens do catálogo ou descrição livre.
- **Outros itens ou serviços:** `outros` – produtos/serviços não cadastrados (ex.: serviços que você descreve em texto).
- **Condições Comerciais:** `tipo_cond_comerc` (`N`=Nenhuma, `P`=Parcelas, `T`=Texto livre), `cond_comerc_texto` (quando tipo `T`), `parcelas` (quando tipo `P`).
- **Validade da Proposta:** `validade_dias` (em dias), `data_validade` (data limite).
- **Observações:** `observacoes`.
- **Prazo de entrega:** `prazo_entrega` (texto livre).

### Download PDF da Proposta

**Endpoint:** `GET /api/v1/propostas/{id}/pdf`

**Autenticação:** Requerida (Permissão: vPropostas)

Gera e retorna o PDF da proposta usando o **mesmo modelo da tela "Imprimir"** na área web (layout idêntico, inclusive QR Code Pix quando configurado).

**Parâmetros Query:**
- `inline` (opcional): `1` ou `true` para abrir no navegador; omitir para forçar download.

**Resposta:** O corpo da resposta é o arquivo PDF (`Content-Type: application/pdf`).  
- Sem `?inline=1`: `Content-Disposition: attachment` → download.  
- Com `?inline=1`: `Content-Disposition: inline` → abre no navegador.

**Exemplo (download):**
```http
GET /api/v1/propostas/123/pdf
Authorization: Bearer {seu_token}
```

**Exemplo (abrir no navegador):**
```http
GET /api/v1/propostas/123/pdf?inline=1
Authorization: Bearer {seu_token}
```

**Exemplo com cURL (salvar arquivo):**
```bash
curl -X GET "http://localhost/mapos/api/v1/propostas/123/pdf" \
  -H "Authorization: Bearer {seu_token}" \
  -o Proposta_123.pdf
```

### Criar Proposta

**Endpoint:** `POST /api/v1/propostas`

**Autenticação:** Requerida (Permissão: aPropostas)

#### Campos disponíveis (mapeados ao formulário)

| Formulário | Campo(s) API | Descrição |
|------------|--------------|-----------|
| **Produtos** | `produtos` | Array de itens. Cada um: `descricao`, `quantidade`, `preco`; opcional `produtos_id` se vinculado ao catálogo. |
| **Outros itens ou serviços** | `outros` ou `descricao_outros` + `preco_outros` | Serviços ou itens não cadastrados. Use `outros`: `[{ "descricao": "...", "preco": 0 }]` ou, no formato do formulário, `descricao_outros` (texto) e `preco_outros` (número). |
| **Condições Comerciais → Tipo** | `tipo_cond_comerc` | `N` = Nenhuma, `P` = Parcelas, `T` = Texto livre. |
| **Condições de Pagamento (Texto Livre)** | `cond_comerc_texto` | Quando `tipo_cond_comerc` = `T`. Texto livre com as condições de pagamento. |
| **Parcelas** | `parcelas` | Quando `tipo_cond_comerc` = `P`. Array de `{ "numero", "dias", "valor", "observacao" }`. |
| **Validade da Proposta** | `validade_dias` e/ou `data_validade` | `validade_dias` = quantidade de dias; `data_validade` = data limite (YYYY-MM-DD ou DD/MM/YYYY). |
| **Prazo de entrega** | `prazo_entrega` | Texto livre (ex.: "15 dias úteis"). |
| **Observações** | `observacoes` | Observações adicionais sobre a proposta. |

#### Exemplo: Produtos + Outros (serviços) + Condições Texto Livre + Validade + Observações

```json
{
  "data_proposta": "2024-01-15",
  "clientes_id": 1,
  "status": "Aberto",
  "produtos": [
    { "descricao": "Notebook Dell", "quantidade": 1, "preco": 3500.00 }
  ],
  "descricao_outros": "Instalação, configuração e suporte remoto por 30 dias.",
  "preco_outros": 450.00,
  "tipo_cond_comerc": "T",
  "cond_comerc_texto": "30% à vista no PIX. Restante em 2x no cartão (parcela em 30 e 60 dias).",
  "validade_dias": 15,
  "observacoes": "Proposta válida para retirada em nossa loja. Entrega sob consulta.",
  "prazo_entrega": "5 dias úteis após aprovação"
}
```

#### Exemplo completo (com serviços, parcelas e todos os campos)

```json
{
  "data_proposta": "2024-01-15",
  "data_validade": "2024-02-15",
  "status": "Aberto",
  "clientes_id": 1,
  "usuarios_id": 1,
  "observacoes": "Observações gerais.",
  "desconto": 0,
  "valor_desconto": 0,
  "tipo_desconto": "real",
  "valor_total": 1500.00,
  "tipo_cond_comerc": "P",
  "validade_dias": 30,
  "prazo_entrega": "15 dias úteis",
  "produtos": [
    { "produtos_id": 1, "descricao": "Produto X", "quantidade": 1, "preco": 100.00 }
  ],
  "servicos": [
    { "servicos_id": 1, "descricao": "Serviço Y", "quantidade": 1, "preco": 50.00 }
  ],
  "outros": [ { "descricao": "Outros itens ou serviços", "preco": 50.00 } ],
  "parcelas": [
    { "numero": 1, "dias": 30, "valor": 500.00, "observacao": "" },
    { "numero": 2, "dias": 60, "valor": 500.00, "observacao": "" }
  ]
}
```

**Campos obrigatórios (apenas na criação):** um de `clientes_id` ou `cliente_nome`.  
**Opcionais:** `data_proposta` (padrão: hoje), `produtos`, `servicos`, `outros`, `parcelas`. Você pode criar uma proposta mínima e ir adicionando itens depois (ver [Fluxo passo a passo](#fluxo-passo-a-passo-whatsapp--agente-ia)).

**Datas:** Aceita `YYYY-MM-DD` ou `DD/MM/YYYY`.

**Cliente:** Use `clientes_id` (ID cadastrado) ou `cliente_nome` (texto livre). Não envie ambos.

**Produtos:** `descricao` obrigatório; `quantidade` (padrão 1) e `preco`; opcional `produtos_id`.

**Outros itens ou serviços:** use `outros` (array) **ou** `descricao_outros` + `preco_outros`. Ideal para serviços ou itens que você descreve em texto.

**Condições Comerciais:** Se `tipo_cond_comerc` = `T`, preencha `cond_comerc_texto`. Se `P`, preencha `parcelas`.

**Parcelas:** `numero`, `dias`, `valor`. `data_vencimento` opcional; se omitida, calculada a partir de `data_proposta` + `dias`.

---

### Fluxo passo a passo (WhatsApp / agente IA)

Para criar propostas **passo a passo** (ex.: via WhatsApp, com um agente de IA solicitando dados aos poucos), use:

1. **Criar proposta mínima** – `POST /api/v1/propostas` apenas com cliente:
   ```json
   { "cliente_nome": "Nome do Cliente" }
   ```
   ou `{ "clientes_id": 1 }`. Opcional: `observacoes`, `data_proposta`. A API retorna a proposta com `idProposta`.

2. **Adicionar produtos** – `POST /api/v1/propostas/{id}/produtos`  
   Body: `{ "itens": [ { "descricao", "quantidade", "preco" } ] }` ou um único `{ "descricao", "quantidade", "preco" }`.  
   Aceita também `description`/`name`, `quantity`, `price`, `produtos_id` (se vinculado ao catálogo).

3. **Adicionar serviços** – `POST /api/v1/propostas/{id}/servicos`  
   Mesmo formato: `itens` ou objeto único com `descricao`, `quantidade`, `preco`.

4. **Adicionar “outros” (mão de obra, itens avulsos)** – `POST /api/v1/propostas/{id}/outros`  
   Body: `{ "itens": [ { "descricao", "preco" } ] }` ou `{ "descricao", "preco" }`.

Os endpoints de acréscimo **somam** itens à proposta (não substituem). O `valor_total` é recalculado automaticamente. Em cada resposta você recebe a proposta atualizada com `produtos`, `servicos`, `outros` e `valor_total`.

#### Exemplo de solicitação (ponto de partida para o agente IA)

> *"Olá, por favor gere uma proposta comercial: material 50 m de cabo condutti CAT5, 3 VBox, 3 conector P4, mão de obra instalação de 3 câmeras wi-fi valor R$ 750,00"*

O agente pode:

1. **Criar a proposta** (antes: obter nome do cliente; se não houver, usar “Cliente a definir” ou perguntar):
   ```http
   POST /api/v1/propostas
   {"cliente_nome": "Cliente a definir"}
   ```
2. **Adicionar produtos** (quantidade + descrição; preço pode vir do catálogo ou ser informado depois):
   ```http
   POST /api/v1/propostas/{id}/produtos
   {"itens": [
     {"descricao": "Cabo condutti CAT5", "quantidade": 50, "preco": 0},
     {"descricao": "VBox", "quantidade": 3, "preco": 0},
     {"descricao": "Conector P4", "quantidade": 3, "preco": 0}
   ]}
   ```
3. **Adicionar “outros”** (mão de obra com valor fixo):
   ```http
   POST /api/v1/propostas/{id}/outros
   {"descricao": "Mão de obra instalação de 3 câmeras wi-fi", "preco": 750}
   ```
4. (Opcional) **Atualizar preços** dos produtos via `PUT /api/v1/propostas/{id}` com `produtos` já preenchidos, ou perguntar ao usuário e refazer os itens se a API permitir edição incremental.

Use `GET /api/v1/propostas/{id}` para conferir o resumo e `GET /api/v1/propostas/{id}/pdf` para gerar o PDF.

#### Endpoints de acréscimo (incremental)

| Método | Endpoint | Body | Descrição |
|--------|----------|------|-----------|
| `POST` | `/api/v1/propostas/{id}/produtos` | `{ "itens": [ { "descricao", "quantidade", "preco", "produtos_id"? } ] }` ou um único objeto | Adiciona produtos à proposta. |
| `POST` | `/api/v1/propostas/{id}/servicos` | `{ "itens": [ { "descricao", "quantidade", "preco", "servicos_id"? } ] }` ou um único objeto | Adiciona serviços à proposta. |
| `POST` | `/api/v1/propostas/{id}/outros` | `{ "itens": [ { "descricao", "preco" } ] }` ou `{ "descricao", "preco" }` | Adiciona itens avulsos (mão de obra, etc.). |

Todos retornam `201 Created` e `result` com a proposta atualizada (`produtos`, `servicos`, `outros`, `valor_total`). Permissão: **ePropostas**.

### Atualizar Proposta

**Endpoints:** `PUT /api/v1/propostas/{id}` ou `PATCH /api/v1/propostas/{id}`

**Autenticação:** Requerida (Permissão: ePropostas)

**Body (JSON):** Mesmos campos do POST (ver [Campos disponíveis](#campos-disponíveis-mapeados-ao-formulário) em Criar Proposta).

- **Campos opcionais** (`observacoes`, `cond_comerc_texto`, `tipo_cond_comerc`, `validade_dias`, `data_validade`, `prazo_entrega`, etc.): se não enviados, mantêm o valor atual.
- **Produtos, serviços, parcelas e outros:** só são alterados se você enviar `produtos`/`produtos_json`, `servicos`/`servicos_json`, `parcelas`/`parcelas_json` ou `outros`/`descricao_outros`+`preco_outros`. Se não enviar, os atuais são preservados. Para limpar, envie array vazio (ex.: `"produtos": []`).

**Atualizar apenas o status:**
- **Opção 1 – Body:** `PUT` ou `PATCH` com `Content-Type: application/json` e body `{"status": "Aprovado"}` (ou outro status: Aberto, Faturado, Negociação, Em Andamento, Orçamento, Finalizado, Cancelado, Aguardando Peças, Aprovado).
- **Opção 2 – Query:** `PUT /api/v1/propostas/{id}?status=Negociação` (útil quando o body não é JSON ou está vazio). Use **URL encode** para acentos: `Negociação` → `Negocia%C3%A7%C3%A3o`.

**Normalização:** Valores como `Negociacao`, `Orcamento`, `Aguardando Pecas` são convertidos automaticamente para a forma canônica (`Negociação`, `Orçamento`, `Aguardando Peças`).

**Exemplo (só status):**
```http
PATCH /api/v1/propostas/123
Content-Type: application/json

{"status": "Aprovado"}
```
```http
PUT /api/v1/propostas/123?status=Aprovado
```

### Atualizar Status da Proposta

**Endpoint:** `PUT /api/v1/propostas/{id}/status`

**Autenticação:** Requerida (Permissão: ePropostas)

Endpoint simplificado para atualizar **apenas o status**. Útil para integrações rápidas ou quando o body JSON não é necessário.

**Parâmetros Query ou Body:**
- `status`: Novo status (Aberto, Aprovado, Faturado, Negociação, Em Andamento, Orçamento, Finalizado, Cancelado, Aguardando Peças).

**Exemplo:**
```http
PUT /api/v1/propostas/123/status?status=Negociacao
```

### Excluir Proposta

**Endpoint:** `DELETE /api/v1/propostas/{id}`

**Autenticação:** Requerida (Permissão: dPropostas)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Proposta excluída com sucesso!"
}
```

**Nota:** Ao excluir, o estoque dos produtos vinculados (quando aplicável) é devolvido automaticamente.

---

## Endpoints de Clientes

### Dashboard do Cliente

**Endpoint:** `GET /api/v1/client`

**Autenticação:** Requerida (Token de Cliente)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Listando resultados",
  "result": {
    "Os": [...],
    "compras": [...]
  }
}
```

### Ordens de Serviço do Cliente

#### Listar OS do Cliente

**Endpoint:** `GET /api/v1/client/os`

**Autenticação:** Requerida (Token de Cliente)

#### Obter OS Específica do Cliente

**Endpoint:** `GET /api/v1/client/os/{id}`

**Autenticação:** Requerida (Token de Cliente)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Listando resultados",
  "result": {
    "pix_key": "chave_pix",
    "os": {...},
    "produtos": [...],
    "servicos": [...],
    "anexos": [...],
    "emitente": {...},
    "qrCode": "...",
    "chaveFormatada": "..."
  }
}
```

#### Criar OS pelo Cliente

**Endpoint:** `POST /api/v1/client/os`

**Autenticação:** Requerida (Token de Cliente)

**Body:**
```json
{
  "descricaoProduto": "Notebook com defeito",
  "defeito": "Não liga",
  "observacoes": "Observações adicionais"
}
```

**Resposta de Sucesso (201):**
```json
{
  "status": true,
  "message": "Ordem de serviço criada com sucesso.",
  "os_id": 123
}
```

### Compras do Cliente

#### Listar Compras

**Endpoint:** `GET /api/v1/client/compras`

**Autenticação:** Requerida (Token de Cliente)

#### Obter Compra Específica

**Endpoint:** `GET /api/v1/client/compras/{id}`

**Autenticação:** Requerida (Token de Cliente)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Listando resultados",
  "result": {
    "Compras": {...},
    "Produtos": [...],
    "QrCode": "...",
    "ChaveFormatada": "..."
  }
}
```

### Cobranças do Cliente

**Endpoint:** `GET /api/v1/client/cobrancas`

**Autenticação:** Requerida (Token de Cliente)

**Resposta de Sucesso (200):**
```json
{
  "status": true,
  "message": "Listando resultados",
  "result": [
    {
      "idCobranca": 1,
      "os_id": 123,
      "status": "pending",
      ...
    }
  ]
}
```

---

## Códigos de Status HTTP

A API utiliza os seguintes códigos de status HTTP:

- **200 OK**: Requisição bem-sucedida
- **201 Created**: Recurso criado com sucesso
- **400 Bad Request**: Erro na requisição (dados inválidos)
- **401 Unauthorized**: Não autenticado ou token inválido
- **403 Forbidden**: Acesso negado (sem permissão)
- **404 Not Found**: Recurso não encontrado
- **500 Internal Server Error**: Erro interno do servidor

---

## Formato de Resposta

Todas as respostas da API seguem um formato padrão:

### Resposta de Sucesso

```json
{
  "status": true,
  "message": "Mensagem de sucesso",
  "result": {
    // Dados retornados
  }
}
```

### Resposta de Erro

```json
{
  "status": false,
  "message": "Mensagem de erro",
  "result": null
}
```

---

## Exemplos de Uso

### Exemplo 1: Autenticação e Listagem de Clientes

```bash
# 1. Fazer login
curl -X POST http://localhost/mapos/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@exemplo.com",
    "password": "senha123"
  }'

# 2. Usar o token retornado para listar clientes
curl -X GET http://localhost/mapos/api/v1/clientes \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json"
```

### Exemplo 2: Criar uma OS

```bash
curl -X POST http://localhost/mapos/api/v1/os \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "dataInicial": "15/01/2024",
    "dataFinal": "20/01/2024",
    "clientes_id": 1,
    "usuarios_id": 1,
    "descricaoProduto": "Notebook Dell",
    "defeito": "Não liga",
    "status": "Aberto"
  }'
```

### Exemplo 3: Adicionar Produto à OS

```bash
curl -X POST http://localhost/mapos/api/v1/os/123/produtos \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "idProduto": 5,
    "preco": 25.50,
    "quantidade": 2
  }'
```

### Exemplo 4: Upload de Anexo

```bash
curl -X POST http://localhost/mapos/api/v1/os/123/anexos \
  -H "Authorization: Bearer {seu_token}" \
  -F "file=@/caminho/para/imagem.jpg"
```

### Exemplo 5: Login de Cliente e Listar OS

```bash
# 1. Login do cliente
curl -X POST http://localhost/mapos/api/v1/client/auth \
  -H "Content-Type: application/json" \
  -d '{
    "email": "cliente@exemplo.com",
    "password": "senha123"
  }'

# 2. Listar OS do cliente
curl -X GET http://localhost/mapos/api/v1/client/os \
  -H "Authorization: Bearer {token_cliente}" \
  -H "Content-Type: application/json"
```

---

## Permissões

A API utiliza um sistema de permissões baseado em níveis de usuário. As principais permissões são:

- **vCliente**: Visualizar clientes
- **aCliente**: Adicionar clientes
- **eCliente**: Editar clientes
- **dCliente**: Deletar clientes
- **vProduto**: Visualizar produtos
- **aProduto**: Adicionar produtos
- **eProduto**: Editar produtos
- **dProduto**: Deletar produtos
- **vServico**: Visualizar serviços
- **aServico**: Adicionar serviços
- **eServico**: Editar serviços
- **dServico**: Deletar serviços
- **vOs**: Visualizar ordens de serviço
- **aOs**: Adicionar ordens de serviço
- **eOs**: Editar ordens de serviço
- **vPropostas**: Visualizar propostas comerciais
- **aPropostas**: Adicionar propostas comerciais
- **ePropostas**: Editar propostas comerciais
- **dPropostas**: Excluir propostas comerciais
- **cUsuario**: Gerenciar usuários
- **cAuditoria**: Visualizar auditoria

---

## Notas Importantes

1. **Datas**: As datas devem ser enviadas no formato `DD/MM/YYYY` para criação/edição de OS, mas são retornadas no formato `YYYY-MM-DD`.

2. **Valores Monetários**: Os valores monetários devem ser enviados como strings no formato decimal (ex: "15.50").

3. **Estoque**: Ao adicionar produtos a uma OS, o estoque é automaticamente debitado se o controle de estoque estiver habilitado.

4. **Status da OS**: Ao cancelar uma OS, os produtos são automaticamente devolvidos ao estoque.

5. **Token Expiration**: Os tokens JWT têm um tempo de expiração configurável. Use o endpoint `/api/v1/reGenToken` para renovar o token.

6. **Paginação**: A maioria dos endpoints de listagem suporta paginação através dos parâmetros `perPage` e `page`.

7. **Busca**: Muitos endpoints suportam busca através do parâmetro `search` na query string.

---

## Troubleshooting – Aplicação offline

Se a aplicação ou a API aparecer **offline** ou indisponível:

1. **LAMPP (Apache + MySQL)**  
   O Map-OS em `/opt/lampp/htdocs/mapos` normalmente usa o LAMPP. Verifique e inicie os serviços:
   ```bash
   /opt/lampp/lampp status
   sudo /opt/lampp/lampp start
   ```
   Apache e MySQL precisam estar em execução.

2. **Health check**  
   Teste o endpoint de status (não requer autenticação):
   ```bash
   curl -s http://localhost/mapos/api/v1/status
   ```
   Ou `GET /api/v1/Mapos/status`. Se retornar `"online": true` e `"database": "ok"`, a API e o banco estão ok.

3. **Banco de dados**  
   Confira o `.env` em `application/` (`DB_HOSTNAME`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`). Se `GET /api/v1/status` retornar `"database": "error"`, o MySQL está inacessível ou as credenciais estão incorretas.

4. **Ambiente**  
   No `.env`, `APP_ENVIRONMENT` deve ser `production` ou `development` (e não `pre_installation`) após a instalação.

---

## Suporte

Para mais informações ou suporte, consulte a documentação do sistema ou entre em contato com o administrador.

---

**Última atualização:** 2024-01-15  
**Versão da API:** 1.0  
**Versão do Sistema:** 4.52.0




