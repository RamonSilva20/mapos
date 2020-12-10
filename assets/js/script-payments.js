$('form#form-gerar-pagamento-gerencianet').submit(function (e) {
    e.preventDefault();

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
                if (dadosBoletos.data) {


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
                    html += "<tr><th>Link do Boleto: </th><td><a href=" + dadosBoletos.data.link + " target='_blank'>Clique aqui para abrir boleto</a></td></tr>"
                    $("#result_table").html(html);

                } else {
                    $("#myModal").modal('hide');
                    $("#myModalBoleto").modal('show');
                    $("#myModalLabel").html("Erro ao Emitir Boleto");

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