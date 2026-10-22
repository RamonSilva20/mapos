# Ilustrações-adesivo

Adesivos do `DESIGN.md` ("Sticker Illustration Layer"), feitos para o Map-OS
e distribuídos sob a mesma licença do projeto (Apache-2.0, ver `LICENSE`).

| Arquivo        | Uso                                   |
|----------------|---------------------------------------|
| `chave.svg`    | Login do painel                        |
| `notebook.svg` | Login do painel                        |
| `celular.svg`  | Login da área do cliente               |
| `caixa.svg`    | Login da área do cliente               |
| `tecnico.svg`  | Reservado (boas-vindas da área do cliente, destaques escuros) |

## Regras
- Só em superfícies de entrada e destaques escuros: nunca dentro de cards,
  como botão ou em telas operacionais.
- Traço ink `#1f1633`, contorno branco de recorte e preenchimento
  `accent-pink`, `accent-lime` e `accent-violet-mid`. **Nunca laranja**, que
  fica reservado às ações.
- Decorativos: `<img src="..." alt="" aria-hidden="true">`, ocultos em
  celulares pequenos (`max-sm:hidden`).
- Cada arquivo final tem menos de 4 KB.

## Como editar
Os arquivos daqui são a fonte legível. A arte fica em `<defs>` e é desenhada
duas vezes com `<use>`: primeiro com traço branco grosso (o recorte do
adesivo), depois com o traço ink. Depois de editar, rode
`npm run build:stickers` (svgo) e commite `assets/img/stickers/`.
