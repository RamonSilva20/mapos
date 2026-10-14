# Armazenamento de arquivos (#2872)

Os anexos das OS e os arquivos da tela **Arquivos** ficam fora de `assets/` e só saem pelo Map-OS, com login e permissão. Não existe mais URL pública para eles: um link direto para o arquivo não funciona.

| Arquivo | Quem acessa | Endereço |
| --- | --- | --- |
| Anexo de OS | painel com `vOs` | `os/anexo/<id>` (miniatura: `os/anexo/<id>/thumb`), `os/downloadanexo/<id>` |
| Anexo de OS | cliente dono da OS | `mine/anexo/<id>`, `mine/downloadanexo/<id>` |
| Anexo de OS | API (token) | `GET api/v1/os/<os>/anexos/<id>`, `GET api/v1/client/os/<os>/anexos/<id>` (`?miniatura=1` para a miniatura) |
| Arquivo | painel com `vArquivo` | `arquivos/download/<id>` |

Imagens e PDF abrem na página; os demais formatos baixam. A logo do emitente e a foto de perfil continuam em `assets/uploads` e `assets/userImage`, porque aparecem no login, em e-mails e em PDFs e não têm conteúdo sensível.

## Configuração (`application/.env`)

- `STORAGE_PATH`: pasta dos arquivos, absoluta ou relativa à raiz do projeto. **Aponte para fora da pasta pública do servidor web**, por exemplo `/var/mapos-storage`, e dê ao usuário do PHP permissão de escrita. Sem ela vale `application/storage`, que recebe um `.htaccess` negando o acesso; em outros servidores, bloqueie a pasta (regras em `docs/servidor-web.md`).
- `UPLOAD_MAX_KB`: tamanho máximo de cada upload, em KB (padrão 10240). Os limites `upload_max_filesize` e `post_max_size` do PHP também valem.
- `UPLOAD_MAX_PIXELS`: limite de pixels (largura × altura) de uma imagem enviada (padrão 40000000, 40 megapixels). Um arquivo pequeno pode ter dimensões enormes, e a miniatura (GD) e os PDFs descompactam a imagem inteira na memória.

Formatos aceitos: anexos de OS em JPG, PNG, GIF, PDF, DOCX, CDR e TXT; arquivos em JPG, PNG, GIF, PDF e TXT. Além da extensão, o conteúdo do arquivo (MIME) é conferido; o nome gravado é aleatório e o servidor responde com `X-Content-Type-Options: nosniff`.

## Migração dos arquivos existentes

A migration `move_uploads_to_storage` move os arquivos de `assets/anexos` e `assets/arquivos` para `STORAGE_PATH` e grava no banco o caminho relativo (`anexos/10-2026/OS-5`). Defina `STORAGE_PATH` **antes** de migrar, e dê ao usuário do servidor web permissão de escrita nela.

- Se a pasta não existe e o PHP não consegue criá-la, ou ele não grava nela, a migration **para com uma mensagem** (na tela "Atualizar banco" e no terminal) e **não conclui**: nenhum arquivo é movido e a versão do banco fica na migration anterior. Corrija a permissão e rode a atualização de novo.
- Se só alguns arquivos não puderem ser movidos, os que deu para mover ficam movidos, a migration para com a contagem e a lista completa vai para `application/logs/`. Corrija a causa e rode a atualização de novo: o que já foi movido não se repete.
- Registro cujo arquivo não existe mais no disco (anexo órfão, comum em bancos antigos) não impede a migration: fica registrado no log, o registro permanece como estava e a OS mostra "Arquivo não encontrado" no lugar do anexo.
- Se a migration terminar com êxito mas você mudar o `STORAGE_PATH` depois, os arquivos já movidos continuam na pasta antiga: mova-os junto, mantendo as subpastas.

Faça backup do banco e de `assets/anexos` e `assets/arquivos` antes de atualizar.

### Rollback

Rodar o `down` da migration devolve os arquivos para `assets/anexos` e `assets/arquivos` e grava de volta os caminhos absolutos e as URLs públicas, para voltar a uma versão anterior à #2872:

```
php index.php tools migrate 20261014020000
```

(a versão anterior à migration é a da `add_tema_modo_to_usuarios`).

O `down` devolve os arquivos para `assets/anexos` e `assets/arquivos` **da pasta onde o comando roda** e grava nos registros o caminho absoluto dessa pasta e a URL do `APP_BASEURL`. Se a v5 está em uma pasta nova (como recomenda o guia de migração), os arquivos vão para a pasta da v5, não para a da 4.x. Para voltar à 4.x:

1. Copie `assets/anexos` e `assets/arquivos` da pasta da v5 para a pasta da 4.x (mesmas subpastas).
2. Restaure o dump do banco feito antes da migração. Prefira isso a ajustar `path` e `url` à mão: o `path` gravado pelo `down` é o caminho da pasta da v5.

Ao restaurar o dump, apague o banco e recrie-o antes (`DROP DATABASE` e `CREATE DATABASE`). Restaurar por cima de um banco já migrado deixa tabelas da v5 (`login_attempts`, `os_historico`, `api_*`, `usuarios_codigos_recuperacao`), e a migração seguinte **falha**: o `php index.php tools migrate` para em `create_login_attempts` (tabela que já existe) e sai com código 8, sem concluir a atualização. Apagar e recriar o banco antes de restaurar evita isso.

Depois do rollback, bloqueie o listing público de `assets/anexos` como na v4.55.1 (nomes aleatórios, `index.html` e bloqueio de scripts).
