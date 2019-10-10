<!-- <link href="<?=base_url('assets/css/lib/sweetalert/sweetalert.css'); ?>" rel="stylesheet"> -->
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="card-content">
                    <?php if($this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')): ?>
                        <div class="row">
                            <div class="col-12">
                                <a  href="<?=base_url(); ?>index.php/os/adicionar" 
                                    class="btn btn-success span12">
                                    <i class="icon-plus icon-white"></i> Adicionar OS
                                </a>
                                <a  href="<?=base_url('index.php/os/adicionar')?>" 
                                    id="abrirFiltro"
                                    class="btn btn-info span12">
                                    <i class="fa fa-search icon-white"></i> Filtros
                                </a>
                            </div>

                        </div>
                    <?php endif ?>

                    <form id="camposFiltro" style="display:none" method="get" action="<?=base_url(); ?>index.php/os/gerenciar">
                        
                        <div class="row pt-2">
                            <div class="col-4">
                                <input type="text"
                                    name="pesquisa"
                                    id="pesquisa"
                                    placeholder="Nome do cliente a pesquisar" 
                                    class="form-control" value="" >
                            </div>
                            <div class="col-3">
                                <select name="status" id="status" class="form-control">
                                    <option value="">Selecione status</option>
                                    <option value="Aberto">Aberto</option>
                                    <option value="Faturado">Faturado</option>
                                    <option value="Em Andamento">Em Andamento</option>
                                    <option value="Orçamento">Orçamento</option>
                                    <option value="Finalizado">Finalizado</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <input type="text" name="data"  id="data"  placeholder="Data Inicial" class="form-control datepicker" value="">
                            </div>  
                            <div class="col-2">
                                <input type="text" name="data2"  id="data2"  placeholder="Data Final" class="form-control datepicker" value="" >
                            </div>
                            <div class="col-1 text-right">
                                <button type="submit" class="btn btn-success"> <i class="fa fa-search"></i> </button>
                            </div>
                        </div>

                    </form>

                    <div class="row pt-2">
                        <div class="col-md-12">

                            <?php if(!empty($results)):?>
                                <div class="table-responsive">
                                    <table id="table"
                                        class="table table-bordered"
                                        style="margin-bottom: 10px">

                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Cliente</th>
                                                <th>Data Inicial</th>
                                                <th>Data Final</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                                <?php foreach($results as $o): ?>
                                                    <tr>
                                                        <td><?=$o->idOs?></td>
                                                        <td><?=$o->nomeCliente?></td>
                                                        <td><?=$o->dataInicial?></td>
                                                        <td><?=$o->dataFinal?></td>
                                                        <td><?=$o->status?></td>
                                                    </tr>
                                                <?php endforeach ?>
                                        </tbody>

                                    </table>
                                </div>   
                            <?php else: ?>
                                <div class="pt-2 col-md-12 text-center =">
                                    Nenhum resultado encontrado!
                                </div>
                            <?php endif ?>
                        </div>
                    </div>


				</div>
			</div>
		</div>
	</div>
</div>

<script>
    $(document).ready(function(){
        $("#abrirFiltro").click(function(e){
            $("#camposFiltro").toggle("slow");
            e.preventDefault();
        })
    })
</script>