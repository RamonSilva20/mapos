<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Script de CI que acusa saída sem escape nas views (scripts/check-escape.php).
 */
final class CheckEscapeTest extends MaposTestCase
{
    #[DataProvider('saidasSemEscape')]
    public function testAcusaSaidaSemEscape(string $view): void
    {
        $this->assertCount(1, escapeOcorrencias($view));
    }

    public static function saidasSemEscape(): array
    {
        return [
            'tag curta com variável' => ['<p><?= $cliente->nome ?></p>'],
            'tag curta com ponto e vírgula' => ['<p><?= $nome; ?></p>'],
            'echo' => ['<?php echo $nome; ?>'],
            'print' => ['<?php print $nome; ?>'],
            'echo concatenado com html' => ['<?php echo \'<td>\' . $r->descricao . \'</td>\'; ?>'],
            'array' => ['<?= $dados[\'nome\'] ?>'],
            'método do $this' => ['<?= $this->session->userdata(\'nome\') ?>'],
            'escape só em parte da concatenação' => ['<?= e($a) . $b ?>'],
            'função que não escapa' => ['<?= strtoupper($nome) ?>'],
            'url sem escape' => ['<a href="<?= site_url(\'clientes/\' . $id) ?>">'],
            'ramo do ternário' => ['<?= isset($x) ? $x : \'\' ?>'],
            'coalesce' => ['<?= $nome ?? \'\' ?>'],
            'escape dentro de função insegura não conta' => ['<?= strtoupper(e($a)) . $b ?>'],
        ];
    }

    #[DataProvider('saidasSeguras')]
    public function testNaoAcusaSaidaSegura(string $view): void
    {
        $this->assertSame([], escapeOcorrencias($view));
    }

    public static function saidasSeguras(): array
    {
        return [
            'e()' => ['<p><?= e($cliente->nome) ?></p>'],
            'html_escape()' => ['<?php echo html_escape($nome); ?>'],
            'htmlspecialchars()' => ['<?= htmlspecialchars($nome, ENT_QUOTES) ?>'],
            'printSafeHtml()' => ['<?= printSafeHtml($os->descricaoProduto) ?>'],
            'printSafeHtml em caixa diferente' => ['<?= PrintSafeHtml($html) ?>'],
            'nome totalmente qualificado' => ['<?= \e($nome) ?>'],
            'escape com função insegura dentro' => ['<?= e(strtoupper($nome)) ?>'],
            'concatenação toda escapada' => ['<?= e($a) . \' - \' . e($b) ?>'],
            'cast para int' => ['<?= (int) $os->idOs ?>'],
            'cast para float' => ['<?= (float) $valor ?>'],
            'intval' => ['<?= intval($id) ?>'],
            'number_format' => ['<?= number_format($valor, 2, \',\', \'.\') ?>'],
            'count' => ['<?= count($itens) ?>'],
            'date' => ['<?= date(\'d/m/Y\', strtotime($os->dataInicial)) ?>'],
            'condição do ternário não é impressa' => ['<?= $ativo ? \'checked\' : \'\' ?>'],
            'ternário com ramos escapados' => ['<?= $x ? e($x) : \'-\' ?>'],
            'literal' => ['<?= \'texto\' ?>'],
            'constante' => ['<?= APP_NAME ?>'],
            'função sem variável' => ['<?= base_url() ?>'],
            'html puro' => ['<p>Olá, $nome</p>'],
            'variável fora do echo' => ['<?php $nome = $_POST[\'nome\']; ?>'],
        ];
    }

    public function testContaCadaComandoUmaVez(): void
    {
        $view = "<td><?= \$a ?></td>\n<td><?php echo \$b . \$c; ?></td>\n<td><?= e(\$d) ?></td>";

        $ocorrencias = escapeOcorrencias($view);

        $this->assertCount(2, $ocorrencias);
        $this->assertSame([1, 2], array_column($ocorrencias, 'linha'));
    }

    public function testTrechoMostraOComando(): void
    {
        $ocorrencias = escapeOcorrencias('<?= $cliente->nome ?>');

        $this->assertSame('<?= $cliente->nome', $ocorrencias[0]['trecho']);
    }

    public function testArquivoAcimaDoBaselineEhAcusado(): void
    {
        $encontrado = [
            'application/views/a.php' => [['linha' => 1, 'trecho' => 'x'], ['linha' => 2, 'trecho' => 'y']],
            'application/views/b.php' => [['linha' => 1, 'trecho' => 'z']],
        ];

        $excedentes = escapeExcedentes($encontrado, [
            'application/views/a.php' => 1,
            'application/views/b.php' => 1,
        ]);

        $this->assertSame(['application/views/a.php'], array_keys($excedentes));
        $this->assertSame(1, $excedentes['application/views/a.php']['permitido']);
    }

    public function testArquivoNovoForaDoBaselineEhAcusado(): void
    {
        $excedentes = escapeExcedentes(
            ['application/views/novo.php' => [['linha' => 3, 'trecho' => 'x']]],
            []
        );

        $this->assertSame(0, $excedentes['application/views/novo.php']['permitido']);
    }

    /**
     * Corrigir ocorrências antigas nunca quebra o build.
     */
    public function testArquivoAbaixoDoBaselinePassa(): void
    {
        $this->assertSame([], escapeExcedentes(
            ['application/views/a.php' => [['linha' => 1, 'trecho' => 'x']]],
            ['application/views/a.php' => 5]
        ));
    }

    /**
     * As views do repositório têm de bater com o baseline commitado. É o mesmo
     * que o job do CI verifica, e falha aqui também para quem roda só o PHPUnit.
     */
    public function testViewsDoProjetoNaoPassamDoBaseline(): void
    {
        $baseline = json_decode((string) file_get_contents(MAPOS_ROOT . '/escape-baseline.json'), true);

        $excedentes = escapeExcedentes(escapeVarrerViews(MAPOS_ROOT), $baseline);

        $this->assertSame([], array_keys($excedentes), 'Há saída sem escape nova nas views. Rode php scripts/check-escape.php.');
    }

    public function testVarreduraUsaCaminhoRelativo(): void
    {
        $raiz = sys_get_temp_dir() . '/mapos-escape-' . uniqid();
        mkdir($raiz . '/application/views/clientes', 0777, true);
        file_put_contents($raiz . '/application/views/clientes/lista.php', '<?= $nome ?>');
        file_put_contents($raiz . '/application/views/clientes/ok.php', '<?= e($nome) ?>');
        file_put_contents($raiz . '/application/views/clientes/leia.txt', '<?= $nome ?>');

        try {
            $encontrado = escapeVarrerViews($raiz);
        } finally {
            array_map('unlink', glob($raiz . '/application/views/clientes/*'));
            rmdir($raiz . '/application/views/clientes');
            rmdir($raiz . '/application/views');
            rmdir($raiz . '/application');
            rmdir($raiz);
        }

        $this->assertSame(['application/views/clientes/lista.php'], array_keys($encontrado));
    }
}
