
$(document).ready(function(){
	// url_ordem_abertas = $(url_item).data('url');
	function tables_config(table_finder, url_item, tipo_os){

		$(table_finder).dataTable({
			"bJQueryUI": true,
			"order": [[0, "DESC"]],
			"language": {
		 		"decimal":        ",",
		    "emptyTable":     "Sem registros.",
		    "info":           "Mostrando _START_ para _END_ de _TOTAL_ registros",
		    "infoEmpty":      "Mostrando 0 para 0 de 0 registros",
		    "infoFiltered":   "(filtered from _MAX_ total entries)",
		    "infoPostFix":    "",
		    "thousands":      ".",
		    "lengthMenu":     "Mostrar  _MENU_  registros",
		    "loadingRecords": "Carregando...",
		    "processing":     "Processando...",
		    "search":         "Pesquisar: ",
		    "zeroRecords":    "Nenhum registro encontrado",
		    "paginate": {
		        "first":      "Primeiro",
		        "last":       "Último",
		        "next":       "Próximo",
		        "previous":   "Anterior"
	  		},
		    "aria": {
		        "sortAscending":  ": ativa para ordenar por coluna ascendente",
		        "sortDescending": ": ativa para ordenar por coluna descendente"
		    },
			},	
			"processing": true,
			"serverSide": true,
	    ajax: {
	        url: url_item,
	        data: {"status_os": tipo_os},
	        "type": "POST",
	        dataSrc: 'data'
	    },
	    columns: [
	        { data: "idOs" },
	        { data: "dataInicial" },
	        { data: "dataFinal" },
	        { data: "nomeCliente" },
	        { data: "botao" }
	    ],
		  "sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>'
		});	
	}
	tables_config('table.os-abertas',$('table.os-abertas').data('url'), "Aberto");
	tables_config('table.os-orcamento',$('table.os-orcamento').data('url'), "Orçamento");
	// tables_config($('.os-orcamento').data('url'));

	
	$('input[type=checkbox],input[type=radio],input[type=file]').uniform();
	
	// $('select').select2();
	
	$("span.icon input:checkbox, th input:checkbox").click(function() {
		var checkedStatus = this.checked;
		var checkbox = $(this).parents('.widget-box').find('tr td:first-child input:checkbox');		
		checkbox.each(function() {
			this.checked = checkedStatus;
			if (checkedStatus == this.checked) {
				$(this).closest('.checker > span').removeClass('checked');
			}
			if (this.checked) {
				$(this).closest('.checker > span').addClass('checked');
			}
		});
	});	
});
