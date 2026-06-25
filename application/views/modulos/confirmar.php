<?php
function _yt_id_confirmar(string $url): string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return $m[1];
    }
    return '';
}
$_ytId        = ! empty($manifest['video_url']) ? _yt_id_confirmar($manifest['video_url']) : '';
$_screenshots = ! empty($manifest['screenshots']) && is_array($manifest['screenshots'])
    ? array_filter($manifest['screenshots']) : [];
$_hasMedia    = $_ytId || $_screenshots;
?>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon"><i class='bx bx-package'></i></span>
                <h5>Confirmar Instalação de Módulo</h5>
            </div>

            <div class="widget-content">
                <div class="row-fluid">

                    <!-- Informações do módulo -->
                    <div class="<?= $_hasMedia ? 'span6' : 'span8' ?>">
                        <h4><?= htmlspecialchars($manifest['name'] ?? $slug) ?></h4>
                        <?php if (! empty($manifest['description'])): ?>
                            <p class="muted"><?= htmlspecialchars($manifest['description']) ?></p>
                        <?php endif; ?>

                        <table class="table table-condensed" style="max-width:400px">
                            <tr>
                                <th>Slug</th>
                                <td><code><?= htmlspecialchars($slug) ?></code></td>
                            </tr>
                            <tr>
                                <th>Versão</th>
                                <td><?= htmlspecialchars($manifest['version'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th>Autor</th>
                                <td><?= htmlspecialchars($manifest['author'] ?? '—') ?></td>
                            </tr>
                        </table>

                        <?php if (! empty($manifest['menu'])): ?>
                            <h5>Itens que serão adicionados ao menu</h5>
                            <ul>
                                <?php foreach ($manifest['menu'] as $item): ?>
                                    <li>
                                        <i class='<?= htmlspecialchars($item['icon'] ?? 'bx bx-extension') ?>'></i>
                                        <?= htmlspecialchars($item['title'] ?? '') ?>
                                        — <code><?= htmlspecialchars($item['url'] ?? '') ?></code>
                                        <?php if (! empty($item['permission'])): ?>
                                            <span class="label">perm: <?= htmlspecialchars($item['permission']) ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (! empty($manifest['configurations'])): ?>
                            <h5>Configurações que serão adicionadas</h5>
                            <ul>
                                <?php foreach ($manifest['configurations'] as $cfg): ?>
                                    <li><code><?= htmlspecialchars($cfg['key']) ?></code> = <em><?= htmlspecialchars($cfg['value'] ?? '') ?></em></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (file_exists(FCPATH . 'modules/' . $slug . '/migrations/install.sql')): ?>
                            <p><span class="label label-info"><i class='bx bx-data'></i> Este módulo executará SQL de instalação</span></p>
                        <?php endif; ?>
                    </div>

                    <!-- Mídia (vídeo + screenshots) -->
                    <?php if ($_hasMedia): ?>
                    <div class="span6">
                        <?php if ($_ytId): ?>
                        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:4px;background:#000;margin-bottom:12px">
                            <iframe
                                src="https://www.youtube.com/embed/<?= htmlspecialchars($_ytId) ?>"
                                style="position:absolute;top:0;left:0;width:100%;height:100%;border:0"
                                allowfullscreen loading="lazy">
                            </iframe>
                        </div>
                        <?php endif; ?>

                        <?php if ($_screenshots): ?>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            <?php foreach ($_screenshots as $i => $url): ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank"
                                   style="flex:0 0 calc(50% - 3px)">
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

                <hr>

                <form method="post" action="<?= site_url('modulos/instalar/' . $slug) ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <a href="<?= site_url('modulos') ?>" class="btn">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-download'></i> Confirmar e Instalar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
