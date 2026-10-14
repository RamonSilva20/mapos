<?php
defined('INSTALADOR') || exit;
/**
 * Configuração: banco de dados, administrador, URL e API REST. O formulário é
 * enviado por fetch (install/assets/js/main.js) para do_install.php.
 *
 * @var string $storage_sugerido Pasta sugerida para os anexos (vazio quando a pasta acima da raiz não aceita escrita)
 */
$campo = 'w-full rounded-md border border-input bg-field px-3 py-2 text-text placeholder:text-muted focus:outline-3 focus:outline-ring/50 sm:flex-1';
?>
<form id="formulario-instalacao" action="do_install.php" method="post" class="flex flex-col gap-6 p-4 sm:p-6">
  <fieldset class="flex flex-col gap-3">
    <legend class="mb-1 text-body-strong">1. Conexão com o <strong>banco de dados</strong></legend>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="host" class="text-label-md sm:w-1/4">Host</label>
        <input type="text" id="host" name="host" placeholder="Geralmente localhost" required class="<?= $campo ?>">
      </div>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="dbuser" class="text-label-md sm:w-1/4">Usuário</label>
        <input type="text" id="dbuser" name="dbuser" placeholder="Usuário do banco de dados" required autocomplete="off" class="<?= $campo ?>">
      </div>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="dbpassword" class="text-label-md sm:w-1/4">Senha</label>
        <input type="password" id="dbpassword" name="dbpassword" placeholder="Senha do usuário do banco de dados" autocomplete="off" class="<?= $campo ?>">
      </div>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="dbname" class="text-label-md sm:w-1/4">Banco de dados</label>
        <input type="text" id="dbname" name="dbname" placeholder="Nome do banco de dados" required class="<?= $campo ?>">
      </div>
  </fieldset>

  <fieldset class="flex flex-col gap-3">
    <legend class="mb-1 text-body-strong">2. Conta de <strong>administrador</strong></legend>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="full_name" class="text-label-md sm:w-1/4">Nome</label>
        <input type="text" id="full_name" name="full_name" placeholder="Nome completo" required class="<?= $campo ?>">
      </div>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="email" class="text-label-md sm:w-1/4">E-mail</label>
        <input type="email" id="email" name="email" placeholder="Seu e-mail" required class="<?= $campo ?>">
      </div>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="password" class="text-label-md sm:w-1/4">Senha</label>
        <input type="password" id="password" name="password" placeholder="Senha de login" required autocomplete="new-password" class="<?= $campo ?>">
      </div>
  </fieldset>

  <fieldset class="flex flex-col gap-3">
    <legend class="mb-1 text-body-strong">3. URL do sistema</legend>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="base_url" class="text-label-md sm:w-1/4">URL</label>
        <input type="url" id="base_url" name="base_url" placeholder="Endereço onde o Map-OS será acessado" required class="<?= $campo ?>">
      </div>
  </fieldset>

  <fieldset class="flex flex-col gap-3">
    <legend class="mb-1 text-body-strong">4. Pasta dos <strong>anexos</strong></legend>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="storage_path" class="text-label-md sm:w-1/4">Pasta (STORAGE_PATH)</label>
        <input type="text" id="storage_path" name="storage_path" value="<?= htmlspecialchars($storage_sugerido, ENT_QUOTES) ?>" placeholder="Vazio: application/storage" autocomplete="off" class="<?= $campo ?>">
      </div>
      <p class="text-sm text-muted">Anexos de OS e arquivos ficam aqui e só saem por tela com permissão. Use uma pasta <strong>fora</strong> da pasta pública do servidor; o instalador a cria se puder. Vazio usa <code>application/storage</code>, que no nginx precisa ser bloqueada (veja docs/servidor-web.md).</p>
  </fieldset>

  <fieldset class="flex flex-col gap-3">
    <legend class="mb-1 text-body-strong">5. API REST (usada no aplicativo)</legend>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="enter_api_enabled" class="text-label-md sm:w-1/4">Habilitar?</label>
        <select id="enter_api_enabled" name="enter_api_enabled" class="<?= $campo ?>">
          <option value="true">Sim</option>
          <option value="false" selected>Não</option>
        </select>
      </div>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
        <label for="enter_token_expire_time" class="text-label-md sm:w-1/4">Expiração do token JWT (para usuários, no máximo 1 hora)</label>
        <select id="enter_token_expire_time" name="enter_token_expire_time" class="<?= $campo ?>">
          <option value="60">1 minuto</option>
          <option value="3600" selected>1 hora</option>
          <option value="86400">1 dia</option>
          <option value="604800">1 semana</option>
          <option value="2592000">1 mês</option>
        </select>
      </div>
  </fieldset>

  <div class="flex justify-end">
    <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 font-bold text-on-primary hover:bg-primary-hover disabled:cursor-wait disabled:bg-disabled-bg disabled:text-disabled">
      <span data-carregando hidden>Por favor, espere…</span>
      <span data-rotulo class="inline-flex items-center gap-2">Instalar <?= instalador_icone('seta', 'size-4') ?></span>
    </button>
  </div>
</form>
