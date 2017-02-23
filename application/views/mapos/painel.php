<!-- [if lt IE 9]><script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/dist/excanvas.min.js"></script><![endif]-->

<link rel="stylesheet" type="text/css" href="<?=base_url('assets/js/dist/jquery.jqplot.min.css');?>" />
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/painel.css');?>" />


<!--Action boxes -->
  <div class="container-fluid">
    <div class="quick-actions_homepage">
      <ul class="quick-actions">
        <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vCliente')){ ?>
            <li class="bg_lb">
              <span class="label label-success"><?=$this->db->count_all('clientes')?></span>
               <a href="<?=site_url('clientes')?>"> <i class="icon-group"></i> Clientes</a>
            </li>

        <?php } ?>
        <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vProduto')){ ?>
            <li class="bg_lg">
              <span class="label label-success"><?=$this->db->count_all('produtos')?></span>
              <a href="<?=site_url('produtos')?>"> <i class="icon-barcode"></i> Produtos</a>
            </li>
        <?php } ?>
        <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vServico')){ ?>
            <li class="bg_ly">
              <span class="label label-success"><?=$this->db->count_all('servicos')?></span>
              <a href="<?=site_url('servicos')?>"> <i class="icon-wrench"></i> Serviços</a> </li>
        <?php } ?>
        <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
            <li class="bg_lo">
              <span class="label label-success"><?=$this->db->count_all('os')?></span>
              <a href="<?=site_url('os')?>"> <i class="icon-tags"></i> OS</a> </li>
        <?php } ?>
        <?php
        if($this->permission->checkPermission($this->session->userdata('permissao'),'vVenda')){ ?>
            <li class="bg_ls">
              <span class="label label-success"><?=$this->db->count_all('vendas')?></span>
               <a href="<?=site_url('vendas')?>"><i class="icon-shopping-cart"></i> Vendas</a></li>
        <?php } ?>        
        <?php
        if($this->permission->checkPermission($this->session->userdata('permissao'),'vLancamento')){ ?>
            <li class="bg_lb">
              <span class="label label-success"><?=$this->db->count_all('lancamentos')?></span>
               <a href="<?=site_url('financeiro/lancamentos')?>"><i class="icon icon-money"></i>Financeiro</a></li>
        <?php } ?>
      </ul>
    </div>
    <div class="quick-actions_homepage" style="text-align:left">
      <a class="btn btn-info" href="<?=site_url('clientes/adicionar')?>">Novo Cliente</a>
      <a class="btn btn-success" href="<?=site_url('produtos/adicionar')?>">Novo Produto</a>
      <a class="btn btn-warning" href="<?=site_url('servicos/adicionar')?>">Novo Serviço</a>
      <a class="btn btn-danger" href="<?=site_url('os/adicionar')?>">Nova OS</a>
      <a class="btn btn-primary" href="<?=site_url('vendas/adicionar')?>">Nova Venda</a>

    </div>
  </div>
<!--End-Action boxes-->



<div class="row-fluid" style="margin-top: 0">
    <div class="span12" style="margin-left: 0">
        <div class="accordion-group widget-box">
            <div class="widget-title" data-toggle="collapse" href="#servicoAberto">
            <span class="icon"><i class="icon-chevron-down"></i></span><span class="icon"><i class="icon-signal"></i></span>
            <h5>Ordens de Serviço Em Aberto</h5>

            </div>
            <div class="widget-content nopadding collapse" id="servicoAberto" style="height: auto;">
                <table class="table table-bordered data-table os-abertas" data-url="<?=site_url('mapos/os_aberta')?>">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data Inicial</th>
                            <th>Data Final</th>
                            <th>Cliente</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="span12" style="margin-left: 0">
        <div class="accordion-group widget-box">
            <div class="widget-title" data-toggle="collapse" href="#ordens_orcamento"><span class="icon"><i class="icon-chevron-down"></i></span><span class="icon"><i class="icon-signal"></i></span><h5>Ordens de Serviço Orçamento</h5></div>
            <div class="widget-content nopadding collapse" id="ordens_orcamento" style="height: 0px;">
                <table class="table table-bordered data-table os-orcamento" data-url="<?=site_url('mapos/os_aberta')?>">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data Inicial</th>
                            <th>Data Final</th>
                            <th>Cliente</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="span12" style="margin-left: 0">
        <div class="widget-box accordion-group ">
            <div class="widget-title" data-toggle="collapse" href="#estoqueMinimo"><span class="icon"><i class="icon-chevron-down"></i></span><span class="icon"><i class="icon-signal"></i></span><h5>Produtos Com Estoque Mínimo</h5></div>
            <div class="widget-content nopadding collapse" id="estoqueMinimo" style="height: 0px;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Preço de Venda</th>
                            <th>Estoque</th>
                            <th>Estoque Mínimo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($produtos != null){
                            foreach ($produtos as $p) {
                                echo '<tr>';
                                echo '<td>'.$p->idProdutos.'</td>';
                                echo '<td>'.$p->descricao.'</td>';
                                echo '<td>R$ '.$p->precoVenda.'</td>';
                                echo '<td>'.$p->estoque.'</td>';
                                echo '<td>'.$p->estoqueMinimo.'</td>';
                                echo '<td>';
                                if($this->permission->checkPermission($this->session->userdata('permissao'),'eProduto')){
                                    echo '<a href="'.site_url('produtos/editar/'.$p->idProdutos).'" class="btn btn-info"> <i class="icon-pencil" ></i> </a>  ';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        else{
                            echo '<tr><td colspan="3">Nenhum produto com estoque baixo.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php if($estatisticas_financeiro != null){
      if($estatisticas_financeiro->total_receita != null || $estatisticas_financeiro->total_despesa != null || $estatisticas_financeiro->total_receita_pendente != null || $estatisticas_financeiro->total_despesa_pendente != null){  ?>
<div class="row-fluid" style="margin-top: 0">

    <div class="span4">

        <div class="widget-box">
            <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Estatísticas financeiras - Realizado</h5></div>
            <div class="widget-content">
                <div class="row-fluid">
                    <div class="span12">
                      <div id="chart-financeiro" style=""></div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="span4">

        <div class="widget-box">
            <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Estatísticas financeiras - Pendente</h5></div>
            <div class="widget-content">
                <div class="row-fluid">
                    <div class="span12">
                      <div id="chart-financeiro2" style=""></div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="span4">

        <div class="widget-box">
            <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Total em caixa / Previsto</h5></div>
            <div class="widget-content">
                <div class="row-fluid">
                    <div class="span12">
                      <div id="chart-financeiro-caixa" style=""></div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
<?php } } ?>

<?php if($os != null){ ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Estatísticas de OS</h5></div>
            <div class="widget-content">
                <div class="row-fluid">
                    <div class="span12">
                      <div id="chart-os" style=""></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<script language="javascript" type="text/javascript" src="<?=base_url('assets/js/jquery.min.js');?>"></script>
<script language="javascript" type="text/javascript" src="<?=base_url('assets/js/dist/jquery.jqplot.min.js')?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/select2.min.js');?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/jquery.uniform.js');?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/jquery.dataTables110.min.js')?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/dist/plugins/jqplot.pieRenderer.min.js');?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/dist/plugins/jqplot.donutRenderer.min.js');?>"></script>
<script src="<?=base_url('assets/js/bootstrap.min.js')?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/matrix.js');?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/matrix.tables.js');?>"></script>


<?php if($os != null) {?>
<script type="text/javascript">

    $(document).ready(function(){
      var data = [
        <?php foreach ($os as $o) {
            echo "['".$o->status."', ".$o->total."],";
        } ?>

      ];
      var plot1 = jQuery.jqplot ('chart-os', [data],
        {
          seriesDefaults: {
            // Make this a pie chart.
            renderer: jQuery.jqplot.PieRenderer,
            rendererOptions: {
              // Put data labels on the pie slices.
              // By default, labels show the percentage of the slice.
              showDataLabels: true
            }
          },
          legend: { show:true, location: 'e' }
        }
      );

    });

</script>

<?php } ?>



<?php if(isset($estatisticas_financeiro) && $estatisticas_financeiro != null) {
         if($estatisticas_financeiro->total_receita != null || $estatisticas_financeiro->total_despesa != null || $estatisticas_financeiro->total_receita_pendente != null || $estatisticas_financeiro->total_despesa_pendente != null){
?>
<script type="text/javascript">

    $(document).ready(function(){

      var data2 = [['Total Receitas',<?php echo ($estatisticas_financeiro->total_receita != null ) ?  $estatisticas_financeiro->total_receita : '0.00'; ?>],['Total Despesas', <?php echo ($estatisticas_financeiro->total_despesa != null ) ?  $estatisticas_financeiro->total_despesa : '0.00'; ?>]];
      var plot2 = jQuery.jqplot ('chart-financeiro', [data2],
        {

          seriesColors: [ "#9ACD32", "#FF8C00", "#EAA228", "#579575", "#839557", "#958c12","#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"],
          seriesDefaults: {
            // Make this a pie chart.
            renderer: jQuery.jqplot.PieRenderer,
            rendererOptions: {
              // Put data labels on the pie slices.
              // By default, labels show the percentage of the slice.
              dataLabels: 'value',
              showDataLabels: true
            }
          },
          legend: { show:true, location: 'e' }
        }
      );


      var data3 = [['Total Receitas',<?php echo ($estatisticas_financeiro->total_receita_pendente != null ) ?  $estatisticas_financeiro->total_receita_pendente : '0.00'; ?>],['Total Despesas', <?php echo ($estatisticas_financeiro->total_despesa_pendente != null ) ?  $estatisticas_financeiro->total_despesa_pendente : '0.00'; ?>]];
      var plot3 = jQuery.jqplot ('chart-financeiro2', [data3],
        {

          seriesColors: [ "#90EE90", "#FF0000", "#EAA228", "#579575", "#839557", "#958c12","#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"],
          seriesDefaults: {
            // Make this a pie chart.
            renderer: jQuery.jqplot.PieRenderer,
            rendererOptions: {
              // Put data labels on the pie slices.
              // By default, labels show the percentage of the slice.
              dataLabels: 'value',
              showDataLabels: true
            }
          },
          legend: { show:true, location: 'e' }
        }

      );


      var data4 = [['Total em Caixa',<?php echo ($estatisticas_financeiro->total_receita - $estatisticas_financeiro->total_despesa); ?>],['Total a Entrar', <?php echo ($estatisticas_financeiro->total_receita_pendente - $estatisticas_financeiro->total_despesa_pendente); ?>]];
      var plot4 = jQuery.jqplot ('chart-financeiro-caixa', [data4],
        {

          seriesColors: ["#839557","#d8b83f", "#d8b83f", "#ff5800", "#0085cc"],
          seriesDefaults: {
            // Make this a pie chart.
            renderer: jQuery.jqplot.PieRenderer,
            rendererOptions: {
              // Put data labels on the pie slices.
              // By default, labels show the percentage of the slice.
              dataLabels: 'value',
              showDataLabels: true
            }
          },
          legend: { show:true, location: 'e' }
        }

      );


    });

</script>

<?php } } ?>
