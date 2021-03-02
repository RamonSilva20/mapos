<link rel="stylesheet" href="<?= base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>

<div class="span12" style="margin-left: 0">
    <form method="get" action="<?= current_url(); ?>">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aArquivo')) : ?>
            <div class="span3">
                <a href="<?= base_url(); ?>index.php/arquivos/adicionar" class="btn btn-success span12">
                    <i class="fas fa-plus"></i>
                    Adicionar Arquivo
                </a>
            </div>
        <?php endif ?>

        <div class="span5">
            <input type="text" name="pesquisa" id="pesquisa" placeholder="Digite o nome do documento para pesquisar" class="span12" value="<?= $this->input->get('pesquisa') ?>">
        </div>
        <div class="span3">
            <input type="text" name="data" id="data" placeholder="Data de" class="span6 datepicker" value="<?= $this->input->get('data') ?>">
            <input type="text" name="data2" id="data2" placeholder="Data até" class="span6 datepicker" value="<?= $this->input->get('data2') ?>">
        </div>
        <div class="span1">
            <button class="span12 btn"> <i class="fas fa-search"></i> </button>
        </div>
    </form>
</div>

<div class="span12" style="margin-left: 0">
    <div class="widget-box">
        <div class="widget-title">
            <span class="icon">
                <i class="fas fa-hdd"></i>
            </span>
            <h5>Arquivos</h5>
        </div>
        <div class="widget_content nopadding">
                <table id="tabela" width="100%" class="table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">Miniatura</th>
                        <th>Nome</th>
                        <th width="8%">Data</th>
                        <th>Descrição</th>
                        <th width="8%">Tamanho</th>
                        <th width="5%">Extensão</th>
                        <th width="10%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    
                    if(!$results){

                        echo '<tr>
                                <td colspan="5">Nenhum Arquivo Encontrado</td>
                              </tr>';
                    }
                    foreach ($results as $r) : ?>
                    	<tr>
                        <td><div align="center"><?= $r->idDocumentos ?></td>
                        <td><div align="center"><a href="<?= $r->url ?>"><a href="<?= $r->url ?>" target="_new"><img src=" <?= $r->url ?> "></td>
                        <td><?= $r->documento ?></td>
                        <td><div align="center"><?= date('d/m/Y', strtotime($r->cadastro)) ?></td>
                        <td><?= $r->descricao ?></td>
                        <td><div align="center"><?= $r->tamanho ?> KB</div></td>
                        <td><div align="center"><?= $r->tipo ?></td>
                        <td><div align="center"><?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eArquivo')) : ?>
                                    <a href="<?= base_url() ?>index.php/arquivos/editar/<?= $r->idDocumentos ?>" class="btn btn-info tip-top" style="margin-right: 1%" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif ?>

                              <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dArquivo')) : ?>
                                    <a href="#modal-excluir" style="margin-right: 1%" role="button" data-toggle="modal" arquivo="<?= $r->idDocumentos ?>" class="btn btn-danger tip-top" title="Excluir Arquivo">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                <?php endif ?>
                            </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
              </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->pagination->create_links() ?>

<div id="modal-excluir" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?= base_url() ?>index.php/arquivos/excluir" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Excluir Arquivo</h3>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idDocumento" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este arquivo?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var arquivo = $(this).attr('arquivo');
            $('#idDocumento').val(arquivo);
        });
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });
</script>
