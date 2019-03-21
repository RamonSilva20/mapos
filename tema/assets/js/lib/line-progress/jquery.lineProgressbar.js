/**
 * jQuery Line Progressbar
 * Author: KingRayhan<rayhan095@gmail.com>
 * Author URL: http://rayhan.info
 * Version: 1.0.0
 */

(function($){
	'use strict';

	$.fn.LineProgressbar = function(options){

		var options = $.extend({
			percentage : null,
			ShowProgressCount: true,
			duration: 1000,

			// Styling Options
			fillBackgroundColor: '#3498db',
			backgroundColor: '#eef1f6',
			radius: '0px',
			height: '5px',
			width: '100%'
		},options);

		return this.each(function(index, el) {
			// Markup
			$(el).html('<div class="progressbar"><div class="proggress"></div><div class="percentCount"></div></div>');
			


			var progressFill = $(el).find('.proggress');
			var progressBar= $(el).find('.progressbar');


			progressFill.css({
				backgroundColor : options.fillBackgroundColor,
				height : options.height,
				borderRadius: options.radius
			});
			progressBar.css({
				width : options.width,
				backgroundColor : options.backgroundColor,
				borderRadius: options.radius
			});

			// Progressing
			progressFill.animate(
				{
					width: options.percentage + "%"
				},
				{	
					step: function(x) {
						if(options.ShowProgressCount){
							$(el).find(".percentCount").text(Math.round(x) + "%");
						}
					},
					duration: options.duration
				}
			);
		////////////////////////////////////////////////////////////////////
		});
	}
})(jQuery);