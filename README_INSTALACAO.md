# Manual de Instalação e Configuração do MapOS

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Requisitos do Sistema](#requisitos-do-sistema)
3. [Instalação Inicial](#instalação-inicial)
4. [Configuração do Apache](#configuração-do-apache)
5. [Configuração do Banco de Dados](#configuração-do-banco-de-dados)
6. [Configuração de Permissões](#configuração-de-permissões)
7. [Configuração de URL Base](#configuração-de-url-base)
8. [Correção de Problemas](#correção-de-problemas)
9. [Acesso ao Sistema](#acesso-ao-sistema)

---

## 🎯 Visão Geral

Este documento descreve o processo completo de instalação e configuração do **MapOS (Sistema de Controle de Ordens de Serviço)** em um servidor Linux Ubuntu/Debian, configurado para rodar na porta **9980**.

**Versão do MapOS:** 4.52.0  
**Porta de Acesso:** 9980  
**Localização:** `/opt/lampp/htdocs/mapos`

---

## 📦 Requisitos do Sistema

- **Sistema Operacional:** Ubuntu/Debian Linux
- **Servidor Web:** Apache 2.4+
- **PHP:** 8.2 ou superior
- **Banco de Dados:** MySQL/MariaDB
- **Privilégios:** Acesso root ou sudo

### Extensões PHP Necessárias:
- cURL
- MySQLi
- GD
- zip

---

## 🚀 Instalação Inicial

### Passo 1: Executar Script de Instalação

```bash
# 1. Elevar privilégios
sudo su

# 2. Executar script de instalação
curl -o MapOS_Install.sh -L https://raw.githubusercontent.com/RamonSilva20/mapos/master/install.sh && chmod +x MapOS_Install.sh && ./MapOS_Install.sh
```

### Passo 2: Seguir Instruções na Tela

O script irá:
- Baixar e extrair o MapOS
- Instalar dependências do Composer
- Configurar estrutura básica

**Localização após instalação:** `/opt/lampp/htdocs/mapos`

---

## 🌐 Configuração do Apache

### Passo 1: Adicionar Porta 9980

Editar o arquivo `/etc/apache2/ports.conf`:

```bash
sudo nano /etc/apache2/ports.conf
```

Adicionar a linha:
```
Listen 9980
```

### Passo 2: Criar Virtual Host

Criar arquivo `/etc/apache2/sites-available/mapos.conf`:

```bash
sudo nano /etc/apache2/sites-available/mapos.conf
```

Conteúdo do arquivo:

```apache
<VirtualHost *:9980>
    ServerName localhost
    
    DocumentRoot /opt/lampp/htdocs/mapos
    
    <Directory /opt/lampp/htdocs/mapos>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Configuração para CodeIgniter
        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php/$1 [L]
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/mapos_error.log
    CustomLog ${APACHE_LOG_DIR}/mapos_access.log combined
    
    # Configurações PHP
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
</VirtualHost>
```

### Passo 3: Habilitar Site e Módulos

```bash
# Habilitar mod_rewrite (se ainda não estiver)
sudo a2enmod rewrite

# Habilitar o site MapOS
sudo a2ensite mapos.conf

# Verificar configuração
sudo apache2ctl configtest

# Reiniciar Apache
sudo systemctl restart apache2
```

### Passo 4: Verificar Porta

```bash
# Verificar se a porta está ativa
netstat -tlnp | grep 9980
# ou
ss -tlnp | grep 9980
```

---

## 🗄️ Configuração do Banco de Dados

### Opção 1: Usar Usuário Específico (Recomendado)

```bash
# Acessar MySQL
sudo mysql

# Executar comandos SQL
CREATE DATABASE IF NOT EXISTS mapos CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'mapos'@'localhost' IDENTIFIED BY 'mapos123';
GRANT ALL PRIVILEGES ON mapos.* TO 'mapos'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importar estrutura do banco
sudo mysql mapos < /opt/lampp/htdocs/mapos/banco.sql
```

**Credenciais para usar no MapOS:**
- Host: `localhost`
- Usuário: `mapos`
- Senha: `mapos123`
- Banco: `mapos`

### Opção 2: Usar Root (Alternativa)

Se preferir usar root com senha em branco:

```bash
sudo mysql

# Executar comandos SQL
CREATE DATABASE IF NOT EXISTS mapos CHARACTER SET utf8 COLLATE utf8_general_ci;
UPDATE mysql.user SET plugin=\mysql_native_password', authentication_string=\' WHERE User='root' AND Host='localhost';
FLUSH PRIVILEGES;
EXIT;

# Importar estrutura
sudo mysql mapos < /opt/lampp/htdocs/mapos/banco.sql
```

**Credenciais:**
- Host: `localhost`
- Usuário: `root`
- Senha: (deixe em branco)
- Banco: `mapos`

---

## 🔐 Configuração de Permissões

### Passo 1: Ajustar Proprietário

```bash
sudo chown -R www-data:www-data /opt/lampp/htdocs/mapos
```

### Passo 2: Configurar Permissões de Diretórios

```bash
# Permissão geral
sudo chmod -R 755 /opt/lampp/htdocs/mapos

# Diretórios que precisam de escrita (777)
sudo chmod -R 777 /opt/lampp/htdocs/mapos/application/cache
sudo chmod -R 777 /opt/lampp/htdocs/mapos/application/logs
sudo chmod -R 777 /opt/lampp/htdocs/mapos/assets/anexos
sudo chmod -R 777 /opt/lampp/htdocs/mapos/assets/arquivos
sudo chmod -R 777 /opt/lampp/htdocs/mapos/assets/userImage
sudo chmod -R 777 /opt/lampp/htdocs/mapos/assets/relatorios
sudo chmod -R 777 /opt/lampp/htdocs/mapos/assets/json
```

### Passo 3: Configurar Permissões de Arquivos de Configuração

```bash
# Arquivos de configuração (666)
sudo chmod 666 /opt/lampp/htdocs/mapos/application/.env
sudo chmod 666 /opt/lampp/htdocs/mapos/application/config/config.php
sudo chmod 666 /opt/lampp/htdocs/mapos/application/config/database.php
```

### Resumo de Permissões

| Diretório/Arquivo | Permissão | Propósito |
|-------------------|-----------|-----------|
| `/application/cache` | 777 | Cache do sistema |
| `/application/logs` | 777 | Logs do sistema |
| `/assets/anexos` | 777 | Anexos de OS |
| `/assets/arquivos` | 777 | Arquivos gerais |
| `/assets/userImage` | 777 | Imagens de usuários |
| `/assets/relatorios` | 777 | Relatórios gerados |
| `/assets/json` | 777 | Arquivos JSON |
| `/application/.env` | 666 | Variáveis de ambiente |
| `/application/config/config.php` | 666 | Configuração principal |
| `/application/config/database.php` | 666 | Configuração do banco |

---

## 🔧 Configuração de URL Base

### Problema 1: Redirecionamento incorreto
O sistema estava redirecionando sempre para `localhost:9980` mesmo quando acessado via IP ou domínio.

### Problema 2: Erro de Mixed Content (HTTPS/HTTP)
Quando acessado via HTTPS, o sistema gerava URLs HTTP, causando erro de "Mixed Content" que bloqueava requisições AJAX.

### Solução Implementada

O arquivo `/opt/lampp/htdocs/mapos/application/config/config.php` foi modificado para detectar automaticamente a URL base e o protocolo (HTTP/HTTPS) baseado no host que está acessando.

**Detecção de Protocolo:**
- Detecta HTTPS via `$_SERVER['HTTPS']`
- Detecta via proxy reverso (`HTTP_X_FORWARDED_PROTO`)
- Detecta via porta 443
- Remove porta duplicada quando necessário

**Resultado:** O sistema agora funciona corretamente quando acessado via:
- `http://localhost:9980`
- `http://10.10.10.2:9980` (IP da rede)
- `https://mapos.tecnicolitoral.com:9980` (domínio com HTTPS)
- `http://seudominio.com:9980` (domínio com HTTP)

**Nota:** O arquivo `.env` deve ter `APP_BASEURL=""` (vazio) para forçar a detecção automática.

---

## 🐛 Correção de Problemas

### Problema 1: Erro de Mixed Content (HTTPS/HTTP)

**Sintoma:** Erro no console: "Mixed Content: The page at 'https://...' was loaded over HTTPS, but requested an insecure XMLHttpRequest endpoint 'http://...'"

**Causa:** O sistema está sendo acessado via HTTPS, mas está gerando URLs HTTP, causando bloqueio de requisições AJAX por segurança.

**Solução Implementada:**
O arquivo `config.php` foi modificado para detectar corretamente o protocolo HTTPS, incluindo:
- Detecção via `$_SERVER['HTTPS']`
- Detecção via proxy reverso (`HTTP_X_FORWARDED_PROTO`)
- Detecção via porta 443
- Remoção de porta duplicada

**Resultado:** O sistema agora detecta automaticamente HTTPS e gera URLs corretas, resolvendo o erro de Mixed Content.

### Problema 2: Login não funciona

**Sintoma:** Botão de login não responde, nenhum erro aparece.

**Possíveis Causas:**
1. JavaScript não está carregando corretamente
2. Erro no console do navegador (verificar Mixed Content)
3. Problema com CSRF token
4. Erro no servidor (verificar logs)

**Solução:**
1. Abrir o console do navegador (F12) e verificar erros
2. Verificar logs do Apache: `sudo tail -f /var/log/apache2/mapos_error.log`
3. Verificar se o JavaScript está carregando: verificar Network no DevTools
4. Limpar cache do navegador (Ctrl+F5)
5. Verificar se o problema é Mixed Content (ver Problema 1)

### Problema 2: Erro de acesso ao banco de dados

**Sintoma:** "Access denied for user 'root'@'localhost'"

**Causa:** MySQL/MariaDB não permite conexão do root com senha em branco por padrão.

**Solução:** Criar usuário específico ou alterar autenticação do root (ver seção de banco de dados).

### Problema 3: Arquivos não podem ser salvos

**Sintoma:** Erro ao tentar salvar arquivos, fazer upload, gerar relatórios.

**Causa:** Permissões incorretas nos diretórios.

**Solução:** Ajustar permissões conforme seção "Configuração de Permissões".

### Problema 4: Redirecionamento incorreto

**Sintoma:** Sistema sempre redireciona para localhost mesmo acessando via IP.

**Causa:** URL base fixa no arquivo de configuração.

**Solução:** Implementar detecção automática de URL (ver seção "Configuração de URL Base").

### Problema 5: Erro "Usuário não encontrado" no login

**Sintoma:** Ao tentar fazer login, aparece erro "Usuário não encontrado, verifique se suas credenciais estão corretass."

**Possíveis Causas:**
1. Email do usuário não corresponde ao cadastrado no banco
2. Usuário está com `situacao = 0` (inativo)
3. Email foi digitado incorretamente durante a instalação
4. Senha não foi gerada corretamente (hash incorreto)

**Solução:**

1. **Verificar email no banco de dados:**
   ```bash
   mysql -u mapos -pmapos123 mapos -e "SELECT idUsuarios, nome, email, situacao FROM usuarios;"
   ```

2. **Atualizar email e senha se necessário:**
   ```bash
   # Gerar hash da senha
   php -r "echo password_hash('SUA_SENHA', PASSWORD_DEFAULT);"
   
   # Atualizar no banco (substitua HASH_AQUI pelo hash gerado e SEU_EMAIL pelo email correto)
   HASH=$(php -r "echo password_hash('SUA_SENHA', PASSWORD_DEFAULT);")
   mysql -u mapos -pmapos123 mapos -e "UPDATE usuarios SET email = 'SEU_EMAIL@exemplo.com', senha = '$HASH' WHERE idUsuarios = 1;"
   ```

3. **Verificar se o usuário está ativo:**
   ```bash
   mysql -u mapos -pmapos123 mapos -e "UPDATE usuarios SET situacao = 1 WHERE idUsuarios = 1;"
   ```

4. **Verificar se o email está correto (case-sensitive):**
   - O email deve ser exatamente como está no banco (maiúsculas/minúsculas)
   - Verificar se não há espaços extras

---

## 🌍 Acesso ao Sistema

### URL de Acesso

- **Local:** `http://localhost:9980`
- **Rede Local:** `http://[IP_DO_SERVIDOR]:9980` (ex: `http://10.10.10.2:9980`)
- **Domínio:** `http://[SEU_DOMINIO]:9980`

### Primeiro Acesso

1. Acesse a URL do sistema
2. Você será redirecionado para a página de instalação (`/install/index.php`)
3. Preencha os dados:
   - **Banco de Dados:** Use as credenciais configuradas anteriormente
   - **Administrador:** Crie sua conta de administrador
   - **URL Base:** O sistema detectará automaticamente
4. Após a instalação, faça login com as credenciais criadas

### Credenciais Padrão

Após a instalação, use as credenciais do administrador criadas durante o processo de instalação.

---

## 📁 Estrutura de Diretórios

```
/opt/lampp/htdocs/mapos/
├── application/
│   ├── cache/          # Cache (permissão 777)
│   ├── config/         # Configurações
│   ├── logs/           # Logs (permissão 777)
│   └── .env            # Variáveis de ambiente
├── assets/
│   ├── anexos/         # Anexos de OS (permissão 777)
│   ├── arquivos/       # Arquivos gerais (permissão 777)
│   ├── userImage/      # Imagens de usuários (permissão 777)
│   ├── relatorios/     # Relatórios (permissão 777)
│   └── json/           # Arquivos JSON (permissão 777)
├── install/            # Pasta de instalação
├── banco.sql           # Script SQL do banco
└── index.php           # Arquivo principal
```

---

## 📝 Arquivos de Configuração Importantes

| Arquivo | Localização | Descrição |
|---------|-------------|-----------|
| `config.php` | `/application/config/` | Configurações principais do sistema |
| `database.php` | `/application/config/` | Configuração do banco de dados |
| `.env` | `/application/` | Variáveis de ambiente |
| `mapos.conf` | `/etc/apache2/sites-available/` | Virtual host do Apache |
| `ports.conf` | `/etc/apache2/` | Portas do Apache |

---

## 🔍 Verificação e Testes

### Testar Apache

```bash
# Verificar se o virtual host está ativo
sudo apache2ctl -S | grep mapos

# Verificar se a porta está escutando
netstat -tlnp | grep 9980
```

### Testar Banco de Dados

```bash
# Se usou usuário mapos
mysql -u mapos -pmapos123 -e "SHOW TABLES FROM mapos;" | head -10

# Se usou root
mysql -u root -e "SHOW TABLES FROM mapos;" | head -10
```

### Testar Permissões

```bash
# Testar escrita em cache
sudo touch /opt/lampp/htdocs/mapos/application/cache/test.txt
sudo rm /opt/lampp/htdocs/mapos/application/cache/test.txt

# Testar escrita em anexos
sudo touch /opt/lampp/htdocs/mapos/assets/anexos/test.txt
sudo rm /opt/lampp/htdocs/mapos/assets/anexos/test.txt
```

### Verificar Logs

```bash
# Logs do Apache
sudo tail -f /var/log/apache2/mapos_error.log

# Logs do MapOS
tail -f /opt/lampp/htdocs/mapos/application/logs/log-*.php
```

---

## 🆘 Suporte e Troubleshooting

### Comandos Úteis

```bash
# Reiniciar Apache
sudo systemctl restart apache2

# Ver status do Apache
sudo systemctl status apache2

# Verificar configuração do Apache
sudo apache2ctl configtest

# Ver processos do Apache
ps aux | grep apache2

# Ver portas em uso
netstat -tlnp | grep apache2
```

### Verificar Erros Comuns

1. **Porta não está escutando:**
   - Verificar se `Listen 9980` está em `/etc/apache2/ports.conf`
   - Verificar se o virtual host está habilitado
   - Reiniciar Apache

2. **Erro 403 Forbidden:**
   - Verificar permissões dos diretórios
   - Verificar configuração do virtual host

3. **Erro 500 Internal Server Error:**
   - Verificar logs do Apache
   - Verificar permissões de arquivos
   - Verificar sintaxe PHP

4. **Banco de dados não conecta:**
   - Verificar credenciais no `.env`
   - Verificar se o banco existe
   - Verificar se o usuário tem permissões

---

## 📚 Referências

- **Repositório Oficial:** https://github.com/RamonSilva20/mapos
- **Documentação:** Ver arquivos na pasta `/docs/`
- **Suporte:** Issues no GitHub do projeto

---

## ✅ Checklist de Instalação

- [ ] Script de instalação executado
- [ ] Apache configurado na porta 9980
- [ ] Virtual host criado e habilitado
- [ ] Banco de dados criado
- [ ] Estrutura do banco importada
- [ ] Permissões configuradas
- [ ] URL base configurada (detecção automática)
- [ ] Sistema acessível via navegador
- [ ] Instalação concluída via interface web
- [ ] Login funcionando
- [ ] Testes de escrita funcionando

---

## 📅 Histórico de Alterações

### Versão 1.0 - Instalação Inicial
- Instalação do MapOS via script oficial
- Configuração do Apache na porta 9980
- Criação do virtual host
- Configuração do banco de dados
- Ajuste de permissões
- Implementação de detecção automática de URL base
- Correção de detecção de protocolo HTTPS/HTTP
- Resolução de erro de Mixed Content
- Documentação de atualização de credenciais de usuário

---

## 📌 Notas Importantes para Novas Instalações

### Configuração Automática de URL Base

O sistema foi configurado para detectar automaticamente a URL base e o protocolo (HTTP/HTTPS). Para que isso funcione corretamente:

1. **Arquivo `.env`:** Deve ter `APP_BASEURL=""` (vazio) para forçar detecção automática
2. **Arquivo `config.php`:** Já contém a lógica de detecção automática implementada

### Atualização de Credenciais

Se após a instalação o login não funcionar, use os comandos na seção "Problema 5" para atualizar email e senha do usuário administrador.

### Suporte a HTTPS

O sistema detecta automaticamente quando está sendo acessado via HTTPS e gera URLs corretas. Isso resolve problemas de Mixed Content quando há proxy reverso (Nginx, Cloudflare, etc.).

---

**Última atualização:** Dezembro 2025  
**Sistema:** MapOS 4.52.0  
**Porta:** 9980  
**Versão do Manual:** 1.0
