<!--sidebar-menu-->
<nav id="sidebar">
    <div id="newlog">
        <div class="icon2">
            <img src="<?php echo base_url() ?>assets/img/logo-two.png">
        </div>
        <div class="title1">
            <?= $configuration['app_theme'] == 'white' ||  $configuration['app_theme'] == 'whitegreen' ? '<img src="' . base_url() . 'assets/img/logo-mapos.png">' : '<img src="' . base_url() . 'assets/img/logo-mapos-branco.png">'; ?>
        </div>
    </div>
    <a href="#" class="visible-phone">
        <div class="mode">
            <div class="moon-menu">
                <i class='bx bx-chevron-right iconX open-2'></i>
                <i class='bx bx-chevron-left iconX close-2'></i>
            </div>
        </div>
    </a>
    <!-- Start Pesquisar-->
    <li class="search-box">
        <form style="display: flex" action="<?= site_url('mapos/pesquisar') ?>">
        <button style="background:transparent;border:transparent" type="submit" class="tip-bottom" title="">
                <i class='bx bx-search iconX'></i></button>
                <input style="background:transparent;<?= $configuration['app_theme'] == 'white' ? 'color:#313030;' : 'color:#fff;' ?>border:transparent" type="search" name="termo" placeholder="Pesquise aqui...">
            <span class="title-tooltip">Pesquisar</span>
        </form>
    </li>
    <!-- End Pesquisar-->

    <div class="menu-bar">
        <div class="menu">

            <ul class="menu-links" style="position: relative;">
                <li class="<?php if (isset($menuPainel)) {
                    echo 'active';
                }; ?>">
                    <a class="tip-bottom" title="" href="<?= base_url() ?>"><i class='bx bx-home-alt iconX'></i>
                        <span class="title nav-title">Home</span>
                        <span class="title-tooltip">Início</span>
                    </a>
                </li>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) { ?>
                    <li class="<?php if (isset($menuClientes)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('clientes') ?>"><i class='bx bx-user iconX'></i>
                            <span class="title">Cliente / Fornecedor</span>
                            <span class="title-tooltip">Clientes</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) { ?>
                    <li class="<?php if (isset($menuProdutos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('produtos') ?>"><i class='bx bx-basket iconX'></i>
                            <span class="title">Produtos</span>
                            <span class="title-tooltip">Produtos</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) { ?>
                    <li class="<?php if (isset($menuServicos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('servicos') ?>"><i class='bx bx-wrench iconX'></i>
                            <span class="title">Serviços</span>
                            <span class="title-tooltip">Serviços</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) { ?>
                    <li class="<?php if (isset($menuVendas)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('vendas') ?>"><i class='bx bx-cart-alt iconX'></i></span>
                            <span class="title">Vendas</span>
                            <span class="title-tooltip">Vendas</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) { ?>
                    <li class="<?php if (isset($menuOs)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('os') ?>"><i class='bx bx-file iconX'></i>
                            <span class="title">Ordens de Serviço</span>
                            <span class="title-tooltip">Ordens</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) { ?>
                    <li class="<?php if (isset($menuGarantia)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('garantias') ?>"><i class='bx bx-receipt iconX'></i>
                            <span class="title">Termos de Garantias</span>
                            <span class="title-tooltip">Garantias</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vArquivo')) { ?>
                    <li class="<?php if (isset($menuArquivos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('arquivos') ?>"><i class='bx bx-box iconX'></i>
                            <span class="title">Arquivos</span>
                            <span class="title-tooltip">Arquivos</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) { ?>
                    <li class="<?php if (isset($menuLancamentos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('financeiro/lancamentos') ?>"><i class="bx bx-bar-chart-alt-2 iconX"></i>
                            <span class="title">Lançamentos</span>
                            <span class="title-tooltip">Lançamentos</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) { ?>
                    <li class="<?php if (isset($menuCobrancas)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('cobrancas/cobrancas') ?>"><i class='bx bx-dollar-circle iconX'></i>
                            <span class="title">Cobranças</span>
                            <span class="title-tooltip">Cobranças</span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="botton-content">
            <li class="">
                <a class="tip-bottom" title="" href="<?= site_url('login/sair'); ?>">
                    <i class='bx bx-log-out-circle iconX'></i>
                    <span class="title">Sair</span>
                    <span class="title-tooltip">Sair</span>
                </a>
            </li>
        </div>
    </div>
</nav>
<!--End sidebar-menu-->
