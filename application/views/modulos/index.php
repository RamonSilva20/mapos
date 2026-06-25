<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon"><i class='bx bx-plug'></i></span>
                <h5>Gerenciamento de Módulos</h5>
                <div style="float:right;padding:8px 15px;">
                    <button class="btn btn-primary btn-small" data-toggle="modal" data-target="#modalUpload">
                        <i class='bx bx-upload'></i> Instalar Módulo (.zip)
                    </button>
                </div>
            </div>

            <div class="widget-content nopadding">

                <!-- Módulos instalados -->
                <?php if (empty($modulos)): ?>
                    <div style="padding:30px;text-align:center;color:#888;">
                        <i class='bx bx-plug' style="font-size:48px;"></i>
                        <p style="margin-top:10px;">Nenhum módulo instalado.<br>Clique em <strong>Instalar Módulo</strong> para adicionar o primeiro.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-striped table-bordered table-hover" style="margin-bottom:0">
                        <thead>
                            <tr>
                                <th>Módulo</th>
                                <th>Versão</th>
                                <th>Autor</th>
                                <th>Status</th>
                                <th style="width:180px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modulos as $m): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($m->nome) ?></strong>
                                        <?php if ($m->descricao): ?>
                                            <br><small class="muted"><?= htmlspecialchars($m->descricao) ?></small>
                                        <?php endif; ?>
                                        <br><small class="muted">slug: <code><?= htmlspecialchars($m->slug) ?></code></small>
                                    </td>
                                    <td><?= htmlspecialchars($m->versao) ?></td>
                                    <td><?= htmlspecialchars($m->autor ?: '—') ?></td>
                                    <td>
                                        <?php if ($m->status === 'ativo'): ?>
                                            <span class="label label-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="label label-default">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="white-space:nowrap">
                                        <a href="<?= site_url('modulos/visualizar/' . $m->slug) ?>"
                                           class="btn btn-info btn-small" title="Detalhes">
                                            <i class='bx bx-info-circle'></i>
                                        </a>
                                        <form method="post" action="<?= site_url('modulos/toggle/' . $m->slug) ?>" style="display:inline">
                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                            <button type="submit"
                                                class="btn btn-small <?= $m->status === 'ativo' ? 'btn-warning' : 'btn-success' ?>"
                                                title="<?= $m->status === 'ativo' ? 'Desativar' : 'Ativar' ?>">
                                                <i class='bx <?= $m->status === 'ativo' ? 'bx-pause' : 'bx-play' ?>'></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-danger btn-small" title="Desinstalar"
                                            onclick="confirmarDesinstalar('<?= htmlspecialchars($m->slug) ?>', '<?= htmlspecialchars(addslashes($m->nome)) ?>')">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>
        </div>

        <!-- Módulos disponíveis (não instalados) -->
        <?php if (! empty($disponiveis)): ?>
            <div class="widget-box" style="margin-top:20px">
                <div class="widget-title">
                    <span class="icon"><i class='bx bx-package'></i></span>
                    <h5>Disponíveis para Instalação</h5>
                </div>
                <div class="widget-content nopadding">
                    <table class="table table-striped table-bordered table-hover" style="margin-bottom:0">
                        <thead>
                            <tr>
                                <th>Módulo</th>
                                <th>Versão</th>
                                <th>Autor</th>
                                <th style="width:160px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disponiveis as $d): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($d['name'] ?? $d['slug']) ?></strong>
                                        <?php if (! empty($d['description'])): ?>
                                            <br><small class="muted"><?= htmlspecialchars($d['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($d['version'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($d['author'] ?? '—') ?></td>
                                    <td style="white-space:nowrap">
                                        <a href="<?= site_url('modulos/visualizar/' . $d['slug']) ?>"
                                           class="btn btn-info btn-small" title="Detalhes">
                                            <i class='bx bx-info-circle'></i>
                                        </a>
                                        <a href="<?= site_url('modulos/confirmar/' . $d['slug']) ?>"
                                           class="btn btn-primary btn-small" title="Instalar">
                                            <i class='bx bx-download'></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal hide fade" id="modalUpload" tabindex="-1" role="dialog">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h3>Instalar Módulo via ZIP</h3>
    </div>
    <form action="<?= site_url('modulos/upload') ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <div class="modal-body">
            <p>Selecione o arquivo <code>.zip</code> do módulo para fazer upload e instalação.</p>
            <input type="file" name="modulo_zip" accept=".zip" required style="margin-top:10px;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">
                <i class='bx bx-upload'></i> Enviar e Analisar
            </button>
        </div>
    </form>
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
        buttons: {
            cancel: "Cancelar",
            confirm: "Sim, desinstalar"
        },
        dangerMode: true // Substitui o 'confirmButtonColor' e deixa o botão vermelho
    }).then((confirmed) => {
        if (confirmed) {
            var form = document.getElementById('formDesinstalar');
            form.action = '<?= site_url('modulos/desinstalar/') ?>' + slug;
            form.submit();
        }
    });
}
</script>
