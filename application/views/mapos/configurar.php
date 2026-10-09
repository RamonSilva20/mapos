<?php
/**
 * Configurações do sistema (#2846), em abas por link (?aba=).
 *
 * - Cada aba é um formulário que grava só os campos dela (POST com o token
 *   CSRF para mapos/configurar?aba=...). Os campos vêm de
 *   configuracaoCampos(): sim/não em switch, listas em select, textos em
 *   input/textarea e os status visíveis da OS em caixas.
 * - Segredos do .env (senhas, tokens) nunca voltam para a tela: o campo vem
 *   vazio e, em branco, mantém o valor atual.
 * - A aba Atualizações tem as ações de atualizar o banco e o Map-OS, por
 *   POST confirmado em modal-confirm.
 *
 * @var string                $aba
 * @var array<string, array>  $campos
 * @var array<string, mixed>  $valores
 * @var array<string, string> $erros
 * @var bool                  $pode_backup
 */
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$icones = ['geral' => 'settings', 'os' => 'wrench', 'vendas' => 'shopping-cart', 'financeiro' => 'circle-dollar-sign', 'email' => 'mail', 'pagamentos' => 'banknote', 'api' => 'key-round', 'sistema' => 'download'];
$curtos = ['os' => 'OS', 'vendas' => 'Vendas', 'pagamentos' => 'Gateways', 'sistema' => 'Atualizar'];

$abas = [];
foreach (CONFIG_ABAS as $chave => $rotulo) {
    $abas[] = ['label' => $rotulo, 'short' => $curtos[$chave] ?? null, 'url' => site_url('mapos/configurar') . ($chave === 'geral' ? '' : listagemQuery(['aba' => $chave])), 'active' => $aba === $chave, 'icon' => $icones[$chave]];
}

/** Um campo de configuracaoCampos() como componente. */
$campo = static function (string $nome, array $def) use ($valores, $errosDeCampo) {
    $id = 'config-' . strtolower($nome);
    $erro = $errosDeCampo[$nome] ?? null;
    $valor = $valores[$nome] ?? null;

    return match ($def['tipo']) {
        'sim_nao', 'bool_env' => component('switch', ['name' => $nome, 'id' => $id, 'value' => $def['tipo'] === 'bool_env' ? 'true' : '1', 'label' => $def['rotulo'], 'checked' => (bool) $valor, 'help' => $def['ajuda'] ?? null]),
        'opcao' => component('select', ['name' => $nome, 'id' => $id, 'label' => $def['rotulo'], 'options' => $def['opcoes'], 'selected' => (string) $valor !== '' ? (string) $valor : null, 'placeholder' => (string) $valor === '' ? 'Escolha' : null, 'error' => $erro, 'help' => $def['ajuda'] ?? null]),
        'area' => component('textarea', ['name' => $nome, 'id' => $id, 'label' => $def['rotulo'], 'value' => $valor, 'rows' => 5, 'error' => $erro, 'help' => $def['ajuda'] ?? null, 'attrs' => ['maxlength' => $def['max'] ?? 2000]]),
        'segredo' => component('input', ['name' => $nome, 'id' => $id, 'label' => $def['rotulo'], 'type' => 'password', 'autocomplete' => 'new-password', 'error' => $erro,
            'placeholder' => $valor['definido'] ? '••••••••' : null,
            'help' => $valor['definido'] ? 'Definida. Deixe em branco para manter.' : 'Ainda não definida.',
            'attrs' => ['maxlength' => $def['max'] ?? 255]]),
        'gerar_jwt' => component('checkbox', ['name' => $nome, 'id' => $id, 'value' => '1', 'label' => $def['rotulo'], 'help' => $def['ajuda'] ?? null]),
        'numero' => component('input', ['name' => $nome, 'id' => $id, 'label' => $def['rotulo'], 'value' => $valor, 'error' => $erro, 'help' => $def['ajuda'] ?? null, 'attrs' => ['inputmode' => 'numeric', 'pattern' => '\d*', 'maxlength' => $def['max'] ?? 10, 'data-msg-invalido' => 'Use só números.']]),
        default => component('input', ['name' => $nome, 'id' => $id, 'label' => $def['rotulo'], 'value' => $valor, 'required' => $def['obrigatorio'] ?? false, 'error' => $erro, 'help' => $def['ajuda'] ?? null, 'attrs' => ['maxlength' => $def['max'] ?? 255, 'data-msg-vazio' => 'Preencha este campo.']]),
    };
};

// Campos agrupados pelos subtítulos (grupo); o primeiro grupo não tem título.
$grupos = [];
$atual = '';
foreach ($campos as $nome => $def) {
    $atual = $def['grupo'] ?? $atual;
    $grupos[$atual][$nome] = $def;
}
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8" <?= js_module('configuracoes/configurar') ?>>
    <header>
        <h1 class="font-display text-heading-xl text-text">Configurações</h1>
        <p class="text-caption text-muted">Preferências do Map-OS para todos os usuários.</p>
    </header>

    <?= component('tabs', ['label' => 'Seções das configurações', 'items' => $abas]) ?>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <?php if ($aba === 'sistema') { ?>
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-banco">
            <h2 id="secao-banco" class="text-heading-sm text-text">Banco de dados</h2>
            <p class="mt-1 text-caption text-muted">Aplica as migrations que ainda não rodaram (mudanças de tabelas das versões novas). Faça um backup antes.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <?= component('button', ['label' => 'Atualizar banco de dados', 'icon' => 'download', 'variant' => 'outline', 'attrs' => ['data-modal-abrir' => 'atualizar-banco']]) ?>
                <?php if ($pode_backup) { ?>
                    <?= component('button', ['label' => 'Baixar backup', 'icon' => 'archive', 'variant' => 'ghost', 'href' => site_url('mapos/backup')]) ?>
                <?php } ?>
            </div>
        </section>
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-mapos">
            <h2 id="secao-mapos" class="text-heading-sm text-text">Map-OS</h2>
            <p class="mt-1 text-caption text-muted">Baixa a versão mais nova do GitHub e substitui os arquivos. As pastas assets/anexos e assets/arquivos são apagadas: faça backup delas antes.</p>
            <div class="mt-4">
                <?= component('button', ['label' => 'Atualizar o Map-OS', 'icon' => 'download', 'variant' => 'outline', 'attrs' => ['data-modal-abrir' => 'atualizar-mapos']]) ?>
            </div>
        </section>

        <form id="form-atualizar-banco" method="post" action="<?= e(site_url('mapos/atualizarBanco')) ?>" hidden><input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>"></form>
        <form id="form-atualizar-mapos" method="post" action="<?= e(site_url('mapos/atualizarMapos')) ?>" hidden><input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>"></form>
        <?= component('modal-confirm', ['id' => 'atualizar-banco', 'title' => 'Atualizar o banco de dados?', 'message' => 'As migrations pendentes alteram as tabelas. Recomendamos baixar um backup antes.', 'confirm_label' => 'Atualizar', 'icon' => 'download', 'confirm_attrs' => ['form' => 'form-atualizar-banco']]) ?>
        <?= component('modal-confirm', ['id' => 'atualizar-mapos', 'title' => 'Atualizar o Map-OS?', 'message' => 'Os arquivos do sistema são substituídos e as pastas assets/anexos e assets/arquivos são apagadas. Faça backup delas e do banco antes.', 'confirm_label' => 'Atualizar', 'icon' => 'triangle-alert', 'confirm_attrs' => ['form' => 'form-atualizar-mapos']]) ?>
    <?php } else { ?>
        <form method="post" action="<?= e(site_url('mapos/configurar') . listagemQuery(['aba' => $aba])) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
            <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

            <?php foreach ($grupos as $titulo => $lista) { ?>
                <section class="<?= e($caixa) ?>" aria-label="<?= e($titulo !== '' ? $titulo : CONFIG_ABAS[$aba]) ?>">
                    <?php if ($titulo !== '') { ?>
                        <h2 class="mb-4 text-heading-sm text-text"><?= e($titulo) ?></h2>
                    <?php } ?>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <?php foreach ($lista as $nome => $def) { ?>
                            <?php if ($def['tipo'] === 'status') { ?>
                                <fieldset class="flex flex-col gap-2 sm:col-span-2">
                                    <legend class="text-label-md text-text"><?= e($def['rotulo']) ?></legend>
                                    <p class="text-caption text-muted"><?= e($def['ajuda']) ?></p>
                                    <div class="mt-1 grid gap-2 sm:grid-cols-3">
                                        <?php foreach ($def['opcoes'] as $status => $rotuloStatus) { ?>
                                            <?= component('checkbox', ['name' => $nome . '[]', 'id' => 'config-status-' . md5($status), 'value' => $status, 'label' => $rotuloStatus, 'checked' => in_array($status, $valores[$nome], true)]) ?>
                                        <?php } ?>
                                    </div>
                                    <?php if (isset($errosDeCampo[$nome])) { ?>
                                        <p class="text-caption text-danger-ink"><?= e($errosDeCampo[$nome]) ?></p>
                                    <?php } ?>
                                </fieldset>
                            <?php } elseif ($def['tipo'] === 'area') { ?>
                                <div class="flex flex-col gap-2 sm:col-span-2">
                                    <?= componenteConteudo($campo($nome, $def)) ?>
                                    <div class="flex flex-wrap gap-1.5" aria-label="Marcadores">
                                        <?php foreach (CONFIG_MARCADORES_WHATSAPP as $marcador => $rotuloMarcador) { ?>
                                            <?= component('button', ['label' => $rotuloMarcador, 'variant' => 'ghost', 'size' => 'sm', 'attrs' => ['data-marcador' => $marcador, 'data-marcador-alvo' => 'config-' . strtolower($nome), 'title' => $marcador]]) ?>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="<?= e(in_array($def['tipo'], ['sim_nao', 'bool_env', 'gerar_jwt'], true) ? 'sm:col-span-2' : '') ?>"><?= componenteConteudo($campo($nome, $def)) ?></div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </section>
            <?php } ?>

            <div class="flex justify-end">
                <?= component('button', ['label' => 'Salvar', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
            </div>
        </form>
    <?php } ?>
</div>
