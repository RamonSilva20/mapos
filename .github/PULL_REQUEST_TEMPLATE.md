<!--
Obrigado por contribuir com o Map-OS!
Preencha as seções abaixo e remova as que não se aplicarem ao seu PR.

Falhas de segurança NÃO devem ser enviadas por Pull Request público.
Reporte em privado para contato@mapos.com.br (veja SECURITY.md).
-->

## Descrição

<!-- O que este PR faz e por quê. Se corrige um bug, descreva o comportamento anterior e o novo. -->

## Issue relacionada

<!-- Ex.: Closes #123 / Relacionado a #123. Deixe em branco se não houver. -->

## Tipo de mudança

<!-- Marque com "x" o que se aplica. -->

- [ ] Correção de bug (`fix`)
- [ ] Nova funcionalidade (`feat`)
- [ ] Documentação (`docs`)
- [ ] Refatoração sem mudança de comportamento (`refactor`)
- [ ] Dependências / manutenção (`chore`)

## Como testar

<!--
Passos para o revisor reproduzir e validar a mudança. Exemplo:
1. Acesse Vendas > Nova venda
2. Aplique um desconto em percentual
3. Confirme que o total exibido bate com o valor salvo
-->

## Capturas de tela

<!-- Obrigatório para mudanças visuais. Sempre que possível, mostre "antes" e "depois". -->

## Interface

<!-- Remova esta seção se o PR não altera telas, componentes ou CSS. -->

- [ ] Segue o [`DESIGN.md`](https://github.com/RamonSilva20/mapos/blob/v5/DESIGN.md): só tokens (sem cores fixas) e componentes da biblioteca.
- [ ] Um único botão primário (laranja) por tela; status em `pill-status`; ações destrutivas com `button-danger`.
- [ ] Campos com os estados de foco, erro e desabilitado; navegação por teclado com foco visível.
- [ ] Capturas em modo claro **e** escuro, no desktop **e** a 375px.

## Checklist

- [ ] O PR resolve **um** assunto (correções e refatorações não estão misturadas).
- [ ] O código foi formatado com `composer format`.
- [ ] Testei manualmente o fluxo afetado.
- [ ] Não há `.env`, credenciais, dumps de banco ou arquivos de IDE no diff.
- [ ] A pasta `application/vendor/` não foi commitada.
- [ ] Entrada do usuário é validada e a saída é escapada nas views.
- [ ] Consultas ao banco usam Query Builder ou *query bindings* (sem concatenação de SQL).

## Banco de dados

<!-- Remova esta seção se o PR não altera o schema. -->

- [ ] A mudança de schema foi feita por **migration** (não editando o `banco.sql`).
- [ ] A migration implementa `up()` e `down()`.
- [ ] Testei em uma instalação nova **e** na atualização de uma base existente.
