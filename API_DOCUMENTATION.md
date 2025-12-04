# Documentação da API Map-OS v1

## Índice

1. [Introdução](#introdução)
2. [Autenticação](#autenticação)
3. [Base URL](#base-url)
4. [Endpoints Administrativos](#endpoints-administrativos)
5. [Endpoints de Clientes](#endpoints-de-clientes)
6. [Códigos de Status HTTP](#códigos-de-status-http)
7. [Formato de Resposta](#formato-de-resposta)
8. [Exemplos de Uso](#exemplos-de-uso)

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

**Resposta de Sucesso (201):**
```json
{
  "status": true,
  "message": "Cliente adicionado com sucesso!",
  "result": {
    "idClientes": 1,
    ...
  }
}
```

### Atualizar Cliente

**Endpoint:** `PUT /api/v1/clientes/{id}`

**Autenticação:** Requerida (Permissão: eCliente)

**Body:** (mesmos campos do POST, todos opcionais exceto os obrigatórios)

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
- `search` (opcional): Buscar por código de barras ou descrição
- `perPage` (opcional): Itens por página (padrão: 20)
- `page` (opcional): Número da página (padrão: 0)

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

### Atualizar Usuário

**Endpoint:** `PUT /api/v1/usuarios/{id}`

**Autenticação:** Requerida (Permissão: cUsuario ou próprio usuário)

**Nota:** Usuários podem editar seus próprios dados, mas não podem alterar permissões ou situação.

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

## Suporte

Para mais informações ou suporte, consulte a documentação do sistema ou entre em contato com o administrador.

---

**Última atualização:** 2024-01-15  
**Versão da API:** 1.0  
**Versão do Sistema:** 4.52.0

