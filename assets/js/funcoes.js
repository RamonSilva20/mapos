$(function () {
    $("#celular").mask("(00)00000-0000")
    $("#telefone").mask("(00)0000-0000")
    $("#cep").mask("00000-000")
    $('.cpfUser').mask('000.000.000-00', {reverse: true});

});

$(function () {
    // INICIO FUNÇÃO DE MASCARA CPF/CNPJ
    var cpfMascara = function (val) {
        return val.replace(/\D/g, '').length > 11 ? '00.000.000/0000-00' : '000.000.000-009';
    },
        cpfOptions = {
            onKeyPress: function (val, e, field, options) {
                field.mask(cpfMascara.apply({}, arguments), options);
            }
        };
    $('.cpfcnpj').mask(cpfMascara, cpfOptions);
    // FIM FUNÇÃO DE MASCARA CPF/CNPJ
});

$(document).ready(function () {
    if ($("[name='idClientes']").val()) {
        $("#nomeCliente").focus();
    } else {
        $("#documento").focus();
    }

    function limpa_formulario_cep() {
        // Limpa valores do formulário de cep.
        $("#rua").val("");
        $("#bairro").val("");
        $("#cidade").val("");
        $("#estado").val("");
    }

    function capitalizeFirstLetter(string) {
        if (typeof string === 'undefined'){
            return;
        }

        return string.charAt(0).toUpperCase() + string.slice(1).toLocaleLowerCase();
    }

    function capital_letter(str) {
        if (typeof str === 'undefined'){ return; }
        str = str.toLocaleLowerCase().split(" ");

        for (var i = 0, x = str.length; i < x; i++) {
            str[i] = str[i][0].toUpperCase() + str[i].substr(1);
        }

        return str.join(" ");
    }

    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g,'');

        if(cnpj == '') return false;

        if (cnpj.length != 14) return false;

        // Elimina CNPJs invalidos conhecidos
        if (cnpj == "00000000000000" ||
            cnpj == "11111111111111" ||
            cnpj == "22222222222222" ||
            cnpj == "33333333333333" ||
            cnpj == "44444444444444" ||
            cnpj == "55555555555555" ||
            cnpj == "66666666666666" ||
            cnpj == "77777777777777" ||
            cnpj == "88888888888888" ||
            cnpj == "99999999999999")
            return false;

        // Valida DVs
        tamanho = cnpj.length - 2
        numeros = cnpj.substring(0,tamanho);
        digitos = cnpj.substring(tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
                pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0))
            return false;

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0,tamanho);
        soma = 0;
        pos = tamanho - 7;

        for (i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2)
                    pos = 9;
        }

        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

        if (resultado != digitos.charAt(1))
            return false;

        return true;
    }

    $('#buscar_info_cnpj').on('click', function () {
        //Nova variável "ndocumento" somente com dígitos.
        var ndocumento = $('#documento').val().replace(/\D/g, '');

        if (validarCNPJ(ndocumento)) {
            //Preenche os campos com "..." enquanto consulta webservice.
            $("#nomeCliente").val("...");
            $("#cep").val("...");
            $("#email").val("...");
            $("#numero").val("...");
            $("#complemento").val("...");
            $("#telefone").val("...");

            //Consulta o webservice receitaws.com.br/
            $.ajax({
                url: "https://www.receitaws.com.br/v1/cnpj/" + ndocumento,
                dataType: 'jsonp',
                crossDomain: true,
                contentType: "text/javascript",
                success: function (dados) {
                    if (dados.status == "OK") {
                        //Atualiza os campos com os valores da consulta.
                        $("#nomeCliente").val(capital_letter(dados.nome));
                        $("#cep").val(dados.cep.replace(/\D/g, ''));
                        $("#email").val(dados.email.toLocaleLowerCase());
                        $("#numero").val(dados.numero);
                        $("#complemento").val(capitalizeFirstLetter(dados.complemento));
                        $("#telefone").val(dados.telefone.split("/")[0].replace(/\D/g, ''));

                        // Força uma atualizacao do endereco via cep
                        document.getElementById("cep").focus();
                        document.getElementById("nomeCliente").focus();
                    } //end if.
                    else {
                        //CEP pesquisado não foi encontrado.
                        $("#nomeCliente").val("");
                        $("#cep").val("");
                        $("#email").val("");
                        $("#numero").val("");
                        $("#complemento").val("");
                        $("#telefone").val("");

                        Swal.fire({
                            type: "warning",
                            title: "Atenção",
                            text: "CNPJ não encontrado."
                        });
                    }
                },
                error: function() {
                    ///CEP pesquisado não foi encontrado.
                    $("#nomeCliente").val("");
                    $("#cep").val("");
                    $("#email").val("");
                    $("#numero").val("");
                    $("#complemento").val("");
                    $("#telefone").val("");

                    Swal.fire({
                        type: "warning",
                        title: "Atenção",
                        text: "CNPJ não encontrado."
                    });
                },
                timeout: 2000,
            });
        } else {
            Swal.fire({
                type: "warning",
                title: "Atenção",
                text: "CNPJ inválido!"
            });
        }
    });

    //Quando o campo cep perde o foco.
    $("#cep").blur(function () {

        //Nova variável "cep" somente com dígitos.
        var cep = $(this).val().replace(/\D/g, '');

        //Verifica se campo cep possui valor informado.
        if (cep != "") {

            //Expressão regular para validar o CEP.

            var validacep = /^[0-9]{8}$/;

            //Valida o formato do CEP.

            if (validacep.test(cep)) {

                //Preenche os campos com "..." enquanto consulta webservice.
                $("#rua").val("...");
                $("#bairro").val("...");
                $("#cidade").val("...");
                $("#estado").val("...");

                //Consulta o webservice viacep.com.br/
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {

                    if (!("erro" in dados)) {
                        //Atualiza os campos com os valores da consulta.
                        $("#rua").val(dados.logradouro);
                        $("#bairro").val(dados.bairro);
                        $("#cidade").val(dados.localidade);
                        $("#estado").val(dados.uf);
                    } //end if.
                    else {
                        //CEP pesquisado não foi encontrado.
                        limpa_formulario_cep();
                        Swal.fire({
                            type: "warning",
                            title: "Atenção",
                            text: "CEP não encontrado."
                        });
                    }
                });
            } //end if.
            else {
                //cep é inválido.
                limpa_formulario_cep();
                Swal.fire({
                    type: "error",
                    title: "Atenção",
                    text: "Formato de CEP inválido."
                });
            }
        } //end if.
        else {
            //cep sem valor, limpa formulário.
            limpa_formulario_cep();
        }
    });
});
