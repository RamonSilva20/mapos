# Servidor web: o que não pode ser público (#2879)

Só `index.php`, `assets/` e `install/` (até o fim da instalação) precisam ser
acessíveis pelo navegador. Todo o resto é lido apenas pelo PHP. Em especial
`application/.env` guarda a senha do banco, a chave de criptografia e a chave
dos tokens da API: se o servidor entregar esse arquivo, quem o baixar controla
a instalação.

O `.htaccess` que acompanha o Map-OS já cobre o **Apache** (com
`AllowOverride All`); o `docker/etc/nginx/default.conf` cobre o **nginx** do
Docker. Em qualquer outro servidor (Caddy, FrankenPHP, nginx próprio, Apache com
`AllowOverride None`) é preciso aplicar as regras abaixo.

## O que bloquear

| Caminho | Motivo |
| --- | --- |
| `application/` (inclusive `.env`, `logs/`, `storage/`, `vendor/`) | código, segredos e arquivos enviados |
| `updates/`, `scripts/`, `tests/`, `docker/`, `docs/` | scripts SQL e arquivos de desenvolvimento |
| `banco.sql`, `composer.json`, `*.lock`, `*.md`, `*.xml`, `*-baseline.json` na raiz | esquema e versões das dependências |
| qualquer arquivo **ou pasta** começando com `.` (exceto `.well-known`) | `.env`, `.htaccess` e as pastas `.git`, `.github`, `.idea` |
| `install/*.lock`, `install/settings.json`, `install/instalador.php`, `install/view/` | internos do instalador |

Depois de configurar, confira (deve dar 403 ou 404, nunca 200):

```
curl -I https://seu-site/application/.env
curl -I https://seu-site/banco.sql
curl -I https://seu-site/application/logs/index.html
curl -I https://seu-site/.git/HEAD
```

A pasta `.git` só existe na instalação por `git clone`; o zip da release não a
tem. Se você clonou, prefira apagá-la depois de instalar (`rm -rf .git`) ou
garanta o 403: o histórico e o endereço do repositório, que pode conter um
token, ficam legíveis se o servidor a entregar.

O melhor complemento é apontar `STORAGE_PATH` para fora da pasta pública
(`docs/armazenamento-de-arquivos.md`): os anexos e o arquivo de relatórios da
CSP ficam lá.

## nginx

```nginx
location ^~ /application/ { deny all; }
location ^~ /updates/     { deny all; }
location ~ ^/(scripts|tests|docker|docs)(/|$) { deny all; }
location ~* ^/[^/]+\.(sql|json|lock|md|xml|neon|bat|sh|dist)$ { deny all; }
location ~ /\.(?!well-known) { deny all; }
location ~* ^/install/(settings\.json|instalador\.php|.*\.lock)$ { deny all; }
location ^~ /install/view/ { deny all; }
```

## Caddy

```caddy
@privado path /application/* /updates/* /scripts/* /tests/* /docker/* /docs/*
respond @privado 403

@arquivos path_regexp ^/[^/]+\.(sql|json|lock|md|xml|neon|bat|sh|dist)$
respond @arquivos 403

@ponto {
    path_regexp (^|/)\.
    not path /.well-known/*
}
respond @ponto 403
```

Cada matcher separado, porque no Caddy os tipos de matcher dentro de um mesmo
bloco se combinam com "e". Declare as regras antes do `php_fastcgi`. Esta
receita não foi testada com um Caddy real; valide com os `curl` acima.

## Apache sem `.htaccess`

Com `AllowOverride None`, ponha no `VirtualHost`:

```apache
<DirectoryMatch "^/caminho/do/mapos/(application|updates|scripts|tests|docker|docs)(/|$)">
    Require all denied
</DirectoryMatch>
<DirectoryMatch "^/caminho/do/mapos/(.*/)?\.(?!well-known)">
    Require all denied
</DirectoryMatch>
<FilesMatch "^(\..*|banco\.sql|composer\.(json|lock)|package(-lock)?\.json|.*baseline\.json)$">
    Require all denied
</FilesMatch>
```

Troque `/caminho/do/mapos` pelo caminho absoluto da pasta do Map-OS (o `DocumentRoot`),
sem barra no fim. As duas primeiras regras começam nesse caminho de propósito: a
regra das pastas ocultas só olha o que está **dentro** do site. Sem essa âncora,
um caminho de instalação com um componente começando por ponto (por exemplo
`/home/usuario/.local/www/mapos`) casaria com tudo e o site inteiro daria 403.
A receita foi conferida num Apache 2.4 real, com a instalação num caminho normal e
num caminho com `.local`: `.git/`, `.github/`, `assets/.qualquer/`, `application/` e
`docs/` dão 403, e `index.php`, `assets/` e `.well-known/` continuam liberados.

## Arquivo de relatórios da CSP

Desde esta versão o `csp-violacoes.json` fica em `<STORAGE_PATH>/logs/` (sem
`STORAGE_PATH`, `application/storage/logs/`) e não mais em `application/logs/`.
O Map-OS move o arquivo antigo sozinho no primeiro relatório recebido.
