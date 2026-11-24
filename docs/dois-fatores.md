# Autenticação em dois fatores (2FA)

Opcional por usuário, com o aplicativo autenticador que a pessoa preferir (Google Authenticator, Microsoft Authenticator, Authy, 1Password...). O algoritmo é o TOTP padrão (RFC 6238: SHA-1, 6 dígitos, 30 segundos), pela biblioteca `robthree/twofactorauth`.

## Para o usuário

Meu perfil → **Autenticação em dois fatores**:

1. Leia o QR code com o aplicativo (ou digite a chave que aparece abaixo dele).
2. Digite o código de 6 dígitos para confirmar. O 2FA só liga depois disso.
3. Guarde os **10 códigos de recuperação** que aparecem uma única vez. Cada um vale uma vez e substitui o aplicativo se o celular se perder.

No login, depois do e-mail e da senha, o sistema pede o código (ou um código de recuperação). A senha conferida espera o código por 5 minutos e 5 tentativas; as falhas contam no mesmo limite do login (5 por e-mail, bloqueio progressivo).

Em Meu perfil também dá para **gerar novos códigos** e **desativar** o 2FA; os dois pedem a senha.

## Para o administrador

- **Exigir por grupo:** Permissões → editar o grupo → "Exigir autenticação em dois fatores". Quem é do grupo e ainda não ativou entra, mas só alcança a tela de ativação até concluir; um grupo que exige não deixa o usuário desativar.
- **Perdeu o celular e os códigos?** Usuários → editar o usuário → **Redefinir o 2FA**. Ele volta a entrar só com a senha (e, se o grupo exigir, ativa de novo). Confirme antes quem está pedindo.

## Como é guardado

- O segredo fica cifrado (AES-256-GCM, chave derivada da `encryption_key` do `.env`) em `usuarios.totp_secreto`. **Não troque a `encryption_key`** com usuários usando 2FA: os segredos deixam de abrir e eles precisam ser redefinidos.
- Um código do aplicativo só vale uma vez (`usuarios.totp_ultimo_passo`), mesmo em requisições simultâneas.
- Os códigos de recuperação ficam só como SHA-256 (`usuarios_codigos_recuperacao`).
- O QR code é desenhado no servidor (`mpdf/qrcode`): o segredo nunca vai para um serviço externo.
- Trocar a senha ou desativar o usuário continua revogando as sessões da API (#2869). Redefinir o 2FA apaga o segredo e os códigos.

## API

Na API v2, quem tem o 2FA ativo recebe um `desafio` no `POST /api/v2/auth/login` e conclui em `POST /api/v2/auth/login/dois-fatores` com o código do aplicativo ou um código de recuperação. Um código errado responde `401` e conta no limite de login. Quem é de um grupo que exige o 2FA e ainda não ativou só alcança as rotas de conta e de sessão (`403` com `codigo: dois_fatores_obrigatorio` nas demais), e pode ativar pela própria API (`POST /api/v2/conta/dois-fatores/configuracao` e `POST /api/v2/conta/dois-fatores`). Detalhes em [api.md](api.md#autenticação-em-dois-fatores).
