<?php
/**
 * Tela exibida no lugar de qualquer rota do painel enquanto o banco tem
 * migrations pendentes (#3006). É avulsa (sem layout()): precisa abrir com o
 * esquema antigo. Segue o modo de cor do sistema.
 *
 * @var int         $quantidade     Migrations pendentes
 * @var bool        $podeAtualizar  O usuário tem cSistema
 * @var string|null $erro           Flash de erro (falha da atualização)
 * @var string      $acaoAtualizar  URL do POST de mapos/atualizarBanco
 * @var string      $acaoSair       URL do logout
 * @var string      $csrfNome
 * @var string      $csrfValor
 */
$raiz = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'))), '/') . '/';
$titulo = 'Banco de dados desatualizado';
?><!DOCTYPE html>
<html lang="pt-br" data-tema-modo="sistema">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($titulo) ?> — Map-OS</title>
    <link rel="icon" type="image/png" href="<?= e($raiz . 'assets/img/favicon.png') ?>">
    <script src="<?= e($raiz . 'assets/js/tema.js') ?>"></script>
    <link rel="stylesheet" href="<?= e($raiz . 'assets/dist/app.css') ?>">
</head>

<body class="min-h-screen bg-bg text-text">
    <main class="mx-auto flex min-h-screen w-full max-w-xl flex-col items-start justify-center gap-4 px-6 py-12">
        <h1 class="font-display text-heading-xl text-text"><?= e($titulo) ?></h1>

        <?php if (is_string($erro) && trim($erro) !== '') { ?>
            <div class="w-full"><?= component('alert', ['variant' => 'danger', 'message' => $erro]) ?></div>
        <?php } ?>

        <?php if ($podeAtualizar) { ?>
            <p class="text-body-lg text-muted">Há <?= e($quantidade === 1 ? '1 atualização' : $quantidade . ' atualizações') ?> do banco de dados <?= e($quantidade === 1 ? 'pendente' : 'pendentes') ?>. Faça um backup e atualize agora para abrir o painel.</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <form method="post" action="<?= e($acaoAtualizar) ?>">
                    <input type="hidden" name="<?= e($csrfNome) ?>" value="<?= e($csrfValor) ?>">
                    <?= component('button', ['label' => 'Atualizar banco de dados', 'type' => 'submit', 'icon' => 'download']) ?>
                </form>
                <?= component('button', ['label' => 'Sair', 'variant' => 'outline', 'href' => $acaoSair]) ?>
            </div>
        <?php } else { ?>
            <p class="text-body-lg text-muted">O sistema aguarda uma atualização do banco de dados. Avise o administrador.</p>
            <div class="mt-2">
                <?= component('button', ['label' => 'Sair', 'variant' => 'outline', 'href' => $acaoSair]) ?>
            </div>
        <?php } ?>
    </main>
</body>

</html>
