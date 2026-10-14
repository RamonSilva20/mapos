# Atualização do Map-OS

Configurações → Atualizar → **Verificar atualização** (precisa da permissão de configurar o sistema).

## Como funciona

1. A tela consulta as releases do repositório no GitHub e escolhe a **mais nova com tag** (`vX.Y.Z`). O branch `master` não é mais usado. Por padrão só entram releases estáveis; para receber pré-releases (alpha, beta, rc) coloque `UPDATER_PRERELEASES=true` no `.env`.
2. Ela mostra a versão, a data e o changelog da release. Nada é baixado ainda.
3. Ao confirmar, o servidor baixa `vX.Y.Z-release.zip` e `vX.Y.Z-release.zip.sha256`, publicados pelo workflow `release.yml`, **só** de `https://github.com/<usuário>/<repo>/releases/download/<tag>/`, com o certificado TLS verificado.
4. O SHA256 do zip precisa ser igual ao do `.sha256` (e ao `digest` que a API do GitHub informa para o asset, quando existe). Se não bater, o zip é descartado e nada é alterado.
5. O zip é extraído numa pasta de trabalho em `application/cache/atualizacao`, recusando caminhos com `..`, caminhos absolutos, links simbólicos e zips grandes demais.
6. Cada arquivo que vai ser substituído é copiado antes para `application/cache/atualizacao/backup-<versão>-<data>`. Se qualquer cópia falhar, tudo volta ao que era. Só o backup da última atualização fica guardado.
7. O número da versão em `application/config/config.php` é atualizado.

## O que nunca é alterado

`application/.env`, `assets/anexos`, `assets/arquivos`, `assets/uploads`, `assets/userImage`, `application/storage`, `application/logs`, `application/cache` e a pasta `install/`. Para proteger mais caminhos nesta instalação, use `ignored_files` em `application/config/github_updater.php`.

## Depois de atualizar

- Clique em **Atualizar banco de dados** (as migrations não rodam sozinhas).
- Arquivos que a versão nova removeu continuam no servidor: a atualização só grava os que estão no zip.

## Limites de segurança

O `.sha256` é publicado na mesma release que o zip: ele protege contra download corrompido ou adulterado no caminho, não contra uma release publicada com conteúdo malicioso por quem controla o repositório. Se o servidor não tiver a extensão `zip`/`curl` ou permissão de escrita nas pastas do sistema, a tela avisa e o botão fica desabilitado.
