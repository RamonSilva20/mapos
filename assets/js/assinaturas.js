
document.addEventListener('DOMContentLoaded', function() {
    var assCliente = document.getElementById('assCliente-pad');
    if(assCliente) {
        var assClientePad = new SignaturePad(assCliente);
    }

    var assTecnico = document.getElementById('assTecnico-pad');
    if(assTecnico) {
        var assTecnicoPad = new SignaturePad(assTecnico);
    }

    var limparAssCliente = document.getElementById('limparAssCliente');
    var limparAssTecnico = document.getElementById('limparAssTecnico');
    var salvarAss        = document.getElementById('salvarAss');
    var saveSigTecButton = document.getElementById('salvarAssTecnico');
    var saveSigCliButton = document.getElementById('salvarAssCliente');
    var adicionarAss     = document.getElementById('adicionarAss');

    if(limparAssCliente) {
        limparAssCliente.addEventListener('click', function() {
            assClientePad.clear();
        });
    }
    if(limparAssTecnico) {
        limparAssTecnico.addEventListener('click', function() {
            assTecnicoPad.clear();
        });
    }

    if(salvarAss) {
        salvarAss.addEventListener('click', function() {
            if (assClientePad.isEmpty() || assTecnicoPad.isEmpty()) {
                var assFaltante = assClientePad.isEmpty() ? 'Cliente' : 'Técnico';
                Swal.fire({
                    icon: "error",
                    title: "Erro",
                    text: "Falta a assinatura do " + assFaltante +"!"
                });
            } else {
                var form_data = new FormData();
                form_data.append('idOs', idOs);

                if(assCliente) {
                    var assClienteImg64 = assClientePad.toDataURL();
                    form_data.append('assClienteImg', assClienteImg64);
                    form_data.append('inserirAssCli', 1);
                }
                if(assTecnico) {
                    var assTecnicoImg64 = assTecnicoPad.toDataURL();
                    form_data.append('assTecnicoImg', assTecnicoImg64);
                    form_data.append('inserirAssTec', 1);
                }
                
                post_form(form_data, assClienteImg64, assTecnicoImg64);
            }
        });
    }

    if(saveSigCliButton) {
        saveSigCliButton.addEventListener('click', function() {
            if (assClientePad.isEmpty()) {
                Swal.fire({
                    icon: "error",
                    title: "Erro",
                    text: "O Cliente precisa assinar!"
                });
            } else {
                var form_data = new FormData();
                form_data.append('idOs', idOs);
                
                var assClienteImg64 = assClientePad.toDataURL();
                form_data.append('assClienteImg', assClienteImg64);
                form_data.append('inserirAssCli', 1);
                
                post_form(form_data, assClienteImg64);
            }
        });
    }

    if(saveSigTecButton) {
        saveSigTecButton.addEventListener('click', function() {
            if (assTecnicoPad.isEmpty()) {
                Swal.fire({
                    icon: "error",
                    title: "Erro",
                    text: "O Técnico precisa assinar!"
                });
            } else {
                var form_data = new FormData();
                var assTecnicoImg64 = assTecnicoPad.toDataURL();
                form_data.append('idOs', idOs);
                form_data.append('assTecnicoImg', assTecnicoImg64);
                form_data.append('inserirAssTec', 1);
                
                post_form(form_data, '', assTecnicoImg64);
            }
        });
    }

    if(adicionarAss) {
        adicionarAss.addEventListener('click', function() {
            var form_data = new FormData();
            form_data.append('idOs', idOs);
            form_data.append('inserirAssTec', 1);

            post_form(form_data);
        });
    }

    function post_form(form_data, assClienteImg64 = '', assTecnicoImg64 = '')
    {
        $.ajax({
            url: base_url+'index.php/Assinatura/upload_signature',
            type: 'POST',
            cache: false,
            data : form_data,
            contentType: false,
            processData: false,
            success: function(response){
                if(response.success == true) {
                    if(response.assClienteImg) {
                        assCliente.remove();
                        $('#assinaturaCliente').prepend('<img src="'+assClienteImg64+'" width="600" alt="">')
                            .append('<p>Em '+response.assClienteData+'</p>').append('<p>IP: '+response.assClienteIp+'</p>');
                        $('button').remove('#limparAssCliente, #salvarAss, #salvarAssCliente');
                        $("#divAnotacoes").load(base_url+'index.php/os/editar/'+form_data.get('idOs')+' #divAnotacoes');
                    }
                    if(response.assTecnicoImg) {
                        assTecnico && assTecnico.remove();
                        assTecnicoImg = assTecnicoImg64 || response.assTecnicoImg;
                        $('#tituloAssTec').length ? '' : $('#assinaturaTecnico').append('<h4>Assinatura do Técnico</h4>');
                        $('#assinaturaTecnico').prepend('<img src="'+assTecnicoImg+'" width="600" alt="">')
                            .append('<p>Em '+response.assTecnicoData+'</p>').append('<p>IP: '+response.assTecnicoIp+'</p>');
                        $('button').remove('#limparAssTecnico, #salvarAss, #salvarAssTecnico, #adicionarAss');
                        $("#divAnotacoes").load(base_url+'index.php/os/editar/'+form_data.get('idOs')+' #divAnotacoes');
                    }
                    Swal.fire({
                        icon: "success",
                        title: "Sucesso",
                        text: response.message,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Atenção",
                        text: response.message || "Ocorreu um erro ao tentar salvar a assinatura"
                    });
                }
            },
            error: function (request, status, error) {
                 console.log(JSON.stringify(request));
                 console.log(JSON.stringify(status));
                 console.log(JSON.stringify(error));
            }
        })
        .fail(function() {
            console.log(JSON.stringify(error))
            Swal.fire({
                icon: "error",
                title: "Atenção",
                text: "Ocorreu um erro ao enviar sua assinatura"
            });
        });
    }
});