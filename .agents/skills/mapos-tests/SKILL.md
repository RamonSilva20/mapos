---
name: mapos-tests
description: Write or refactor tests for Map-OS, the CodeIgniter 3 PHP app in this repo. Covers the in-process controller harness, the naming rule, data providers for same-shape rejection cases, the docblock budget, and the two things that cannot be tested in-process. Use when adding a controller test, fixing a failing test, or reviewing a diff that touches application/tests/.
metadata:
  project: mapos
  stack: php-codeigniter3-phpunit
---

# Writing Map-OS tests

Map-OS tests are **in-process**: `application/tests/bootstrap.php` boots the app once and the
PHPUnit keeps that state between cases. Each test instantiates the controller directly, because
restarting the CI3 lifecycle per test is not possible. That single fact shapes everything below.

## The four rules

### 1. The name carries the method

`test<Method><BehaviourInEnglish>`:

```php
testIndexRendersTheLoginPage
testVerificarLoginRejectsTheRequest
testSairRedirectsToLogin
testCleanRemovesOnlyLogsOlderThanThirtyDays
```

The method segment keeps the controller's own casing — `clean`, not `Clean`; `verificarLogin`,
not `VerificarLogin`. The behaviour is third-person singular English.

The prefix is not decoration. `Login` has `index`, `verificarLogin` and `sair`, and
`testRendersTheLoginPage` does not say which one it called.

```php
final class LoginControllerTest extends ControllerTestCase
```

`final`, and **no `#[Test]` attribute**. The `test` prefix already selects the method, so the
attribute is a second way of saying the same thing. `#[Depends]`, `#[BeforeClass]`, `#[After]`
and `#[DataProvider]` are fine — they select on something the prefix cannot.

### 2. Same-shape rejection cases are one data provider, not six methods

Every row asserts the whole contract. If a row asserts only one thing, it is a method.

```php
/**
 * @return array<string, array{?string, string, string}>
 */
public static function rejectedRequests(): array
{
    return [
        'sem corpo de POST' => [null, null, 'Preencha o e-mail e a senha.'],
        'e-mail e senha vazios' => ['', '', 'Preencha o e-mail e a senha.'],
        'senha errada' => ['admin@admin.com', 'errada', 'Os dados de acesso estão incorretos.'],
    ];
}

#[DataProvider('rejectedRequests')]
public function testVerificarLoginRejectsTheRequest(
    ?string $email,
    ?string $password,
    string $expectedMessage
): void {
    if ($email !== null) {
        $this->postLogin($email, (string) $password);
    }

    $response = $this->callController('Login', 'verificarLogin');

    $this->assertFalse($response['result']);
    $this->assertSame($expectedMessage, $response['message']);
    $this->assertStringNotContainsString('<', $response['message'], 'a view escreve com .text()');
    $this->assertNotEmpty($response['MAPOS_TOKEN'] ?? null);
    $this->assertNull($this->ci()->session->userdata('logado'));
}
```

**Make the input column nullable, and let `null` mean *no request body at all*.** That is what
keeps "missing fields" and "empty fields" apart, and they are different defects: with no POST body
`Form_validation::set_rules()` returns without doing anything, so `run()` gave `FALSE` with no
error registered and the controller answered with an empty `message`.

Two things a provider row must not absorb:

- **A security property gets its own method**, even when two rows already share the expectation.
  `testVerificarLoginDoesNotRevealWhetherTheEmailExists` stays separate: a property that can only
  go red as "data set 5 of 8" will not be found by whoever broke it.
- **Row count must be justified by input a reader can enumerate**, not by how many cases felt
  worth writing. Same rule the XSS gate follows.

### 3. A docblock explains a decision, not a mechanism

A method docblock earns its lines by naming something someone might reverse silently, and is
otherwise one to three lines or absent — the name already says what the case is. A class docblock
is what it covers, what it deliberately does not, and where the rest lives: four to six lines.

```php
/**
 * A data exata de 30 dias atrás NÃO é removida: o model faz `data <`
 * ('- 30 dias'), estrito. O corte é afirmado por causa do dia, e não por
 * cima, porque trocar o `<` por `<=` não quebraria nenhum outro caso.
 */
```

That earns its lines: it names the boundary and why it is asserted from below. This does not:

```php
/**
 * GET auditoria: a tela renderiza.
 *
 * Este é o primeiro teste que renderiza o layout inteiro (tema/topo,
 * tema/menu, ...) em processo, e é ele que pegaria um "Cannot redeclare"...
 */
```

### 4. A framework fact lives in exactly one place — `AGENTS.md`

Each paragraph about reentrancy, about the connection the guard reads through, about the child's
environment, was once written three times, once per test file that touched it, and the copies
drifted. When a test needs to say "this depends on a CI3 quirk", it says which section to read.

`AGENTS.md` → **Testing** → *Writing a controller test* holds the conventions, and *FrontendBoundaryTest*
plus the "Known limits of the in-process approach" list holds the traps. Read those before writing
anything new; do not re-explain them in a test file.

## Picking the entry point

| need | use |
| --- | --- |
| a rendered view, raw HTML | `callControllerRaw('Login', 'index')` |
| a JSON endpoint | `callController('Login', 'verificarLogin')` — decodes and asserts it is JSON |
| only the guard, which throws in the constructor | `constructController('Auditoria')` |
| a POST with a valid CSRF token | `postLogin($email, $password)` |

`constructController()` is its own door because a guard throws before any method runs. Choosing an
arbitrary method just to have something to call is picking blind in a controller that writes to
the database.

## Database isolation

Opt into `TransactsDatabase` by default — it wraps the case in a transaction and rolls it back,
which is what stops `log_info()` leaking a row per execution.

```php
final class LoginControllerTest extends ControllerTestCase
{
    use TransactsDatabase;
```

**One documented reason to opt out**: a test that exercises a constructor guard.
`AuthorizationGuardControllerTest` writes with autocommit and restores in `tearDown()`, because the
guard reads through a *second* connection that the transaction does not cover
(`AGENTS.md` → "The guard reads through a second connection"). A transaction there would give the
appearance of isolation without giving isolation.

A class that mutates `usuarios` also needs:

```php
protected function resetsBaselineData(): bool
{
    return true;
}
```

## What cannot be tested in-process

Do not spend time trying, and do not let a missing case read as a gap that needs filling:

- **An invalid CSRF token.** `Security::csrf_verify()` calls `show_error(403)`, which ends the
  process. Only the accepting path is coverable.
- **Any controller still reaching `echo` or `exit`.** That is what `respond_redirect()` replaced.
  Refactor the controller to build the payload and return it through `$this->output`.
- **`header()` output.** It does not exist in CLI. Assert on `$this->ci()->output->get_header()`,
  which is where the CORS headers must go.
- **A view that declares a function or class at the top level.** The CI3 `include`s on every render,
  so a second render dies with "Cannot redeclare". Guard with `function_exists()` (as `saudacao()`
  does in `views/mapos/login.php`) or move it to a helper.

Also out of reach, for a reason that is about the app rather than the harness: `Permissoes` and
`Usuarios` cannot be declared as controller classes in the same process as their seeds — CI3 loads
both by file path, so the second is a fatal "Cannot redeclare class". `Auditoria` stands in for
that family.

## Session is readable, but only `$_SESSION`

The `CI_Session` library aborts under CLI, so nothing is persisted to `ci_sessions` and assertions
read the superglobal only. Set up a session with `$this->loginAs($permissionId)` rather than
hand-writing `$_SESSION`.

One trap when testing logout: `sess_destroy()` calls `session_destroy()`, which destroys the
persistent session but does **not** clear the process's `$_SESSION` array. `userdata('logado')` still
returns `true` right after. Reopen the session before asserting, or the case passes for the wrong
reason.

## Before you finish

```
composer test          # rebuilds/refreshes the DB, then runs PHPUnit
composer format:check  # fails on a generated log file under application/logs/
composer xss:check     # only when a view changed
```

Run `composer test:fresh` (`setup-db.php --fresh`) when a failure says the schema is short of
something; the default run reuses a current schema and only clears data.

`format:check` does not honour `.gitignore`, so a log file a test generated makes the check fail on
a file nobody edited. That is why `FrontendBoundaryTest` points the child's `log_path` at the
temporary directory.