jQuery(document).ready(function( $ ){

	"use strict";
	
	var ajax_request = function( formData, action ){
		$.ajax({
			type 	: 'POST', 
			dataType: 'json', 
			url 	: SETTINGS_AJAX.ajaxurl,
			data 	: {
				action: action,
				data: formData,
			},
			success: function(response){
				if ( response.success === true ){
					var parsed  = $.parseJSON(response.data);
					var counter = parsed[0]; 
					if (counter == 0){
						var string  = "Defaults Restored"; 
						document.getElementById(formData.dialogue).innerHTML = string;
						document.getElementById(formData.dialogue).setAttribute("style","font-weight:900;color:#444444;");
					 	var destroy = document.getElementById(formData._destroy);
						destroy.classList.remove("pagoda-db-destroy-input-final");
						// Update settings fields to reflect defaults
						document.getElementById("pagoda-settings-1").value = "4";
						document.getElementById("pagoda-settings-2").value = "4";
						document.getElementById("pagoda-settings-3").value = "5";
					}
					if (1 <= counter && counter <= 3){
						var string  = "Click "+(5-counter)+" more times ...";
						document.getElementById(formData.dialogue).innerHTML = string;
						document.getElementById(formData.dialogue).setAttribute("style","font-weight:900;color:#444444;");
					}
					if (counter == 4){
						var string  = "Are you sure?";
						document.getElementById(formData.dialogue).innerHTML = string;
						document.getElementById(formData.dialogue).setAttribute("style","font-weight:900;color:#ff0000;");
						var destroy = document.getElementById(formData._destroy);
						destroy.classList.add("pagoda-db-destroy-input-final");
						
					}
				}
				else { 				
			        alert( response.data );
				}	
			}  // Close success
		}); // Close ajax request
	} // Close function

    $(document).on('click','.pagoda-db-destroy',function(e){
       
        e.preventDefault();
        var dialogue = $(this).closest('div').find("#pagoda-db-destroy-p");
        var formData = {
        	'_destroy' : this.id,
			'dialogue' : dialogue.attr('id'), 			
		}
		//alert(formData.dialogue);
		ajax_request( formData, 'pagoda_db_destroy');
    });

}); // Close Script 