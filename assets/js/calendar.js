
$(document).ready(function(){
	
	unicorn.init();
	
	$('#add-event-submit').click(function(){
		unicorn.add_event();
	});
	
	$('#event-name').keypress(function(e){
		if(e.which == 13) {	
			unicorn.add_event();
		}
	});	
});

unicorn = {	
	
	
	init: function() {	

		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();	
		
		$('#fullcalendar').fullCalendar({
			header: {
				left: 'prev,next',
				center: 'title',
				right: 'month,basicWeek,basicDay'
			},
			editable: true,
			droppable: true, 
			drop: function(date, allDay) { 
				

				var originalEventObject = $(this).data('eventObject');
					
				
				var copiedEventObject = $.extend({}, originalEventObject);
					
			
				copiedEventObject.start = date;
				copiedEventObject.allDay = allDay;
					

				$('#fullcalendar').fullCalendar('renderEvent', copiedEventObject, true);
					
				
					$(this).remove();
				
			}
		});
		this.external_events();		
	},
	
	
	add_event: function(){
		if($('#event-name').val() != '') {
			var event_name = $('#event-name').val();
			$('#external-events .panel-content').append('<div class="external-event ui-draggable label label-inverse">'+event_name+'</div>');
			this.external_events();
			$('#modal-add-event').modal('hide');
			$('#event-name').val('');
		} else {
			this.show_error();
		}
	},
	
	
	external_events: function(){
	
		$('#external-events div.external-event').each(function() {		
		
			var eventObject = {
				title: $.trim($(this).text()) 
			};
				
		
			$(this).data('eventObject', eventObject);
				
		
			$(this).draggable({
				zIndex: 999,
				revert: true,      /
				revertDuration: 0  
			});		
		});		
	},
	

	show_error: function(){
		$('#modal-error').remove();
		$('<div style="border-radius: 5px; top: 70px; font-size:14px; left: 50%; margin-left: -70px; position: absolute;width: 140px; background-color: #f00; text-align: center; padding: 5px; color: #ffffff;" id="modal-error">Enter event name!</div>').appendTo('#modal-add-event .modal-body');
		$('#modal-error').delay('1500').fadeOut(700,function() {
			$(this).remove();
		});
	}
	
	
};
