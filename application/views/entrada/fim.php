<?php
/**
 * Fechamento das telas de entrada (ver entrada/inicio.php): o divisor lima
 * ondulado acima do rodapé (DESIGN.md "Lime Squiggly Footer Divider") e o
 * crédito do projeto.
 */
?>
    <footer class="mx-auto mt-auto w-full max-w-6xl px-4 pt-10 pb-6 lg:px-8">
        <svg class="h-3 w-full text-accent-lime" viewBox="0 0 240 12" preserveAspectRatio="none" fill="none" aria-hidden="true" focusable="false">
            <path d="M0 6 Q 7.5 0 15 6 T 30 6 T 45 6 T 60 6 T 75 6 T 90 6 T 105 6 T 120 6 T 135 6 T 150 6 T 165 6 T 180 6 T 195 6 T 210 6 T 225 6 T 240 6" stroke="currentColor" stroke-width="3" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
        </svg>
        <p class="mt-4 text-center text-caption text-on-dark-muted">
            <a href="https://github.com/RamonSilva20/mapos" class="rounded-xs underline-offset-4 hover:text-on-dark hover:underline focus-visible:outline-3 focus-visible:outline-ring/50"><?= e(date('Y') . ' © Ramon Silva · Map-OS') ?></a>
        </p>
    </footer>
</body>
</html>
