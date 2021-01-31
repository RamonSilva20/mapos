$('#form-gerar-cobranca').submit(function(e) {
    e.preventDefault();

    $("#modal-gerar-pagamento").modal('hide');

    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(),
        beforeSend: function() {
            swal({
                title: 'Processando',
                text: 'Criando cobrança...',
                icon: 'info',
                showCloseButton: false,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        },
        success: function(response) {
            swal("Sucesso!", "Cobrança criada com sucesso!", "success");
            setTimeout(function() {
                window.location.href = window.BaseUrl + 'index.php/cobrancas/visualizar/' + response.idCobranca;
            }, 5000);
        },
        error: function (response) {
            var message = response.responseJSON.message || "Erro ao criar cobrança!";
            swal("Erro!", message, "error");
        }
    });
});

$("#gateway_de_pagamento").change(function (e) {
    var gatewayDePagamento = $(this).val();
    $("#forma_pagamento").hide();
    $("#label_forma_pagamento").hide();
    $('#forma_pagamento').empty();

    if (!gatewayDePagamento) {
        return;
    }

    $('#forma_pagamento').append(new Option('Escolha a forma de pagamento', ''));
    for (var i = 0; i < paymentGatewaysConfig[gatewayDePagamento]['payment_methods'].length; i++) {
        var option = new Option(
            paymentGatewaysConfig[gatewayDePagamento]['payment_methods'][i]['name'],
            paymentGatewaysConfig[gatewayDePagamento]['payment_methods'][i]['value']
        );
        $('#forma_pagamento').append(option);
    }
    $("#forma_pagamento").show();
    $("#label_forma_pagamento").show();
})
