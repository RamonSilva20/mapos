$(function () {

	$("#celular").mask("(00)00000-0000")
	$("#telefone").mask("(00)0000-0000")
	$("#cep").mask("00000-000")
});

$(function () {
	// INICIO FUNÇÃO DE MASCARA CPF/CNPJ
	var cpfMascara = function (val) {
		return val.replace(/\D/g, '').length > 11 ? '00.000.000/0000-00' : '000.000.000-009';
	 },
	 cpfOptions = {
		onKeyPress: function(val, e, field, options) {
		   field.mask(cpfMascara.apply({}, arguments), options);
		}	
	 };
	 $('.cpfcnpj').mask(cpfMascara, cpfOptions);
	 // FIM FUNÇÃO DE MASCARA CPF/CNPJ
});
