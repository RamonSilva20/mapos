<?php

use Exceptions\Http\AuthenticationRequired;
use Exceptions\Http\AuthorizationDenied;
use Exceptions\Http\HttpException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Covers the output escapers in application/helpers/general_helper.php.
 *
 * These are the last line of defence for every value a view emits, and the
 * XSS checker in tools/check_view_escaping.php only proves that views *call*
 * them. It says nothing about whether they behave correctly, so their
 * contracts are asserted here.
 */
class GeneralHelperTest extends TestCase
{
    public function testPrintSafeHtmlAcceptsNull(): void
    {
        $this->assertSame('', printSafeHtml(null));
    }

    public function testPrintSafeHtmlAcceptsEmptyString(): void
    {
        $this->assertSame('', printSafeHtml(''));
    }

    public function testPrintSafeHtmlKeepsSafeMarkup(): void
    {
        $this->assertSame('<b>ok</b>', printSafeHtml('<b>ok</b>'));
    }

    public function testPrintSafeHtmlStripsScriptTags(): void
    {
        $this->assertStringNotContainsString('<script', printSafeHtml('a<script>alert(1)</script>'));
    }

    public function testPrintSafeHtmlStripsEventHandlers(): void
    {
        $this->assertStringNotContainsString('onerror', printSafeHtml('<img src=x onerror=alert(1)>'));
    }

    /**
     * The bug this test exists for: `os.defeito` and friends are `TEXT NULL`,
     * and CI3 writes an explicit NULL when the key is absent from the payload.
     * A `string` parameter type turned that into a TypeError, which is a fatal
     * 500 on the customer-facing OS pages rather than a cosmetic problem.
     */
    public function testPrintSafeHtmlAcceptsTheResultOfANullableColumn(): void
    {
        $row = new stdClass();
        $row->defeito = null;

        $this->assertSame('', printSafeHtml($row->defeito));
    }

    public function testEscEncodesEveryCharacterThatBreaksOutOfMarkup(): void
    {
        $this->assertSame('&lt;&gt;&quot;&#039;', esc('<>"\''));
    }

    #[DataProvider('numericValues')]
    public function testEscStringifiesNumbers(mixed $value, string $expected): void
    {
        $this->assertSame($expected, esc($value));
    }

    public static function numericValues(): array
    {
        return [
            'int' => [123, '123'],
            'float' => [1.5, '1.5'],
            'zero' => [0, '0'],
        ];
    }

    public function testEscScalarClassifiesValuesByWhetherTheyAreText(): void
    {
        $this->assertSame('123', esc_scalar(123));
        $this->assertSame('1.5', esc_scalar(1.5));
        $this->assertSame('0', esc_scalar(0));
        $this->assertSame('', esc_scalar(''));
    }

    /**
     * `null`, `bool`, `array` e `object` não são texto, e é `esc_scalar()` que
     * decide isso — uma vez, para todos os escapers. O `null` de retorno é o
     * que permite a cada meio de saída escolher o seu próprio vazio.
     */
    #[DataProvider('nonTextValues')]
    public function testEscScalarReturnsNullForValuesThatAreNotText(mixed $value): void
    {
        $this->assertNull(esc_scalar($value));
    }

    public static function nonTextValues(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'array' => [['a']],
            'empty array' => [[]],
            'object' => [new stdClass()],
        ];
    }

    /**
     * Comportamento real de `esc()` com valor não-texto: string vazia. Isso
     * **não** é "rejeitar" o valor — o nome do teste anterior dizia isso e a
     * asserção dizia o contrário. Um booleano não tem representação em
     * contexto de texto, e a view que precisar mostrar "Sim"/"Nao" escolhe o
     * texto no ternário; o que `esc()` faz é não inventar um `1` nem um
     * `Array` na página.
     */
    public function testEscRendersValuesThatAreNotTextAsEmptyString(): void
    {
        $this->assertSame('', esc(null));
        $this->assertSame('', esc(true));
        $this->assertSame('', esc(false));
        $this->assertSame('', esc(['a']));
        $this->assertSame('', esc(new stdClass()));
    }

    /**
     * O "não é texto" de `esc_scalar()` chega a meios de saída diferentes e
     * cada um vira o seu próprio vazio: nada em HTML, `""` num alerta JS,
     * `null` num literal JSON. É a razão de `esc_scalar()` existir em vez de
     * cada escaper decidir sozinho — a decisão fica em uma linha por meio.
     */
    public function testEachOutputMediumPicksItsOwnEmptyValue(): void
    {
        $this->assertSame('', esc(null));
        $this->assertSame('""', esc_msg(null));
        $this->assertSame('null', esc_json(null));
    }

    /**
     * Estruturas **não** são o "não é texto" de `esc_scalar()`: em JSON elas
     * são valores legítimos e codificam como tal.
     */
    public function testEscJsonEncodesStructuresRatherThanBlankingThem(): void
    {
        $this->assertSame('{"a":1}', esc_json(['a' => 1]));
        $this->assertSame('["a","b"]', esc_json(['a', 'b']));
        $this->assertSame('{}', esc_json(new stdClass()));
    }

    public function testEscJsonKeepsTypesSoJsCodeDoesNotSilentlyChange(): void
    {
        $this->assertSame('true', esc_json(true));
        $this->assertSame('false', esc_json(false));
        $this->assertSame('null', esc_json(null));
        $this->assertSame('"1.5"', esc_json(1.5));
        $this->assertSame('{"a":1}', esc_json(['a' => 1]));
    }

    public function testEscJsonNeutralisesScriptTerminator(): void
    {
        $this->assertStringNotContainsString('</script>', esc_json('</script>'));
    }

    #[DataProvider('dangerousUrls')]
    public function testEscUrlRejectsExecutableSchemes(string $url): void
    {
        $this->assertSame('', esc_url($url));
    }

    public static function dangerousUrls(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'javascript upper' => ['JaVaScRiPt:alert(1)'],
            'vbscript' => ['vbscript:msgbox(1)'],
            'data' => ['data:text/html;base64,PHNjcmlwdD4='],
            'control char smuggling' => ["java\nscript:alert(1)"],
        ];
    }

    public function testEscUrlAllowsOrdinaryLinks(): void
    {
        $this->assertSame('https://mapos.com.br', esc_url('https://mapos.com.br'));
        $this->assertSame('mailto:contato@mapos.com.br', esc_url('mailto:contato@mapos.com.br'));
    }

    public function testEscUrlEscapesWhatItAllows(): void
    {
        $this->assertSame('https://mapos.com.br/?a=1&amp;b=2', esc_url('https://mapos.com.br/?a=1&b=2'));
    }

    public function testEscUrlStaysOutOfSrcWhereADataImageIsLegitimate(): void
    {
        // A razão de `esc_img_src()` existir. `data:` é perigoso num `href`,
        // onde clicar executa, e é o formato normal de um `src` de imagem — os
        // QR codes de pagamento são data URIs. Uma função só teria de escolher
        // um dos dois, e a escolha errada some com a imagem em silêncio.
        $this->assertSame('', esc_url('data:image/svg+xml;base64,AAA'));
        $this->assertSame('data:image/svg+xml;base64,AAA', esc_img_src('data:image/svg+xml;base64,AAA'));
    }

    #[DataProvider('dangerousImageSources')]
    public function testEscImgSrcRejectsEverythingThatIsNotAnImage(string $src): void
    {
        $this->assertSame('', esc_img_src($src));
    }

    public static function dangerousImageSources(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'javascript upper' => ['JaVaScRiPt:alert(1)'],
            'vbscript' => ['vbscript:msgbox(1)'],
            'file' => ['file:///etc/passwd'],
            // `data:image/` passa, mas `data:text/html` não: não há motivo para
            // aceitar HTML num `src`, e aceitar tornaria a regra inexplicável.
            'data html' => ['data:text/html;base64,PHNjcmlwdD4='],
            'data with image in the path' => ['data:text/html,<img src=x onerror=alert(1)>'],
            'control char smuggling' => ["java\nscript:alert(1)"],
            // A letra de unidade do Windows é lida como esquema por `parse_url()`,
            // então um caminho de disco vira "C", que não é uma URL de imagem e
            // volta como "". É o que impede `imprimirTopo.php` de usar este
            // escaper: lá o valor é um caminho de disco, e não uma URL.
            'windows drive letter' => ['C:\\laragon\\www\\mapos\\assets\\uploads\\logo.png'],
        ];
    }

    public function testEscImgSrcAllowsRelativeAndRemoteImages(): void
    {
        $this->assertSame('assets/img/User.png', esc_img_src('assets/img/User.png'));
        $this->assertSame('/uploads/1.png?v=2', esc_img_src('/uploads/1.png?v=2'));
        $this->assertSame('https://mapos.com.br/logo.png', esc_img_src('https://mapos.com.br/logo.png'));
    }

    public function testEscImgSrcEscapesWhatItAllows(): void
    {
        $this->assertSame(
            'https://mapos.com.br/logo.png?a=1&amp;b=2',
            esc_img_src('https://mapos.com.br/logo.png?a=1&b=2')
        );
    }

    public function testUrlHelpersReturnTheEmptyFormOfTheirOwnMedium(): void
    {
        // `esc_scalar()` devolve null para o que não é texto; cada meio escolhe
        // o seu vazio. É a mesma regra que `testEachOutputMediumPicksItsOwnEmptyValue`
        // cobre para os escapers, aplicada aos dois helpers por baixo deles.
        foreach ([null, [], new stdClass(), true] as $value) {
            $this->assertNull(clean_url($value));
            $this->assertNull(url_scheme('assets/img/User.png'));
            $this->assertSame('', esc_url($value));
            $this->assertSame('', esc_img_src($value));
        }

        $this->assertNull(clean_url('   '));
        $this->assertSame('https', url_scheme('HTTPS://mapos.com.br'));
    }

    public function testCleanUrlRefusesToLetABrowserReinterpretTheScheme(): void
    {
        // Espaços e barras de controle são removidos por alguns navegadores ao
        // resolver a URL, o que contrabandeia o esquema. Recusar antes de decidir
        // qual esquema é o ponto.
        $this->assertNull(clean_url("java\tscript:alert(1)"));
        $this->assertNull(clean_url("java\0script:alert(1)"));
    }

    public function testEscCssDropsTheCharactersThatBreakOutOfADeclaration(): void
    {
        $escaped = esc_css('red;} body{display:none');

        $this->assertStringNotContainsString(';', $escaped);
        $this->assertStringNotContainsString('{', $escaped);
        $this->assertStringNotContainsString('}', $escaped);
        $this->assertStringNotContainsString('<', $escaped);
    }

    public function testEscMsgTurnsBreakIntoNewlineAndDropsMarkup(): void
    {
        $this->assertSame('"linha1\nlinha2"', esc_msg('linha1<br>linha2'));
        $this->assertSame('"so texto"', esc_msg('<b>so texto</b>'));
    }

    public function testRedirectStatusForPreservesTheMethodOnHttp11(): void
    {
        $this->assertSame(307, redirect_status_for('GET', 'HTTP/1.1'));
        $this->assertSame(303, redirect_status_for('POST', 'HTTP/1.1'));
        $this->assertSame(303, redirect_status_for('PUT', 'HTTP/1.1'));
    }

    public function testRedirectStatusForFallsBackTo302OutsideHttp11(): void
    {
        $this->assertSame(302, redirect_status_for('POST', 'HTTP/2'));
        $this->assertSame(302, redirect_status_for('POST', null));
        $this->assertSame(302, redirect_status_for(null, 'HTTP/1.1'));
    }

    /**
     * The status is what separates "did not log in" from "logged in and may not".
     *
     * A family without a status would serve the screen, where every refusal turns
     * into a redirect, and would break the API, which is the only place the two
     * are told apart.
     */
    public function testThePairOfStatusesIsUnauthorizedAndForbidden(): void
    {
        $this->assertSame(401, (new AuthenticationRequired())->status());
        $this->assertSame(403, (new AuthorizationDenied())->status());
    }

    public function testUnauthenticatedExceptionRedirectsToLogin(): void
    {
        $this->assertSame('login', http_exception_redirect_target(new AuthenticationRequired()));
    }

    public function testAuthorizedButDeniedExceptionRedirectsToHome(): void
    {
        $this->assertSame(base_url(), http_exception_redirect_target(new AuthorizationDenied()));
    }

    /**
     * Os dois destinos são diferentes, e não duas escritas do mesmo texto.
     *
     * Se os dois voltassem 'login', este arquivo continuaria verde: as duas
     * asserções acima comparam com o valor que a função deveria devolver, e
     * `base_url()` não é 'login'. A diferença é a propriedade que importa para
     * quem está usando a tela — quem não entrou pode entrar de novo, quem entrou
     * e não pode não resolve nada entrando de novo — e por isso ela é afirmada
     * diretamente, e não deixada como consequência de duas asserções.
     */
    public function testTheTwoDestinationsAreNotTheSameOne(): void
    {
        $this->assertNotSame(
            http_exception_redirect_target(new AuthenticationRequired()),
            http_exception_redirect_target(new AuthorizationDenied())
        );
    }

    /**
     * Uma exceção fora dos dois pares conhecidos cai no login, e o renderizador
     * web ignora o status de qualquer forma.
     *
     * O par 401/403 é o que os guards de hoje produzem. Um `HttpException` com
     * outro status ainda tem que responder alguma coisa no lado web, e este caso
     * fixa qual: o mesmo caminho do 401, porque o destino é decidido pela
     * exceção ser ou não `AuthorizationDenied`, e não pelo número. Fica escrito
     * para que a escolha não vire um default implícito quando alguém criar uma
     * terceira subclasse.
     */
    public function testAnUnknownStatusFallsBackToTheLoginTarget(): void
    {
        $unknown = new class('algo deu errado') extends HttpException {
            public function __construct(string $message)
            {
                parent::__construct($message, 500);
            }
        };

        $this->assertSame(500, $unknown->status());
        $this->assertSame('login', http_exception_redirect_target($unknown));
    }

    /**
     * A família inteira não expõe nenhum destino, e o contrato é medido aqui.
     *
     * O que se afirma é a lista de métodos DECLARADOS em cada classe, e não
     * todos os métodos públicos: os herdados de `Exception` (`getMessage`,
     * `getTrace`, `__wakeup`, ...) não são a família e não precisam ser
     * enumerados um a um. Declarados, os três são exatos: a base acrescenta o
     * construtor que recebe o status e o `status()` que o devolve, e cada
     * subclasse só o construtor que o fixa.
     *
     * É por esta lista que a garantia é de que um guard que recebe a exceção não
     * tem onde ler uma URL — não há método, nem setter, nem propriedade — e o
     * destino não pode acabar escolhido a partir de dado de requisição, que
     * transformaria o 403 em redirecionamento aberto.
     */
    public function testTheFamilyExposesTheStatusAndNothingElse(): void
    {
        $declared = static function (string $class): array {
            $names = array_map(
                static fn (ReflectionMethod $method): string => $method->getName(),
                (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC)
            );

            $own = array_values(array_filter(
                $names,
                static fn (string $name, int $index): bool => (new ReflectionClass($class))->getMethod($name)->getDeclaringClass()->getName() === $class,
                ARRAY_FILTER_USE_BOTH
            ));

            sort($own);

            return $own;
        };

        $this->assertSame(['__construct', 'status'], $declared(HttpException::class));
        $this->assertSame(['__construct'], $declared(AuthenticationRequired::class));
        $this->assertSame(['__construct'], $declared(AuthorizationDenied::class));
    }
}
