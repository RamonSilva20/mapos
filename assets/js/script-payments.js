$('#buttonFormaPagamento').click(function () {
    $('#myModalFormaPagamento').modal('show');
});

$('#escolha-pagamento').change(function () {
    var chars = this.value;
    $('#mostra-butao-pagamento-boleto').hide();
    $('#mostra-butao-pagamento-link').hide();

    if (chars === "") {
        return;
    }
    if (chars === "boleto") {
        $('#mostra-butao-pagamento-link').hide();
        $('#mostra-butao-pagamento-boleto').show();
        return;
    }
    if (chars === "link-pagamento") {
        $('#mostra-butao-pagamento-boleto').hide();
        $('#mostra-butao-pagamento-link').show();
        return;
    }
});

$('form#form-gerar-pagamento-gerencianet-boleto').submit(function (e) {
    e.preventDefault();
    $("#myModalFormaPagamento").modal('hide');

    if (online = navigator.onLine) {
        $("#myModal").modal('show');
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (response) {

                $("#myModal").modal('hide');
                const dadosBoletos = JSON.parse(response);
                if (dadosBoletos.code === 200) {


                    var dataN = dadosBoletos.data.expire_at.split("-");
                    var moedaN = "R$ " + (parseFloat(dadosBoletos.data.total) / 100).toFixed(2).replace(".", ",");
                    var boleto = dadosBoletos.data.payment == 'banking_billet' ? 'Boleto' : 'Cartão de Crédito';

                    $("#myModalBoleto").modal('show');

                    var html = "<tr><th>Nº da Transação: </th><td>" + dadosBoletos.data.charge_id + "</td></tr>"
                    html += "<tr><th>Vencimento: </th><td>" + dataN[2] + "/" + dataN[1] + "/" + dataN[0] + "</td></tr>"
                    html += "<tr><th>Status:  </th><td>" + dadosBoletos.data.status + "</td></tr>"
                    html += "<tr><th>Total: </th><td>" + moedaN + "</td></tr>"
                    html += "<tr><th>Método de pagamento: <td>" + boleto + "</td></tr>";
                    html += "<tr><th>Código de Barras: </th><td>" + dadosBoletos.data.barcode + "</td></tr>"
                    html += "<tr><th>Link do Boleto: </th><td><a href=" + dadosBoletos.data.link + " target='_blank'>Clique aqui para visualizar boleto.</a><br />"
                    html += "<a href=" + dadosBoletos.data.pdf.charge + " target='_blank'>Clique aqui para gerar PDF.</a></td></tr>"
                    $("#result_table").html(html);

                } else {
                    $("#myModal").modal('hide');
                    $("#myModalBoleto").modal('show');
                    $("#myModalLabelMsg").html("Erro ao Emitir Boleto");

                    var html = "<tr><th>Codigo: </th><td>" + dadosBoletos.code + "</td></tr>"
                    html += "<tr><th>Erro: </th><td>" + dadosBoletos.error + "</td></tr>"
                    html += "<tr><th>Descrição:  </th><td>" + dadosBoletos.errorDescription + "</td></tr>"
                    $("#result_table").html(html);
                }


            },
            error: function (response) {
                $("#myModal").modal('hide');
                const dadosError = JSON.parse(response);

                $("#myModalBoleto").modal('show');
                $("#myModalLabelMsg").html("Erro ao Emitir Boleto");
                var html = "<tr><th>Codigo: </th><td>" + dadosError.code + "</td></tr>"
                html += "<tr><th>Erro: </th><td>" + dadosError.error + "</td></tr>"
                html += "<tr><th>Descrição:  </th><td>" + dadosError.description + "</td></tr>"
                $("#result_table").html(html);
            }
        });

    } else {
        $("#myModal").modal('hide');
        $('#msgError').addClass("alert alert-danger").html("Precisa de conexão com a internet para gerar pagamento!").fadeIn('slow'); //also show a success message 
        $('#msgError').delay(5000).fadeOut('slow');
    }
});

$('form#form-gerar-pagamento-gerencianet-link').submit(function (e) {
    e.preventDefault();
    $("#myModalFormaPagamento").modal('hide');

    if (online = navigator.onLine) {
        $("#myModal").modal('show');
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (response) {

                $("#myModal").modal('hide');
                const dadosLink = JSON.parse(response);

                if (dadosLink.code === 200) {
                    var dataL = dadosLink.data.expire_at.split("-");
                    var moedaL = "R$ " + (parseFloat(dadosLink.data.total) / 100).toFixed(2).replace(".", ",");

                    $("#myModalLink").modal('show');
                    var html = "<tr><th>Transação Id: </th><td>" + dadosLink.data.charge_id + "</td></tr>"
                    html += "<tr><th>Status: </th><td>" + dadosLink.data.status + "</td></tr>"
                    html += "<tr><th>Metodo de Pagamento: </th><td>" + dadosLink.data.payment_method + "</td></tr>"
                    html += "<tr><th>Valor: </th><td>" + moedaL + "</td></tr>"
                    html += "<tr><th>Vencimento:  </th><td>" + dataL[2] + "/" + dataL[1] + "/" + dataL[0] + "</td></tr>"
                    html += "<tr><th>Link:  </th><td><a href=" + dadosLink.data.payment_url + " target='_blank'>" + dadosLink.data.payment_url + " </a><br /><br />"
                    html += "<a title='Enviar Por WhatsApp' class='btn btn-success' id='enviarWhatsApp' target='_blank' href='https://web.whatsapp.com/send?phone=55" + $("#celular_cliente").val().replace("(", "").replace(")", "") + "&text=Prezado(a)%20*" + $("#nomeCliente").val() + "*.%0d%0a%0d%0aFoi%20gerado%20um%20link%20para%20pagamento%20referente%20a%20sua%20*" + ($("#idOs").val() === undefined ? "Venda:%20"+$("#idVenda").val() : "OS:%20"+$("#idOs").val()) + "*%20no%20valor%20de%20*" + moedaL + "*,%20com%20vencimento%20para%20*" + dataL[2] + "/" + dataL[1] + "/" + dataL[0] + "*.%0d%0aClique%20no%20link%20*" + dadosLink.data.payment_url + "*.%0d%0a%0d%0aFavor%20entrar%20em%20contato%20em%20caso%20de%20duvidas.%0d%0aAtenciosamente,%20'><i class='fab fa-whatsapp'></i>WhatsApp</a></td></tr>";
                    $("#result_table_link").html(html);

                } else {
                    $("#myModal").modal('hide');
                    $("#myModalLink").modal('show');
                    $(".modal-title-msg").html("Erro ao Emitir Link");

                    var html = "<tr><th>Codigo: </th><td>" + dadosLink.code + "</td></tr>"
                    html += "<tr><th>Erro: </th><td>" + dadosLink.error + "</td></tr>"
                    html += "<tr><th>Descrição:  </th><td>" + dadosLink.errorDescription + "</td></tr>"
                    $("#result_table_link").html(html);
                }


            },
            error: function (response) {
                $("#myModal").modal('hide');
                $("#myModalLink").modal('show');
                $(".modal-title-msg").html("Erro ao Emitir Link");
                
                const dadosError = JSON.parse(response);
                var html = "<tr><th>Codigo: </th><td>" + dadosError.code + "</td></tr>"
                html += "<tr><th>Erro: </th><td>" + dadosError.error + "</td></tr>"
                html += "<tr><th>Descrição:  </th><td>" + dadosError.description + "</td></tr>"
                $("#result_table_link").html(html);
            }
        });

    } else {
        $("#myModal").modal('hide');
        $('#msgError').addClass("alert alert-danger").html("Precisa de conexão com a internet para gerar pagamento!").fadeIn('slow'); //also show a success message 
        $('#msgError').delay(5000).fadeOut('slow');
    }
});