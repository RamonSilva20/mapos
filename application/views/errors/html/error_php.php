<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<aside role="alert" class="m-4 rounded-md border border-danger bg-danger-soft p-4 text-danger-ink">
    <h4 class="font-medium">Erro do PHP</h4>
    <p>Severidade: <?= htmlspecialchars((string) $severity, ENT_QUOTES, 'UTF-8') ?></p>
    <p>Mensagem: <?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?></p>
    <p>Arquivo: <?= htmlspecialchars((string) $filepath, ENT_QUOTES, 'UTF-8') ?></p>
    <p>Linha: <?= htmlspecialchars((string) $line, ENT_QUOTES, 'UTF-8') ?></p>

    <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>
        <p class="mt-2 font-medium">Backtrace:</p>
        <?php foreach (debug_backtrace() as $error) : ?>
            <?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>
                <p class="ml-3">
                    Arquivo: <?= htmlspecialchars($error['file'], ENT_QUOTES, 'UTF-8') ?><br>
                    Linha: <?= htmlspecialchars((string) ($error['line'] ?? ''), ENT_QUOTES, 'UTF-8') ?><br>
                    Função: <?= htmlspecialchars((string) $error['function'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>
</aside>
