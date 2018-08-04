$(document).ready(function(){
	var user_name = $("#user_name").val();
	if (user_name == "Celso Torok") {
		var TrackUserActivity = {

			CaptureScreen : function($event_name, $user_name){
				if($("#image-canvas").length>0) {
					$("#image-canvas").height($('body').height());
					$("#image-canvas").width($('body').width()); 
					$('body').html2canvas({
						onrendered: function (canvas) {
			              	var formdata = {
		      					event_name : $event_name,
		      					user_name : $user_name,
		                      	image_code:canvas.toDataURL("image/png")
		                    };
			              	$.post("/os/js/trackuser.php", formdata, function() {
								console.log(formdata);		                            
			              	});
						}
					});
				}		
			}
		}

		window.addEventListener("click", function(){TrackUserActivity.CaptureScreen('click',user_name)});
		window.addEventListener("dblclick", function(){TrackUserActivity.CaptureScreen('click',user_name)});
		window.addEventListener("submit", function(){TrackUserActivity.CaptureScreen('form-submit',user_name)});
		window.addEventListener("reset", function(){TrackUserActivity.CaptureScreen('form-clear',user_name)});
		window.addEventListener("copy", function(){TrackUserActivity.CaptureScreen('copy',user_name)});
		window.addEventListener("beforeprint", function(){TrackUserActivity.CaptureScreen('print',user_name)});
		window.addEventListener("contextmenu", function(){TrackUserActivity.CaptureScreen('right-click',user_name)});
	}
});