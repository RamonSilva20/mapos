<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?php echo base_url(); ?>js/dist/excanvas.min.js"></script><![endif]-->

<script language="javascript" type="text/javascript" src="<?= base_url(); ?>assets/js/dist/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="<?= base_url(); ?>assets/js/dist/plugins/jqplot.pieRenderer.min.js"></script>
<script type="text/javascript" src="<?= base_url(); ?>assets/js/dist/plugins/jqplot.donutRenderer.min.js"></script>
<script src='<?= base_url(); ?>assets/js/fullcalendar.min.js'></script>
<script src='<?= base_url(); ?>assets/js/fullcalendar/locales/pt-br.js'></script>

<link href='<?= base_url(); ?>assets/css/fullcalendar.min.css' rel='stylesheet' />
<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/js/dist/jquery.jqplot.min.css" />

<!--Action boxes-->
<div class="container-fluid">
    <div class="quick-actions_homepage">
        <ul class="quick-actions">
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) : ?>
                <li class="bg_lb">
                    <a href="<?= base_url() ?>index.php/clientes"> <i class="fas fa-users" style="font-size:36px"></i>
                        <div>Clientes <span class="badge badge-light">F1</span></div>
                    </a>
                </li>
            <?php endif ?>

            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) : ?>
                <li class="bg_jj">
                    <a href="<?= base_url() ?>index.php/produtos"> <i class="fas fa-shopping-bag" style="font-size:36px"></i>
                        <div>Produtos <span class="badge badge-light">F2</span></div>
                    </a>
                </li>
            <?php endif ?>

            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) : ?>
                <li class="bg_lg">
                    <a href="<?= base_url() ?>index.php/servicos"> <i class="fas fa-wrench" style="font-size:36px"></i>
                        <div>Serviços <span class="badge badge-light">F3</span></div>
                    </a>
                </li>
            <?php endif ?>

            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?>
                <li class="bg_lo">
                    <a href="<?= base_url() ?>index.php/os"> <i class="fas fa-diagnoses" style="font-size:36px"></i>
                        <div>OS <span class="badge badge-light">F4</span></div>
                    </a>
                </li>
            <?php endif ?>

            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) : ?>
                <li class="bg_ls">
                    <a href="<?= base_url() ?>index.php/vendas"><i class="fas fa-cash-register" style="font-size:36px"></i>
                        <div>Vendas <span class="badge badge-light">F6</span></div>
                    </a>
                </li>
            <?php endif ?>

            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) : ?>
                <li class="bg_jk">
                    <a href="<?= base_url() ?>index.php/financeiro/lancamentos"><i class="fas fa-book" style="font-size:36px"></i>
                        <div>Lançamentos <span class="badge badge-light">F8</span></div>
                    </a>
                </li>
            <?php endif ?>
        </ul>
    </div>
</div>
<!--End-Action boxes-->

<div class="row-fluid" style="margin-top: 0">
	<!--Serviços Concluidos-->
    <div class="widget_box_Painel">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                <h5>Serviços Concluidos</h5>
            </div>
            <div class="widget_content">
                <table class="table_p">
                    <thead>
                        <tr>
                            <th>OS N°</th>
                            <th>Data de Entrada</th>
                            <th>Cliente</th>
                            <th>Contato</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordens6 != null) : ?>
                            <?php foreach ($ordens6 as $o) : ?>
                                <tr>
				<td><div align="center"><?= $o->idOs ?></div></td>
				<td><div align="center"><?= date('d/m/Y', strtotime($o->dataInicial)) ?></div></td>
				<td><div align="center"><?= $o->nomeCliente ?></div></td>
				<td><div align="center"><?= $o->telefone ?></div></td>
				<td><div align="center">R$: <?= number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.') ?></div></td>
				<td><div align="center">
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Visualizar OS" class="btn tip-top" href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn"><i class="fas fa-eye"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) : ?><a title="Editar OS" class="btn btn-info tip-top" href="<?= base_url() ?>index.php/os/editar/<?= $o->idOs ?>" class="btn"><i class="fas fa-edit"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
					$zapnumber = preg_replace("/[^0-9]/", "", $o->telefone);
					$total_os = number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.');
					echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Por WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $o->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $o->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($o->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $o->status . '*.%0d%0a%0d%0a' . strip_tags($o->defeito) . '%0d%0a%0d%0a' . strip_tags($o->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($o->observacoes) . '%0d%0a%0d%0aValor%20Total%20R$&#58%20*'. $total_os . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20estes%20dados%20para%20fazer%20Log-in%0d%0aEmail:%20*' . strip_tags($result->email) . '*%0d%0aSenha:%20*' . strip_tags($result->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';} ?>
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir OS" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimir/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica 2" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica2/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a style="margin-right: 1%" target="_new" href="https://www.linkcorreios.com.br/<?= $o->rastreio ?>" class="btn btn-warning tip-top" title="Rastreio Correio"><i class="fas fa-envelope"></i></a>
				<?php endif ?></div>
                				</td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhum Serviço Concluido.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <!--Fim Serviços Concluidos-->
    
    <!--OS Aprovados-->
    <div class="widget_box_Painel">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                <h5>OS Aprovados</h5>
            </div>
            <div class="widget_content">
                <table class="table_p">
                    <thead>
                        <tr>
                            <th>OS N°</th>
                            <th>Data de Entrada</th>
                            <th>Cliente</th>
                            <th>Contato</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordens3 != null) : ?>
                            <?php foreach ($ordens3 as $o) : ?>
                                <tr>
				<td><div align="center"><?= $o->idOs ?></div></td>
				<td><div align="center"><?= date('d/m/Y', strtotime($o->dataInicial)) ?></div></td>
				<td><div align="center"><?= $o->nomeCliente ?></div></td>
				<td><div align="center"><?= $o->telefone ?></div></td>
				<td><div align="center">R$: <?= number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.') ?></div></td>
				<td><div align="center">
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Visualizar OS" class="btn tip-top" href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn"><i class="fas fa-eye"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) : ?><a title="Editar OS" class="btn btn-info tip-top" href="<?= base_url() ?>index.php/os/editar/<?= $o->idOs ?>" class="btn"><i class="fas fa-edit"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
					$zapnumber = preg_replace("/[^0-9]/", "", $o->telefone);
					$total_os = number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.');
					echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Por WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $o->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $o->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($o->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $o->status . '*.%0d%0a%0d%0a' . strip_tags($o->defeito) . '%0d%0a%0d%0a' . strip_tags($o->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($o->observacoes) . '%0d%0a%0d%0aValor%20Total%20R$&#58%20*'. $total_os . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20estes%20dados%20para%20fazer%20Log-in%0d%0aEmail:%20*' . strip_tags($result->email) . '*%0d%0aSenha:%20*' . strip_tags($result->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';} ?>
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir OS" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimir/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica 2" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica2/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a style="margin-right: 1%" target="_new" href="https://www.linkcorreios.com.br/<?= $o->rastreio ?>" class="btn btn-warning tip-top" title="Rastreio Correio"><i class="fas fa-envelope"></i></a>
				<?php endif ?></div>
                				</td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhuma OS Aprovados.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <!--Fim Orçamentos Concluidos-->
    
    <!--Orçamentos Concluidos-->
    <div class="widget_box_Painel">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                <h5>Orçamentos Concluidos</h5>
            </div>
            <div class="widget_content">
                <table class="table_p">
                    <thead>
                        <tr>
                            <th>OS N°</th>
                            <th>Data de Entrada</th>
                            <th>Cliente</th>
                            <th>Contato</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordens2 != null) : ?>
                            <?php foreach ($ordens2 as $o) : ?>
                                <tr>
				<td><div align="center"><?= $o->idOs ?></div></td>
				<td><div align="center"><?= date('d/m/Y', strtotime($o->dataInicial)) ?></div></td>
				<td><div align="center"><?= $o->nomeCliente ?></div></td>
				<td><div align="center"><?= $o->telefone ?></div></td>
				<td><div align="center">R$: <?= number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.') ?></div></td>
				<td><div align="center">
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Visualizar OS" class="btn tip-top" href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn"><i class="fas fa-eye"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) : ?><a title="Editar OS" class="btn btn-info tip-top" href="<?= base_url() ?>index.php/os/editar/<?= $o->idOs ?>" class="btn"><i class="fas fa-edit"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
					$zapnumber = preg_replace("/[^0-9]/", "", $o->telefone);
					$total_os = number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.');
					echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Por WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $o->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $o->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($o->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $o->status . '*.%0d%0a%0d%0a' . strip_tags($o->defeito) . '%0d%0a%0d%0a' . strip_tags($o->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($o->observacoes) . '%0d%0a%0d%0aValor%20Total%20R$&#58%20*'. $total_os . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20estes%20dados%20para%20fazer%20Log-in%0d%0aEmail:%20*' . strip_tags($result->email) . '*%0d%0aSenha:%20*' . strip_tags($result->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';} ?>
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir OS" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimir/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica 2" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica2/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a style="margin-right: 1%" target="_new" href="https://www.linkcorreios.com.br/<?= $o->rastreio ?>" class="btn btn-warning tip-top" title="Rastreio Correio"><i class="fas fa-envelope"></i></a>
				<?php endif ?></div>
                				</td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhum Orçamento Concluido.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <!--Fim Orçamentos Concluidos-->
    
    <!--Em Orçamento-->
    <div class="widget_box_Painel">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                <h5>Em Orçamento</h5>
            </div>
            <div class="widget_content">
                <table class="table_p">
                    <thead>
                        <tr>
                            <th>OS N°</th>
                            <th>Data de Entrada</th>
                            <th>Cliente</th>
                            <th>Contato</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordens1 != null) : ?>
                            <?php foreach ($ordens1 as $o) : ?>
                                <tr>
				<td><div align="center"><?= $o->idOs ?></div></td>
				<td><div align="center"><?= date('d/m/Y', strtotime($o->dataInicial)) ?></div></td>
				<td><div align="center"><?= $o->nomeCliente ?></div></td>
				<td><div align="center"><?= $o->telefone ?></div></td>
				<td><div align="center">R$: <?= number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.') ?></div></td>
				<td><div align="center">
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Visualizar OS" class="btn tip-top" href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn"><i class="fas fa-eye"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) : ?><a title="Editar OS" class="btn btn-info tip-top" href="<?= base_url() ?>index.php/os/editar/<?= $o->idOs ?>" class="btn"><i class="fas fa-edit"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
					$zapnumber = preg_replace("/[^0-9]/", "", $o->telefone);
					$total_os = number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.');
					echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Por WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $o->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $o->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($o->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $o->status . '*.%0d%0a%0d%0a' . strip_tags($o->defeito) . '%0d%0a%0d%0a' . strip_tags($o->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($o->observacoes) . '%0d%0a%0d%0aValor%20Total%20R$&#58%20*'. $total_os . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20estes%20dados%20para%20fazer%20Log-in%0d%0aEmail:%20*' . strip_tags($result->email) . '*%0d%0aSenha:%20*' . strip_tags($result->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';} ?>
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir OS" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimir/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica 2" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica2/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a style="margin-right: 1%" target="_new" href="https://www.linkcorreios.com.br/<?= $o->rastreio ?>" class="btn btn-warning tip-top" title="Rastreio Correio"><i class="fas fa-envelope"></i></a>
				<?php endif ?></div>
                				</td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhuma OS em Orçamento.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <!--Fim Em Orçamento-->
    
    <!--Aguardando Peças-->
    <div class="widget_box_Painel">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                <h5>Aguardando Peças</h5>
            </div>
            <div class="widget_content">
                <table class="table_p">
                    <thead>
                        <tr>
                            <th>OS N°</th>
                            <th>Data de Entrada</th>
                            <th>Cliente</th>
                            <th>Contato</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordens5 != null) : ?>
                            <?php foreach ($ordens5 as $o) : ?>
                                <tr>
				<td><div align="center"><?= $o->idOs ?></div></td>
				<td><div align="center"><?= date('d/m/Y', strtotime($o->dataInicial)) ?></div></td>
				<td><div align="center"><?= $o->nomeCliente ?></div></td>
				<td><div align="center"><?= $o->telefone ?></div></td>
				<td><div align="center">R$: <?= number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.') ?></div></td>
				<td><div align="center">
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Visualizar OS" class="btn tip-top" href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn"><i class="fas fa-eye"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) : ?><a title="Editar OS" class="btn btn-info tip-top" href="<?= base_url() ?>index.php/os/editar/<?= $o->idOs ?>" class="btn"><i class="fas fa-edit"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
					$zapnumber = preg_replace("/[^0-9]/", "", $o->telefone);
					$total_os = number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.');
					echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Por WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $o->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $o->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($o->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $o->status . '*.%0d%0a%0d%0a' . strip_tags($o->defeito) . '%0d%0a%0d%0a' . strip_tags($o->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($o->observacoes) . '%0d%0a%0d%0aValor%20Total%20R$&#58%20*'. $total_os . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20estes%20dados%20para%20fazer%20Log-in%0d%0aEmail:%20*' . strip_tags($result->email) . '*%0d%0aSenha:%20*' . strip_tags($result->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';} ?>
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir OS" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimir/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica 2" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica2/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a style="margin-right: 1%" target="_new" href="https://www.linkcorreios.com.br/<?= $o->rastreio ?>" class="btn btn-warning tip-top" title="Rastreio Correio"><i class="fas fa-envelope"></i></a>
				<?php endif ?></div>
                				</td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhuma OS Aguardando Peças.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <!--Fim Aguardando Peças-->
    
    <!--Entregue - A Receber-->
    <div class="widget_box_Painel">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                <h5>Entregue - A Receber</h5>
            </div>
            <div class="widget_content">
                <table class="table_p">
                    <thead>
                        <tr>
                            <th>OS N°</th>
                            <th>Data de Entrada</th>
                            <th>Cliente</th>
                            <th>Contato</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($ordens7 != null) : ?>
                            <?php foreach ($ordens7 as $o) : ?>
                                <tr>
				<td><div align="center"><?= $o->idOs ?></div></td>
				<td><div align="center"><?= date('d/m/Y', strtotime($o->dataInicial)) ?></div></td>
				<td><div align="center"><?= $o->nomeCliente ?></div></td>
				<td><div align="center"><?= $o->telefone ?></div></td>
				<td><div align="center">R$: <?= number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.') ?></div></td>
				<td><div align="center">
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Visualizar OS" class="btn tip-top" href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn"><i class="fas fa-eye"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) : ?><a title="Editar OS" class="btn btn-info tip-top" href="<?= base_url() ?>index.php/os/editar/<?= $o->idOs ?>" class="btn"><i class="fas fa-edit"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
					$zapnumber = preg_replace("/[^0-9]/", "", $o->telefone);
					$total_os = number_format($o->totalProdutos + $o->totalServicos, 2, ',', '.');
					echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Por WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $o->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $o->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($o->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $o->status . '*.%0d%0a%0d%0a' . strip_tags($o->defeito) . '%0d%0a%0d%0a' . strip_tags($o->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($o->observacoes) . '%0d%0a%0d%0aValor%20Total%20R$&#58%20*'. $total_os . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20estes%20dados%20para%20fazer%20Log-in%0d%0aEmail:%20*' . strip_tags($result->email) . '*%0d%0aSenha:%20*' . strip_tags($result->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';} ?>
				<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir OS" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimir/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a title="Imprimir Termica 2" class="btn btn-inverse tip-top" href="<?= base_url() ?>index.php/os/imprimirTermica2/<?= $o->idOs ?>" class="btn"><i class="fas fa-print"></i></a>
				<?php endif ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?><a style="margin-right: 1%" target="_new" href="https://www.linkcorreios.com.br/<?= $o->rastreio ?>" class="btn btn-warning tip-top" title="Rastreio Correio"><i class="fas fa-envelope"></i></a>
				<?php endif ?></div>
                				</td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhuma OS Entregue - A Receber.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <!--Fim Entregue - A Receber-->
    
    <!--Produtos Com Estoque Mínimo-->
    <div class="widget_box_Painel">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-shopping-bag"></i></span>
                <h5>Produtos Com Estoque Mínimo</h5>
            </div>
            <div class="widget_content">
                <table class="table_p">
                    <thead>
                        <tr>
                            <th>Cod. Item</th>
                            <th>Produto</th>
                            <th>Preço de Venda</th>
                            <th>Estoque</th>
                            <th>Estoque Mínimo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($produtos != null) : ?>
                            <?php foreach ($produtos as $p) : ?>
                                <tr>
                                    <td>
                                        <div align="center">
                                          <?= $p->idProdutos ?>
                                    </div></td>
                                    <td><div align="left">
                                      <?= $p->descricao ?>
                                    </div></td>
                                    <td><div align="center">R$:
                                        <?= $p->precoVenda ?>
                                    </div></td>
                                    <td>
                                        <div align="center">
                                          <?= $p->estoque ?>
                                    </div></td>
                                    <td>
                                        <div align="center">
                                          <?= $p->estoqueMinimo ?>
                                    </div></td>
                                    <td>
                                        <div align="center">
                                          <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) : ?>
                                          <a title="Editar Produto" href="<?= base_url() ?>index.php/produtos/editar/<?= $p->idProdutos ?>" class="btn btn-info">
                                            <i class="fas fa-edit"></i></a>
                                          
                                          <a title="Atualizar Estoque" href="#atualizar-estoque" role="button" data-toggle="modal" produto="<?= $p->idProdutos?>" estoque="<?=$p->estoque?>" class="btn btn-primary" ><i class="fas fa-plus-square"></i></a>
                                          <?php endif; ?>
                                    </div></td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhum produto com estoque baixo.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <!--Fim Produtos Com Estoque Mínimo-->
    
    <!-- Agenda -->
    <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-shopping-bag"></i></span>
                <h5>Agenda</h5>
            </div>
            <div class="widget_box_master_os">
                <table class="table table-mapos">

                    <div id='source-calendar'>
                        <form method="post">
                            <select class="span12" name="statusOsGet" id="statusOsGet" value="">
                                <option value="">Todos os Status</option>
                                <option value="Orçamento">Orçamento</option>
								<option value="Orçamento Concluido">Orçamento Concluido</option>
                                <option value="Orçamento Aprovado">Orçamento Aprovado</option>
                                <option value="Em Andamento">Em Andamento</option>
                                <option value="Aguardando Peças">Aguardando Peças</option>
                                <option value="Serviço Concluido">Serviço Concluido</option>
                                <option value="Sem Reparo">Sem Reparo</option>
                                <option value="Não Autorizado">Não Autorizado</option>
                                <option value="Contato sem Sucesso">Contato sem Sucesso</option>
                                <option value="Cancelado">Cancelado</option>
                                <option value="Pronto-Despachar">Pronto-Despachar</option>
                                <option value="Enviado">Enviado</option>
                                <option value="Aguardando Envio">Aguardando Envio</option>
                                <option value="Aguardando Entrega Correio">Aguardando Entrega Correio</option>
                                <option value="Entregue - A Receber">Entregue - A Receber</option>
                                <option value="Garantia">Garantia</option>
                                <option value="Abandonado">Abandonado</option>
                                <option value="Comprado pela Loja">Comprado pela Loja</option>
                                <option value="Entregue - Faturado">Entregue - Faturado</option>
                            </select>
                            <button type="button" class="btn-xs" id="btn-calendar">Pesquisar</button>
                        </form>

                    </div>

                </table>
            </div>
        </div>
    <!-- Fim Agenda -->
    
    <!-- Balanço Mensal do Ano -->
<?php if ($estatisticas_financeiro != null) {
    if ($estatisticas_financeiro->total_receita != null || $estatisticas_financeiro->total_despesa != null || $estatisticas_financeiro->total_receita_pendente != null || $estatisticas_financeiro->total_despesa_pendente != null) {  ?>

        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) : ?>
            <div class="row-fluid" style="margin-top: 0">
                    <div class="span12">
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                                <h5>
                                    Balanço Mensal do Ano:
                                </h5>
                                <form method="get">
                                    <input type="number" name="year" style="height: 1.1rem; margin-bottom: 0; margin-top: 0.2rem" value="<?php echo intval(preg_replace('/[^0-9]/', '', $this->input->get('year'))) ?: date('Y') ?>">
                                    <button type="submit" class="btn-xs" style="height: 1.8rem; margin-bottom: 0; margin-top: 0.2rem">Pesquisar</button>
                                </form>
                            </div>
                            <div class="widget_box_master_os">
                                <div class="row-fluid">
                                    <div class="span12">
                                        <div id="chart-vendas-mes1" style=""></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <script src="<?= base_url('assets/js/highchart/highcharts.js') ?>"></script>

            <script type="text/javascript">
                $(function() {
                    var myChart = Highcharts.chart('chart-vendas-mes1', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Financeiro'
                        },
                        xAxis: {
                            categories: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
                        },
                        yAxis: {
                            title: {
                                text: 'Reais',
                                format: 'R$: {value}'
                            }
                        },
                        tooltip: {
                            valueDecimals: 2,
                            valuePrefix: 'R$: '
                        },
                        plotOptions: {
                            series: {
                                dataLabels: {
                                    enabled: true,
                                    format: 'R$: {y}',
                                }
                            }
                        },
                        credits: {
                            enabled: false
                        },
                        series: [{
                                name: 'Receita Líquida',
                                data: [
                                    [<?php echo ($financeiro_mes->VALOR_JAN_REC - $financeiro_mes->VALOR_JAN_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_FEV_REC - $financeiro_mes->VALOR_FEV_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_MAR_REC - $financeiro_mes->VALOR_MAR_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_ABR_REC - $financeiro_mes->VALOR_ABR_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_MAI_REC - $financeiro_mes->VALOR_MAI_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_JUN_REC - $financeiro_mes->VALOR_JUN_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_JUL_REC - $financeiro_mes->VALOR_JUL_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_AGO_REC - $financeiro_mes->VALOR_AGO_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_SET_REC - $financeiro_mes->VALOR_SET_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_OUT_REC - $financeiro_mes->VALOR_OUT_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_NOV_REC - $financeiro_mes->VALOR_NOV_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_DEZ_REC - $financeiro_mes->VALOR_DEZ_DES); ?>]
                                ]
                            },
                            {
                                name: 'Receita Bruta',
                                data: [
                                    [<?php echo ($financeiro_mes->VALOR_JAN_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_FEV_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_MAR_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_ABR_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_MAI_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_JUN_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_JUL_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_AGO_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_SET_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_OUT_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_NOV_REC); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_DEZ_REC); ?>]
                                ]
                            },
                            {
                                name: 'Despesas',
                                data: [
                                    [<?php echo ($financeiro_mes->VALOR_JAN_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_FEV_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_MAR_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_ABR_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_MAI_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_JUN_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_JUL_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_AGO_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_SET_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_OUT_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_NOV_DES); ?>],
                                    [<?php echo ($financeiro_mes->VALOR_DEZ_DES); ?>]
                                ]
                            }
                        ]
                    });
                });
            </script>
        <?php endif ?>
<?php  }
} ?>
    <!--Fim Balanço Mensal do Ano -->

</div>

<?php if ($estatisticas_financeiro != null) {
    if ($estatisticas_financeiro->total_receita != null || $estatisticas_financeiro->total_despesa != null || $estatisticas_financeiro->total_receita_pendente != null || $estatisticas_financeiro->total_despesa_pendente != null) {  ?>

        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) : ?>
        
            <div class="row-fluid" style="margin-top: 0">
            	<div class="span4">
                	<div class="widget-box">
                        <div class="widget-title"><span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
                            <h5>Estatísticas financeiras - Realizado</h5>
                        </div>
                        <div class="widget_estatisticas widget_box_Painel2">
                            <div class="row-fluid">
                                    <div id="chart-financeiro" style="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="span4">
                	<div class="widget-box">
                        <div class="widget-title"><span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
                            <h5>Estatísticas financeiras - Pendente</h5>
                        </div>
                        <div class="widget_estatisticas widget_box_Painel2">
                            <div class="row-fluid">
                                    <div id="chart-financeiro2" style="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="span4">
                	 <div class="widget-box">
                        <div class="widget-title"><span class="icon"><i class="fas fa-cash-register"></i></span>
                            <h5>Total em caixa / Previsto</h5>
                        </div>
                        <div class="widget_estatisticas widget_box_Painel2">
                            <div class="row-fluid">
                                    <div id="chart-financeiro-caixa" style="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif ?>

<?php  }
} ?>

<div class="row-fluid" style="margin-top: 0">
<!-- Estatísticas de OS -->
<?php if ($os != null && $this->permission->checkPermission($this->session->userdata('permissao'), 'rOs')) { ?>
    <div class="row-fluid" style="margin-top: 0">

        <div class="span12">

            <div class="widget-box">
                <div class="widget-title"><span class="icon"><i class="fas fa-diagnoses"></i></span>
                    <h5>Estatísticas de OS</h5>
                </div>
                <div class="widget_estatisticas widget_box_Painel2">
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
<!-- Fim Estatísticas de OS -->

<!-- Estatísticas do Sistema -->
<div class="row-fluid" style="margin-top: 0">

    <div class="span12">

        <div class="widget-box">
            <div class="widget-title"><span class="icon"><i class="fas fa-signal"></i></span>
                <h5>Estatísticas do Sistema</h5>
            </div>
            <div class="widget_estatisticas widget_box_Painel2">
                <div class="row-fluid">
                    <div class="span12">
                        <ul class="site-stats">
                            <li class="bg_lh"><i class="fas fa-users"></i> <strong>
                                    <?= $this->db->count_all('clientes'); ?></strong> <small>Clientes</small></li>
                            <li class="bg_lh"><i class="fas fa-shopping-bag"></i> <strong>
                                    <?= $this->db->count_all('produtos'); ?></strong> <small>Produtos </small></li>
                            <li class="bg_lh"><i class="fas fa-diagnoses"></i> <strong>
                                    <?= $this->db->count_all('os'); ?></strong> <small>Ordens de Serviço</small></li>
                            <li class="bg_lh"><i class="fas fa-wrench"></i> <strong>
                                    <?= $this->db->count_all('servicos'); ?></strong> <small>Serviços</small></li>

                        </ul>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fim Estatísticas do Sistema -->
</div>

<?php if ($os != null && $this->permission->checkPermission($this->session->userdata('permissao'), 'rOs')) { ?>
    <script type="text/javascript">
        $(document).ready(function() {
            var data = [
                <?php foreach ($os as $o) {
                    echo "['" . $o->status . "', " . $o->total . "],";
                } ?>

            ];
            var plot1 = jQuery.jqplot('chart-os', [data], {
                seriesDefaults: {
                    // Make this a pie chart.
                    renderer: jQuery.jqplot.PieRenderer,
                    rendererOptions: {
                        // Put data labels on the pie slices.
                        // By default, labels show the percentage of the slice.
                        showDataLabels: true
                    }
                },
                legend: {
                    show: true,
                    location: 'e'
                }
            });

        });
    </script>

<?php
} ?>



<?php if (isset($estatisticas_financeiro) && $estatisticas_financeiro != null && $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
    if ($estatisticas_financeiro->total_receita != null || $estatisticas_financeiro->total_despesa != null || $estatisticas_financeiro->total_receita_pendente != null || $estatisticas_financeiro->total_despesa_pendente != null) {
?>
        <script type="text/javascript">
            $(document).ready(function() {

                var data2 = [
                    ['Total Receitas', <?php echo ($estatisticas_financeiro->total_receita != null) ?  $estatisticas_financeiro->total_receita : '0.00'; ?>],
                    ['Total Despesas', <?php echo ($estatisticas_financeiro->total_despesa != null) ?  $estatisticas_financeiro->total_despesa : '0.00'; ?>]
                ];
                var plot2 = jQuery.jqplot('chart-financeiro', [data2], {

                    seriesColors: ["#9ACD32", "#FF8C00", "#EAA228", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"],
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
                    legend: {
                        show: true,
                        location: 'e'
                    }
                });


                var data3 = [
                    ['Total Receitas', <?php echo ($estatisticas_financeiro->total_receita_pendente != null) ?  $estatisticas_financeiro->total_receita_pendente : '0.00'; ?>],
                    ['Total Despesas', <?php echo ($estatisticas_financeiro->total_despesa_pendente != null) ?  $estatisticas_financeiro->total_despesa_pendente : '0.00'; ?>]
                ];
                var plot3 = jQuery.jqplot('chart-financeiro2', [data3], {

                        seriesColors: ["#90EE90", "#FF0000", "#EAA228", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"],
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
                        legend: {
                            show: true,
                            location: 'e'
                        }
                    }

                );


                var data4 = [
                    ['Total em Caixa', <?php echo ($estatisticas_financeiro->total_receita - $estatisticas_financeiro->total_despesa); ?>],
                    ['Total a Entrar', <?php echo ($estatisticas_financeiro->total_receita_pendente - $estatisticas_financeiro->total_despesa_pendente); ?>]
                ];
                var plot4 = jQuery.jqplot('chart-financeiro-caixa', [data4], {

                        seriesColors: ["#839557", "#d8b83f", "#d8b83f", "#ff5800", "#0085cc"],
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
                        legend: {
                            show: true,
                            location: 'e'
                        }
                    }

                );


            });
        </script>

<?php
    }
} ?>

<!-- Modal Status OS Calendar -->
<div id="calendarModal" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal_header_anexos">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Status OS Detalhada</h3>
    </div>
    <div class="modal-body">
        <div class="span12" id="divFormStatusOS" style="margin-left: 0"></div>
        <h4><b>OS:</b> <span id="modalId" class="modal-id"></span></h4>
        <h5 id="modalCliente" class="modal-cliente"></h5>
        <div id="modalDataInicial" class="modal-DataInicial"></div>
        <div id="modalDataFinal" class="modal-DataFinal"></div>
        <div id="modalGarantia" class="modal-Garantia"></div>
        <div id="modalStatus" class="modal-Status"></div>
        <div id="modalDescription" class="modal-Description"></div>
        <div id="modalDefeito" class="modal-Defeito"></div>
        <div id="modalObservacoes" class="modal-Observacoes"></div>
        <div id="modalTotal" class="modal-Total"></div>
        <div id="modalValorFaturado" class="modal-ValorFaturado"></div>
    </div>
    <div class="modal-footer">
        <?php
        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            echo '<a id="modalIdVisualizar" style="margin-right: 1%" href="" class="btn tip-top" title="Ver mais detalhes"><i class="fas fa-eye"></i></a>';
        }
        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            echo '<a id="modalIdEditar" style="margin-right: 1%" href="" class="btn btn-info tip-top" title="Editar OS"><i class="fas fa-edit"></i></a>';
        }
        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dOs')) {
            echo '<a id="linkExcluir" href="#modal-excluir-os" role="button" data-toggle="modal" os="" class="btn btn-danger tip-top" title="Excluir OS"><i class="fas fa-trash-alt"></i></a>  ';
        }
        ?>
    </div>
</div>

<!-- Modal Excluir Os -->
<div id="modal-excluir-os" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/os/excluir" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Excluir OS</h3>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modalIdExcluir" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta OS?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>

<!-- Modal Estoque -->
<div id="atualizar-estoque" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/produtos/atualizar_estoque" method="post" id="formEstoque">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel"><i class="fas fa-plus-square"></i> Atualizar Estoque</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label for="estoqueAtual" class="control-label">Estoque Atual</label>
                <div class="controls">
                    <input id="estoqueAtual" type="text" name="estoqueAtual" value="" readonly />
                </div>
            </div>

            <div class="control-group">
                <label for="estoque" class="control-label">Adicionar Produtos<span class="required">*</span></label>
                <div class="controls">
                    <input type="hidden" id="idProduto" class="idProduto" name="id" value="" />
                    <input id="estoque" type="text" name="estoque" value="" />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-primary">Atualizar</button>
        </div>
    </form>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<!-- Modal Estoque-->
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var produto = $(this).attr('produto');
            var estoque = $(this).attr('estoque');
            $('.idProduto').val(produto);
            $('#estoqueAtual').val(estoque);
        });

        $('#formEstoque').validate({
            rules: {
                estoque: {
                    required: true,
                    number: true
                }
            },
            messages: {
                estoque: {
                    required: 'Campo Requerido.',
                    number: 'Informe um número válido.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });

        var srcCalendarEl = document.getElementById('source-calendar');
        var srcCalendar = new FullCalendar.Calendar(srcCalendarEl, {
            locale: 'pt-br',
            height: 500,
            editable: false,
            selectable: false,
            businessHours: true,
            dayMaxEvents: true, // allow "more" link when too many events
            displayEventTime: false,
            events: {
                url: "<?= base_url() . "index.php/mapos/calendario"; ?>",
                method: 'GET',
                extraParams: function() { // a function that returns an object
                    return {
                        status: $("#statusOsGet").val(),
                    };
                },
                failure: function() {
                    alert('Falha ao buscar OS de calendário!');
                },
            },
            eventClick: function(info) {
                var eventObj = info.event.extendedProps;
                $('#modalId').html(eventObj.id);
                $('#modalIdVisualizar').attr("href", "<?php echo base_url(); ?>index.php/os/visualizar/" + eventObj.id);
                if (eventObj.editar) {
                    $('#modalIdEditar').show();
                    $('#linkExcluir').show();
                    $('#modalIdEditar').attr("href", "<?php echo base_url(); ?>index.php/os/editar/" + eventObj.id);
                    $('#modalIdExcluir').val(eventObj.id);
                } else {
                    $('#modalIdEditar').hide();
                    $('#linkExcluir').hide();
                }
                $('#modalCliente').html(eventObj.cliente);
                $('#modalDataInicial').html(eventObj.dataInicial);
                $('#modalDataFinal').html(eventObj.dataFinal);
                $('#modalGarantia').html(eventObj.garantia);
                $('#modalStatus').html(eventObj.status);
                $('#modalDescription').html(eventObj.description);
                $('#modalDefeito').html(eventObj.defeito);
                $('#modalObservacoes').html(eventObj.observacoes);
                $('#modalTotal').html(eventObj.total);
                $('#modalValorFaturado').html(eventObj.valorFaturado);

                $('#eventUrl').attr('href', event.url);
                $('#calendarModal').modal();
            },
        });

        srcCalendar.render();

        $('#btn-calendar').on('click', function() {
            srcCalendar.refetchEvents();
        });
    });
</script>
