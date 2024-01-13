
document.addEventListener('DOMContentLoaded', function() {
    var assCliente = document.getElementById('assCliente-pad');
    if(assCliente) {
        var assClientePad = new SignaturePad(assCliente);
    }

    var assTecnico = document.getElementById('assTecnico-pad');
    if(assTecnico) {
        var assTecnicoPad = new SignaturePad(assTecnico);
    }

    var assUsuario = document.getElementById('assUsuario-pad');
    if(assUsuario) {
        var assUsuarioPad = new SignaturePad(assUsuario);
    }

    var limparAssCliente  = document.getElementById('limparAssCliente');
    var limparAssTecnico  = document.getElementById('limparAssTecnico');
    var limparAssUsuario  = document.getElementById('limparAssUsuario');
    var salvarAss         = document.getElementById('salvarAss');
    var saveSigTecButton  = document.getElementById('salvarAssTecnico');
    var saveSigCliButton  = document.getElementById('salvarAssCliente');
    var saveSigUsuButton  = document.getElementById('salvarAssUsuario');
    var adicionarAss      = document.getElementById('adicionarAss');
    var adicionarAss      = document.getElementById('adicionarAss');

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
    if(limparAssUsuario) {
        limparAssUsuario.addEventListener('click', function() {
            assUsuarioPad.clear();
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
                    var nomeAssinatura  = $('#nomeAssinatura').val();
                    if(!nomeAssinatura) {
                        Swal.fire({
                            icon: "error",
                            title: "Erro",
                            text: "Informe o nome de quem está assinando!",
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#nomeAssinatura').focus();
                        return;
                    }

                    var assClienteImg64 = assClientePad.toDataURL();
                    form_data.append('assClienteImg', assClienteImg64);
                    form_data.append('inserirAssCli', 1);
                    form_data.append('nomeAssinatura', nomeAssinatura);
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
                var nomeAssinatura = $('#nomeAssinatura').val();
                if(!nomeAssinatura) {
                    Swal.fire({
                        icon: "error",
                        title: "Erro",
                        text: "Informe o nome de quem está assinando!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#nomeAssinatura').focus();
                    return;
                }

                var form_data = new FormData();
                form_data.append('idOs', idOs);
                
                var assClienteImg64 = assClientePad.toDataURL();
                form_data.append('assClienteImg', assClienteImg64);
                form_data.append('inserirAssCli', 1);
                form_data.append('nomeAssinatura', nomeAssinatura);
                
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

    if(saveSigUsuButton) {
        saveSigUsuButton.addEventListener('click', function() {
            if (assUsuarioPad.isEmpty()) {
                Swal.fire({
                    icon: "error",
                    title: "Erro",
                    text: "O Usuário precisa assinar!"
                });
            } else {
                var form_data = new FormData();
                var assUsuarioImg64 = assUsuarioPad.toDataURL();
                form_data.append('idUsuario', saveSigUsuButton.dataset.idUsuario);
                form_data.append('assUsuarioImg', assUsuarioImg64);
                
                post_form(form_data, '', '', assUsuarioImg64);
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

    function post_form(form_data, assClienteImg64 = '', assTecnicoImg64 = '', assUsuarioImg64 = '')
    {
        $.ajax({
            url: base_url+'Assinatura/upload_signature',
            type: 'POST',
            cache: false,
            data : form_data,
            contentType: false,
            processData: false,
            success: function(response){
                if(response.success == true) {
                    if(response.assTecnicoImg) {
                        assTecnico && assTecnico.remove();
                        assTecnicoImg = assTecnicoImg64 || response.assTecnicoImg;
                        $('#tituloAssTec').length ? '' : $('#assinaturaTecnico').append('<h4>Assinatura do Técnico</h4>');
                        $('#assinaturaTecnico').prepend('<img src="'+assTecnicoImg+'" width="600" alt="">')
                            .append('<p>Em '+response.assTecnicoData+'</p>').append('<p>IP: '+response.assTecnicoIp+'</p>');
                        $('button').remove('#limparAssTecnico, #salvarAss, #salvarAssTecnico, #adicionarAss');
                        $('#salvarAssCliente').removeClass('hide');
                        $("#divAnotacoes").load(base_url+'os/editar/'+form_data.get('idOs')+' #divAnotacoes');
                    }
                    if(response.assClienteImg) {
                        assCliente.remove();
                        $('#assinaturaCliente').prepend('<img src="'+assClienteImg64+'" class="img-fluid" style="max-width:600px;" alt="">')
                            .append('<p>Em '+response.assClienteData+'</p>').append('<p>IP: '+response.assClienteIp+'</p>');
                        $('button').remove('#limparAssCliente, #salvarAss, #salvarAssCliente');
                        $("#divAnotacoes").load(base_url+'os/editar/'+form_data.get('idOs')+' #divAnotacoes');
                        $('#nomeAssinatura').remove();
                    }
                    if(response.code == 200) {
                        assUsuario.remove();
                        $('#assinaturaUsuario').prepend('<img src="'+assUsuarioImg64+'" class="img-fluid" style="max-width:600px;" alt="">');
                        $('#botoesAssinatura').html('<a href="#modal-excluir" role="button" data-toggle="modal" idUsuario="'+form_data.get('idUsuario')+'" class="button btn btn-danger" title="Excluir Assinatura"><span class="button__icon"><i class="far fa-trash-alt"></i></span><span class="button__text2">Excluir assinatura</span></a>');
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