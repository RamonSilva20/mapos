<?php
// Extrai ID do YouTube de qualquer formato de URL
function _yt_id(string $url): string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return $m[1];
    }
    return '';
}

$videoId     = ! empty($manifest['video_url']) ? _yt_id($manifest['video_url']) : '';
$screenshots = ! empty($manifest['screenshots']) && is_array($manifest['screenshots'])
    ? array_filter($manifest['screenshots'])
    : [];
$instalado_status = $instalado ? $instalado->status : null;
?>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">

        <!-- Cabeçalho -->
        <div class="widget-box">
            <div class="widget-title" style="margin:-20px 0 0">
                <span class="icon"><i class='bx bx-plug'></i></span>
                <h5>
                    <a href="<?= site_url('modulos') ?>" style="color:inherit">Módulos</a>
                    &rsaquo; <?= htmlspecialchars($manifest['name'] ?? $slug) ?>
                </h5>
                <div style="float:right;padding:6px 15px;display:flex;gap:8px;align-items:center">
                    <?php if ($instalado): ?>
                        <span class="label <?= $instalado_status === 'ativo' ? 'label-success' : 'label-default' ?>">
                            <?= $instalado_status === 'ativo' ? 'Ativo' : 'Inativo' ?>
                        </span>
                        <form method="post" action="<?= site_url('modulos/toggle/' . $slug) ?>" style="display:inline">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                            <button type="submit" class="btn btn-small <?= $instalado_status === 'ativo' ? 'btn-warning' : 'btn-success' ?>">
                                <?= $instalado_status === 'ativo' ? 'Desativar' : 'Ativar' ?>
                            </button>
                        </form>
                        <button class="btn btn-danger btn-small"
                            onclick="confirmarDesinstalar('<?= htmlspecialchars($slug) ?>', '<?= htmlspecialchars(addslashes($manifest['name'] ?? $slug)) ?>')">
                            Desinstalar
                        </button>
                    <?php else: ?>
                        <a href="<?= site_url('modulos/confirmar/' . $slug) ?>" class="btn btn-primary btn-small">
                            <i class='bx bx-download'></i> Instalar
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="widget-content">
                <div class="row-fluid">

                    <!-- Coluna info -->
                    <div class="span<?= ($videoId || $screenshots) ? '5' : '12' ?>">
                        <h4 style="margin-top:0"><?= htmlspecialchars($manifest['name'] ?? $slug) ?></h4>
                        <?php if (! empty($manifest['description'])): ?>
                            <p><?= htmlspecialchars($manifest['description']) ?></p>
                        <?php endif; ?>

                        <table class="table table-condensed" style="max-width:380px">
                            <tr><th>Slug</th><td><code><?= htmlspecialchars($slug) ?></code></td></tr>
                            <tr><th>Versão</th><td><?= htmlspecialchars($manifest['version'] ?? '—') ?></td></tr>
                            <tr><th>Autor</th><td><?= htmlspecialchars($manifest['author'] ?? '—') ?></td></tr>
                        </table>

                        <?php if (! empty($manifest['menu'])): ?>
                            <h5>Itens de menu</h5>
                            <ul style="list-style:none;padding:0">
                                <?php foreach ($manifest['menu'] as $item): ?>
                                    <li style="margin-bottom:4px">
                                        <i class='<?= htmlspecialchars($item['icon'] ?? 'bx bx-extension') ?>'></i>
                                        <strong><?= htmlspecialchars($item['title'] ?? '') ?></strong>
                                        — <code><?= htmlspecialchars($item['url'] ?? '') ?></code>
                                        <?php if (! empty($item['permission'])): ?>
                                            <span class="label">perm: <?= htmlspecialchars($item['permission']) ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (! empty($manifest['permissions'])): ?>
                            <h5>Permissões</h5>
                            <p><?= implode(', ', array_map(fn($p) => "<code>{$p}</code>", $manifest['permissions'])) ?></p>
                        <?php endif; ?>

                        <?php if (! empty($manifest['view_hooks'])): ?>
                            <h5>View Hooks</h5>
                            <ul style="list-style:none;padding:0">
                                <?php foreach ($manifest['view_hooks'] as $h): ?>
                                    <li><code><?= htmlspecialchars($h['hook']) ?></code></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (! empty($manifest['controller_hooks'])): ?>
                            <h5>Controller Hooks</h5>
                            <ul style="list-style:none;padding:0">
                                <?php foreach ($manifest['controller_hooks'] as $h): ?>
                                    <li><code><?= htmlspecialchars($h['hook'] ?? $h) ?></code></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (file_exists(FCPATH . 'modules/' . $slug . '/migrations/install.sql')): ?>
                            <p><span class="label label-info"><i class='bx bx-data'></i> Executa SQL de instalação</span></p>
                        <?php endif; ?>
                    </div>

                    <!-- Coluna mídia -->
                    <?php if ($videoId || $screenshots): ?>
                    <div class="span7">

                        <?php if ($videoId): ?>
                        <div style="margin-bottom:16px">
                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:4px;background:#000">
                                <iframe
                                    src="https://www.youtube.com/embed/<?= htmlspecialchars($videoId) ?>"
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0"
                                    allowfullscreen
                                    loading="lazy">
                                </iframe>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($screenshots): ?>
                        <div id="gallery" style="display:flex;flex-wrap:wrap;gap:8px">
                            <?php foreach ($screenshots as $i => $url): ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank"
                                   style="display:block;flex:0 0 calc(50% - 4px)">
                                    <img src="<?= htmlspecialchars($url) ?>"
                                         alt="Screenshot <?= $i + 1 ?>"
                                         style="width:100%;border-radius:4px;border:1px solid #ddd;cursor:zoom-in"
                                         onerror="this.closest('a').style.display='none'">
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Form oculto para desinstalar -->
<form id="formDesinstalar" method="post" action="" style="display:none">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
</form>

<script>
function confirmarDesinstalar(slug, nome) {
    swal({
        title: 'Desinstalar módulo?',
        text: 'Tem certeza que deseja desinstalar "' + nome + '"? Esta ação removerá todos os dados do módulo.',
        icon: 'warning',
        buttons: { cancel: 'Cancelar', confirm: 'Sim, desinstalar' },
        dangerMode: true
    }).then(function(confirmed) {
        if (confirmed) {
            var form = document.getElementById('formDesinstalar');
            form.action = '<?= site_url('modulos/desinstalar/') ?>' + slug;
            form.submit();
        }
    });
}
</script>
