# PR #2984 (#2858): teste independente

Commit testado: 429c845e (branch fix/2858-instalador-tools-migrations, sobre a v5). PHP 8.5.10, MySQL 8.4, `php -S` isolado, bancos descartaveis, Playwright + Chromium. Sem Apache (o PR nao toca no .htaccess).

| # | Item | Resultado |
| - | ---- | --------- |
| 1 | `composer install --no-dev`; S1 reproduzido na v5 antiga (exit 1 sem mensagem) e corrigido (exit 0) | OK (20, 21) |
| 1 | Sem vendor: instalador com "Proximo" desabilitado e linha "Dependencias: Ausentes"; do_install.php recusa; web 503 curto sem caminhos; CLI stderr + exit 1 | OK (01) |
| 1 | 12 extensoes conferidas no instalador | OK (02) |
| 1 | STORAGE_PATH: sugestao ao lado do sistema; recusa assets/, install/, raiz; recusa pasta sem escrita; valido cria e grava | OK (03); obs. abaixo |
| 1 | .env 0600, chaves aleatorias (32 e 44 chars, diferentes a cada instalacao), sem marcadores enter_ | OK (02) |
| 1 | Migrations ao final: 13 aplicadas, versao 20261014060000, sem aviso na tela final | OK (02) |
| 1 | Primeiro login, limite de login (bloqueia na 6a), 2FA (codigo errado recusado, certo aceito), OS com desconto por item (10% de 2 x 199,90 = 359,82), anexo em STORAGE_PATH, impressao A4 em PDF, 2 relatorios mPDF, claro/escuro/celular | OK (04-11) |
| 2 | proc_open desabilitado: aviso correto; "Atualizar banco" pelo painel aplica as 13 | OK (12) |
| 2 | Sem PHP CLI (namespace sem /usr/bin/php, binario copiado, PATH vazio): mesmo aviso; "Atualizar banco" resolve | OK (13) |
| 2 | Migration falhando no instalador: aviso "atualizacoes falharam" com a saida | OK (14) |
| 3 | `tools migrate`, rollback 20240503170400 e migrate de novo (sem Faker) | OK, exit 0 (20) |
| 3 | `tools seed` sem Faker e com Faker | OK, exit 0 (21); os seeds nao usam Faker, nao ha mensagem amigavel porque nao ha erro |
| 3 | Erro fatal forcado (excecao e memory_limit): aparece, exit 1 | OK (22) |
| 4 | Aviso de migrations pendentes so para cSistema; some depois de migrar | OK (30, 31) |
| 4 | Aviso de cookies inseguros: .env antigo em http (2 variaveis) e em https (3), nao aparece em instalacao nova nem para quem nao e admin | OK (32) |
| 5 | Upgrade de banco 4.x (banco.sql da 4.55.2 + dados + anexos/documentos): migrate no-dev, contagens iguais, arquivos movidos para STORAGE_PATH, anexos abrem, link publico antigo 404, login com hash antigo | OK (40-42) |
| 6 | Revisao do diff e testes: InstalacaoCompletaTest 18/18, InstaladorTest 22/22, suite completa verde (exceto artefatos do meu ambiente), check-escape e check-inline-script OK | OK |

Observacoes (nao bloqueiam):
- A recusa de pasta dentro de assets/ ou install/ acontece depois do `mkdir`, deixando uma pasta vazia (`assets/storage-x`, `install/anexos`) (03).
- STORAGE_PATH com barra invertida e recusado (caminho do Windows precisa de barras normais).
- O aviso de migration falhando mostra o caminho do arquivo da migration (so o administrador que instala o ve).
- "Tools seed" nao avisa nada sobre Faker; nao precisa.

Rebase: a #2983 entrou na v5 antes do merge. Rebase limpo (sem conflitos; Tools::migrate e Mapos::atualizarBanco combinam: try/catch da #2983 + Faker/avisos do PR). Depois do rebase (c96d1719): suite completa 1468 testes OK; instalacao limpa, CLI (rollback/migrate/excecao forcada) e upgrade 4.x com anexos e documentos movidos re-testados e OK (50-apos-rebase-v5-com-2983.txt).
