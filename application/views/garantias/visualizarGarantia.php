<?php $totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-book"></i>
                </span>
                <h5>Termo de Garantia</h5>
                <div class="buttons">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eGarantia')) {
                        echo '<a title="Editar Termo de Garantia" class="button btn btn-mini btn-success" href="' . esc_url(base_url() . 'index.php/garantias/editar/' . rawurlencode((string) $result->idGarantias)) . '">
    <span class="button__icon"><i class="bx bx-edit"></i> </span> <span class="button__text">Editar</span></a>';
                    } ?>
                    <a target="_blank" title="Imprimir" class="button btn btn-mini btn-inverse" href="<?= site_url() ?>/garantias/imprimir/<?= esc($result->idGarantias) ?>">
                      <span class="button__icon"><i class="bx bx-printer"></i></span> <span class="button__text">Imprimir</span></a>
                </div>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head">
                        <table class="table">
                            <tbody>
                                <?php if ($emitente == null) { ?>
                                    <tr>
                                        <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?= base_url() ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr> <?php
                                } else { ?> <tr>
                                        <td style="width: 25%"><img src="<?= esc_img_src($emitente->url_logo) ?>"></td>
                                        <td> <span style="font-size: 20px; ">
                                                <?= esc($emitente->nome) ?></span> </br><span>
                                                <?= esc($emitente->cnpj) ?> </br>
                                                <?= esc($emitente->rua) . ', nº:' . esc($emitente->numero) . ', ' . esc($emitente->bairro) . ' - ' . esc($emitente->cidade) . ' - ' . esc($emitente->uf) ?> </span> </br> <span> E-mail:
                                                <?= esc($emitente->email) . ' - Fone: ' . esc($emitente->telefone) ?></span></td>
                                        <td style="width: 18%; text-align: center">#Garantia: <span>
                                                <?= esc($result->idGarantias) ?></span></br> </br> <span>Emissão:
                                                <?= date('d/m/Y') ?></span>
                                        </td>
                                    </tr>
                                <?php
                                } ?>
                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 40%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Responsável</h5>
                                                </span>
                                                <span>
                                                    <?= esc($result->nome) ?></span> <br />
                                                <span>Telefone:
                                                    <?= esc($result->telefone) ?></span><br />
                                                <span>Email:
                                                    <?= esc($result->email) ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 30%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Data</h5>
                                                </span>
                                                <span> <?= date('d/m/Y', strtotime($result->dataGarantia)) ?></span> <br />

                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 30%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Ref. Termo</h5>
                                                </span>
                                                <span><?= esc($result->refGarantia) ?> </span>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 100%; padding-left: 0">
                                        <ul>
                                            <li>

                                                <span>
                                                    <h5>Texto da Garantia</h5>
                                                </span><br />
                                                <span><?= printSafeHtml($result->textoGarantia) ?></span><br />
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
