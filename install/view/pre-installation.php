<?php
defined('INSTALADOR') || exit;
/**
 * Pré-instalação: requisitos de PHP, extensões e permissões de escrita.
 * Cada tabela mostra o valor atual, o requerido e o status.
 *
 * @var string $current_php_version
 * @var string $php_version_required
 * @var bool   $php_version_success
 * @var bool   $allow_url_fopen_success
 * @var bool   $vendor_success
 * @var bool   $all_requirement_success
 */
$cabecalho = 'border-b border-border bg-surface-subtle px-3 py-2 font-medium';
$celula = 'border-b border-border px-3 py-2';
?>
<div class="flex flex-col gap-6 p-4 sm:p-6">
  <div>
    <p class="mb-2">1. Confira se as configurações do seu <strong>PHP</strong> atendem aos requisitos:</p>
    <div class="overflow-x-auto"><table class="w-full text-sm">
      <thead>
        <tr>
          <th class="<?= $cabecalho ?> text-left">Configuração</th>
          <th class="<?= $cabecalho ?> text-left">Atual</th>
          <th class="<?= $cabecalho ?> text-left">Requerida</th>
          <th class="<?= $cabecalho ?> text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="<?= $celula ?>">PHP</td>
          <td class="<?= $celula ?>"><?= htmlspecialchars($current_php_version, ENT_QUOTES) ?></td>
          <td class="<?= $celula ?>"><?= htmlspecialchars($php_version_required, ENT_QUOTES) ?> ou superior</td>
          <td class="<?= $celula ?> text-center"><?= instalador_status($php_version_success) ?></td>
        </tr>
        <tr>
          <td class="<?= $celula ?>">allow_url_fopen</td>
          <td class="<?= $celula ?>"><?= $allow_url_fopen_success ? 'On' : 'Off' ?></td>
          <td class="<?= $celula ?>">On</td>
          <td class="<?= $celula ?> text-center"><?= instalador_status($allow_url_fopen_success) ?></td>
        </tr>
        <tr>
          <td class="<?= $celula ?>">Dependências (application/vendor)</td>
          <td class="<?= $celula ?>"><?= $vendor_success ? 'Instaladas' : 'Ausentes' ?></td>
          <td class="<?= $celula ?>">Instaladas</td>
          <td class="<?= $celula ?> text-center"><?= instalador_status($vendor_success) ?></td>
        </tr>
      </tbody>
    </table></div>
  </div>

  <div>
    <p class="mb-2">2. Confira se as extensões abaixo estão <strong>instaladas e habilitadas</strong> no servidor:</p>
    <div class="overflow-x-auto"><table class="w-full text-sm">
      <thead>
        <tr>
          <th class="<?= $cabecalho ?> text-left">Extensão</th>
          <th class="<?= $cabecalho ?> text-left">Atual</th>
          <th class="<?= $cabecalho ?> text-left">Requerida</th>
          <th class="<?= $cabecalho ?> text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($settings['extensions'] as $extensao) { ?>
          <tr>
            <td class="<?= $celula ?>"><?= htmlspecialchars($extensao, ENT_QUOTES) ?></td>
            <td class="<?= $celula ?>"><?= extension_loaded($extensao) ? 'On' : 'Off' ?></td>
            <td class="<?= $celula ?>">On</td>
            <td class="<?= $celula ?> text-center"><?= instalador_status(extension_loaded($extensao)) ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table></div>
  </div>

  <div>
    <p class="mb-2">3. Dê permissão de <strong>leitura e escrita</strong> aos diretórios e arquivos abaixo:</p>
    <div class="overflow-x-auto"><table class="w-full text-sm">
      <thead>
        <tr>
          <th class="<?= $cabecalho ?> text-left">Arquivo ou diretório</th>
          <th class="<?= $cabecalho ?> text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($settings['writeable_directories'] as $diretorio) { ?>
          <tr>
            <td class="<?= $celula ?>"><?= htmlspecialchars($diretorio, ENT_QUOTES) ?></td>
            <td class="<?= $celula ?> text-center"><?= instalador_status(is_writable('..' . $diretorio)) ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table></div>
  </div>

  <div class="flex justify-end">
    <button type="button" data-proxima <?= $all_requirement_success ? '' : 'disabled' ?> class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 font-bold text-on-primary hover:bg-primary-hover disabled:cursor-not-allowed disabled:bg-disabled-bg disabled:text-disabled">
      Próximo <?= instalador_icone('seta', 'size-4') ?>
    </button>
  </div>
</div>
