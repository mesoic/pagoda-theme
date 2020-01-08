jQuery(function($){ 

	"use strict";

    $('body').on('click', '.pagoda-admin-widget-toggle-1', function(e){
		$(this).toggleClass('open');
		$('.pagoda-admin-widget-field-1').toggle();
    });
    $('body').on('click', '.pagoda-admin-widget-toggle-2', function(e){
		$(this).toggleClass('open');
		$('.pagoda-admin-widget-field-2').toggle();
    });

});
