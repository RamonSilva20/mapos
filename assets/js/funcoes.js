$(function () {

	$("#rg").mask("00.000.000-0")
	$("#celular").mask("(00)00000-0000")
	$("#telefone").mask("(00)0000-0000")
	$("#cep").mask("00000-000")
});
$(function formatar(mascara, documento) {
	var i = documento.value.length;
	var saida = mascara.substring(0, 1);
	var texto = mascara.substring(i)
	if (texto.substring(0, 1) != saida) {
		documento.value += texto.substring(0, 1);
	}
});

$(function () {
	var options = {
		onKeyPress: function (cpfcnpj, e, field, options) {
			var masks = ['000.000.000-000', '000.000.000/0000-00'];
			var mask = (cpfcnpj.length > 14) ? masks[1] : masks[0];
			$('.cpfcnpj').mask(mask, options);
		}
	};

	$('.cpfcnpj').mask('000.000.000-000', options);

});