<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regressões dos erros 500 da #2901: calendário sem datas, OS inexistente em
 * os/visualizar e autocompletes de Garantias.
 */
final class Erros500Test extends MaposTestCase
{
    public static function datas(): array
    {
        return [
            'data pura' => ['2026-10-01', '2026-10-01'],
            'ISO do FullCalendar' => ['2026-09-28T00:00:00-03:00', '2026-09-28'],
            'ISO em UTC' => ['2026-11-09T00:00:00Z', '2026-11-09'],
            'ausente' => [null, null],
            'vazia' => ['', null],
            'lixo' => ['lixo', null],
            'dia inexistente' => ['2026-02-30', null],
            'formato brasileiro' => ['01/10/2026', null],
            'SQL' => ["2026-10-01' OR 1=1 --", null],
            'array' => [['2026-10-01'], null],
        ];
    }

    #[DataProvider('datas')]
    public function testDataDoCalendario($valor, ?string $esperado): void
    {
        $this->assertSame($esperado, dataIsoParaYmd($valor));
    }

    public function testCalendarioValidaAsDatasAntesDaConsulta(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        preg_match('/function calendario\(\).*?\$allOs = /s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString("dataIsoParaYmd(\$this->input->get('start'))", $trecho[0]);
        $this->assertStringContainsString('set_status_header(400)', $trecho[0]);
    }

    public function testVisualizarOsInexistenteRedireciona(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Os.php');
        preg_match('/function visualizar\(\).*?getProdutos/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertMatchesRegularExpression("/if \\(! \\\$this->data\\['result'\\]\\) \\{\\s+\\\$this->session->set_flashdata\\('error', 'OS não encontrada/", $trecho[0]);
    }

    /**
     * Os autocompletes de Garantias chamavam o vendas_model, que o controller
     * nunca carrega, e sem resultado não devolviam JSON.
     */
    public function testAutocompletesDeGarantiasUsamOProprioModelEDevolvemJson(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Garantias.php');
        $this->assertStringNotContainsString('vendas_model', $controller);
        foreach (['Produto', 'Cliente', 'Usuario'] as $tipo) {
            $this->assertStringContainsString("\$this->garantias_model->autoComplete{$tipo}(\$q)", $controller);
        }

        $model = (string) file_get_contents(APPPATH . 'models/Garantias_model.php');
        $this->assertSame(3, substr_count($model, '$row_set = [];'));
        $this->assertSame(0, substr_count($model, 'if ($query->num_rows() > 0)'));
    }

    /**
     * Colunas de texto opcionais (ex. os.defeito) chegam como null: a view de
     * visualizar cliente dava erro 500 com printSafeHtml(null).
     */
    public function testPrintSafeHtmlAceitaNulo(): void
    {
        $this->assertSame('', printSafeHtml(null));
        $this->assertSame('', printSafeHtml(''));
        $this->assertSame('<b>ok</b>', printSafeHtml('<b>ok</b><script>x</script>'));
    }
}
