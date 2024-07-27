<!DOCTYPE html>
<html lang="pr_BR">
<!--
sarkozin
https://github.com/sarKozin 
-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>assets/img/favicon.png" />
    <title>Checkout Map-OS</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/checkout.css">
</head>

<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <img src="<?php echo $emitente->url_logo; ?>" alt="Logo">
            <h1 class="h4 mb-0"><?php echo $emitente->nome ?></h1>
        </div>
        <div class="checkout-body">
            <div class="row mb-3">
                <div class="col-8 text-left">
                    <p><strong>Cliente: </strong><?php echo $result->nomeCliente; ?></p>
                </div>
                <div class="col-4 text-right">
                    <span><strong>Subtotal:</strong> R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span><br>
                    <?php if ($desconto > 0): ?>
                        <span><strong>Desconto:</strong> <?php echo $desconto; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <?php if (empty($comprovante)): ?>
                        <h2 class="h5">Pagamento Pix</h2>
                        <div class="qr-code mb-3">
                            <img src="data:image/png;base64,<?php echo $pix_qrcode_base64; ?>" alt="QR Code Pix">
                            <p><strong>Total:</strong> R$ <?php echo number_format($valorFinal, 2, ',', '.'); ?></p>
                        </div>
                        <div class="alert alert-success copy-success" role="alert">
                            Código copiado com sucesso!
                        </div>
                        <div class="copy-code mb-3">
                            <textarea class="form-control" readonly><?php echo $pix_payload; ?></textarea>
                        </div>
                        <button class="btn btn-success btn-copy mb-2" onclick="copyToClipboard()">Copiar código</button>

                        <button type="button" class="btn btn-info btn-copy" data-toggle="modal"
                            data-target="#uploadModal">Enviar comprovante</button>

                        <div class="alert alert-success upload-success" role="alert">
                            Comprovante enviado com sucesso!
                        </div>
                        <div class="alert alert-danger upload-error" role="alert" style="display: none;">
                            Ocorreu um erro ao enviar o comprovante.
                        </div>
                    <?php else:
                        $verified = $comprovante[0]->verified;
                        if ($verified < 1): ?>
                            <div class="alert alert-warning" role="alert">
                                <span>Recebemos seu comprovante e estamos confirmando seu pagamento, esse processo leva no
                                    maximo 1 hora</span>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" role="alert">
                                O pagamento já foi verificado, seu serviço já está em andamento
                                <a href="<?php echo base_url('/index.php/payment/details/' . $result->payment_url); ?>">Clique
                                    aqui</a>
                                para mais detalhes.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="checkout-footer text-center mt-3">
            <p class="text-muted">&copy; 2024 <?php echo $emitente->nome; ?> - Todos os direitos reservados</p>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Anexar Comprovante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="file" name="comprovante" id="comprovante" class="form-control mb-2" required>
                        <input type="hidden" name="payment_url" id="payment_url" class="form-control mb-2"
                            value="<?= $result->payment_url ?>">
                        <button type="submit" class="btn btn-info btn-copy">Enviar comprovante</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>

    <script>
        var paymentUrl = document.getElementById("payment_url").value;
        function copyToClipboard() {
            var copyText = document.querySelector(".copy-code textarea");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");

            var successMessage = document.querySelector(".copy-success");
            successMessage.style.display = "block";

            setTimeout(function () {
                successMessage.style.display = "none";
            }, 2000);
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        $(document).ready(function () {
            $('#uploadForm').on('submit', function (e) {
                e.preventDefault();

                var formData = new FormData(this);
                var csrfToken = getCookie('MAPOS_CSRF_COOKIE');
                formData.append('MAPOS_CSRF_TOKEN', csrfToken);
                formData.append('payment_url', paymentUrl);

                var submitButton = $(this).find('button[type="submit"]');
                submitButton.prop('disabled', true);

                $.ajax({
                    url: '<?php echo site_url('payment/upload_comprovante/' . $result->idOs); ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('#uploadModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso',
                                text: response.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message
                            });
                        }
                        submitButton.prop('disabled', false);
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Ocorreu um erro ao enviar o comprovante. Por favor, tente novamente.'
                        });
                        submitButton.prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>
