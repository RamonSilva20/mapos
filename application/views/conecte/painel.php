
  <div class="quick-actions_homepage">
    <ul class="quick-actions">
      <li class="bg_lo span3"> <a href="<?php echo base_url()?>index.php/conecte/os"> <i class="icon-tags"></i> Ordens de Serviço</a> </li>
      <li class="bg_ls span3"> <a href="<?php echo base_url()?>index.php/conecte/compras"><i class="icon-shopping-cart"></i> Compras</a></li>
      <li class="bg_lg span3"> <a href="<?php echo base_url()?>index.php/conecte/conta"><i class="icon-star"></i> Minha Conta</a></li>
    </ul>
  </div>
 

  <div class="span12" style="margin-left: 0">
      
      <div class="widget-box">
          <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Últimas Ordens de Serviço</h5></div>
          <div class="widget-content">
              <table class="table table-bordered">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Data Inicial</th>
                          <th>Data Final</th>
                          <th>Garantia</th>
                          <th>Status</th>
                          <th></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php 
                      if($os != null){
                          foreach ($os as $o) {

                            
                              echo '<tr>';
                              echo '<td>'.$o->idOs.'</td>';
                              echo '<td>'.date('d/m/Y',strtotime($o->dataInicial)).'</td>';
                              echo '<td>'.date('d/m/Y',strtotime($o->dataFinal)).'</td>';
                              echo '<td>'.$o->garantia.'</td>';
                              echo '<td>'.$o->status.'</td>';
                              echo '<td> <a href="'.base_url().'index.php/conecte/visualizarOs/'.$o->idOs.'" class="btn"> <i class="icon-eye-open" ></i> </a></td>';
                              echo '</tr>';
                          }
                      }
                      else{
                          echo '<tr><td colspan="3">Nenhum ordem de serviço encontrada.</td></tr>';
                      }    

                      ?>
                  </tbody>
              </table>
          </div>
      </div>

      <div class="widget-box">
          <div class="widget-title"><span class="icon"><i class="icon-signal"></i></span><h5>Últimas Compras</h5></div>
          <div class="widget-content">
              <table class="table table-bordered">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Data de Venda</th>
                          <th>Responsável</th>
                          <th>Faturado</th>
                          <th></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php 
                      if($compras != null){
                          foreach ($compras as $p) {
                              if($p->faturado == 1){$faturado = 'Sim';} else{$faturado = 'Não';}
                              echo '<tr>';
                              echo '<td>'.$p->idVendas.'</td>';
                              echo '<td>'.date('d/m/Y',strtotime($p->dataVenda)).'</td>';
                              echo '<td>'.$p->nome.'</td>';
                              echo '<td>'.$faturado.'</td>';
                              echo '<td> <a href="'.base_url().'index.php/conecte/visualizarCompra/'.$p->idVendas.'" class="btn"> <i class="icon-eye-open" ></i> </a></td>';
                              echo '</tr>';
                          }
                      }
                      else{
                          echo '<tr><td colspan="5">Nenhum venda encontrada.</td></tr>';
                      }    

                      ?>
                  </tbody>
              </table>
          </div>
      </div>
    
  </div>
