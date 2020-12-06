$('form#form-gerar-pagamento-wirecard').submit(function (e) {
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
                $("#myModalBoleto").modal('show');

                var dataN = dadosBoletos.fundingInstrument.boleto.expirationDate.split("-");
                var moedaN = "R$ " + (parseFloat(dadosBoletos.amount.total) / 100).toFixed(2).replace(".",",");

                //"code":200,"data":{"barcode":"03399.32766 55400.000000 60348.101027 6 69020000009000","link":"https:\/\/visualizacaosandbox.gerencianet.com.br\/emissao\/59808_79_FORAA2\/A4XB-59808-60348-HIMA4","expire_at":"2016-08-30","charge_id":76777,"status":"waiting","total":9000,"payment":"banking_billet"
                var html = "<tr><th>Nº da Transação Order: </th><td>" + dadosBoletos._links.order.title + "</td></tr>"
                html += "<tr><th>Nº da Transação Payment: </th><td>" + dadosBoletos.id + "</td></tr>"
                html += "<tr><th>Vencimento: </th><td>" + dataN[2] + "/" + dataN[1] + "/" + dataN[0] + "</td></tr>"
                html += "<tr><th>Status:  </th><td>" + dadosBoletos.status + "</td></tr>"
                html += "<tr><th>Total: </th><td>" + moedaN + "</td></tr>"
                html += "<tr><th>Método de pagamento: <td>" + dadosBoletos.fundingInstrument.method + "</td></tr>";
                html += "<tr><th>Código de Barras: </th><td>" + dadosBoletos.fundingInstrument.boleto.lineCode + "</td></tr>"
                html += "<tr><th>Link do Boleto: </th><td><a href=" + dadosBoletos._links.payBoleto.printHref + " target='_blank'>Clique aqui para abrir boleto</a></td></tr>"
                $("#result_table").html(html);
                //document.getElementById("dadosPagamento").innerHTML = response;
                //window.focus(); //manter focus na janela anterior e não na nova janela.
                //$('#msg').html(response).fadeIn('slow');

            }
        });
        
    } else {
        $('#msgError').addClass("alert alert-danger").html("Precisa de conexão com a internet para gerar pagamento!").fadeIn('slow'); //also show a success message 
        $('#msgError').delay(5000).fadeOut('slow');
    }
});

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

                var dataN = dadosBoletos.data.expire_at.split("-");
                var moedaN = "R$ " + (parseFloat(dadosBoletos.data.total) / 100).toFixed(2).replace(".",",");


                $("#myModalBoleto").modal('show');
                //"code":200,"data":{"barcode":"03399.32766 55400.000000 60348.101027 6 69020000009000","link":"https:\/\/visualizacaosandbox.gerencianet.com.br\/emissao\/59808_79_FORAA2\/A4XB-59808-60348-HIMA4","expire_at":"2016-08-30","charge_id":76777,"status":"waiting","total":9000,"payment":"banking_billet"
                var html = "<tr><th>Nº da Transação: </th><td>" + dadosBoletos.data.charge_id + "</td></tr>"
                html += "<tr><th>Vencimento: </th><td>" + dataN[2] + "/" + dataN[1] + "/" + dataN[0] + "</td></tr>"
                html += "<tr><th>Status:  </th><td>" + dadosBoletos.data.status + "</td></tr>"
                html += "<tr><th>Total: </th><td>" + moedaN + "</td></tr>"
                html += "<tr><th>Método de pagamento: <td>" + dadosBoletos.data.payment + "</td></tr>";
                html += "<tr><th>Código de Barras: </th><td>" + dadosBoletos.data.barcode + "</td></tr>"
                html += "<tr><th>Link do Boleto: </th><td><a href=" + dadosBoletos.data.link + " target='_blank'>Clique aqui para abrir boleto</a></td></tr>"
                $("#result_table").html(html);
                //document.getElementById("dadosPagamento").innerHTML = response;
                //window.focus(); //manter focus na janela anterior e não na nova janela.
                //$('#msg').html(response).fadeIn('slow');


            }
        });
        
    } else {
        $("#myModal").modal('hide');
        $('#msgError').addClass("alert alert-danger").html("Precisa de conexão com a internet para gerar pagamento!").fadeIn('slow'); //also show a success message 
        $('#msgError').delay(5000).fadeOut('slow');
    }
});