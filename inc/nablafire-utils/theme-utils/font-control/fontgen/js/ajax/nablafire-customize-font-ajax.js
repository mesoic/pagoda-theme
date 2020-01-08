jQuery(document).ready(function( $ ){

	'use strict';

	var ajax_request = function( formData, varCtrls, action ){
			
		$.ajax({
			type: 'POST', 
			dataType: 'json', 
			url: NABLAFIRE_CUSTOMIZE_FONT_AJAX.ajaxurl,
			data: {
				action: action,
				data: formData,
			},
			success: function(response){
				if ( response.success === true ){
	
					var variants = $.parseJSON(response.data);

					for (var i = 0; i < varCtrls.length; ++i) {

						varCtrls[i].innerHTML = "";
    					for (var j = 0; j < variants.length; j++){
							var option = document.createElement("option");
							option.text = variants[j];
							if (variants[j] === "regular"){
								option.selected = true;
							}
							varCtrls[i].add(option); 
    					}
    				}
				}
				else { 
			        alert( response.data );
				}	
			}  // Close success
		}); // Close ajax request
	} // Close function

    $(document).on('change','.customize-font-family-control',function(e){
       
        e.preventDefault();

        // Get the control, and find font variant elements
       	var _control = $(this).closest('div');
       	var varCtrls = _control[0].querySelectorAll('.customize-font-variant-control');

       	// Get the link's of these elements. link coresponds 
       	// to the option stored in the DB. When AJAXing, we 
       	// will update the option in the wordpress database.   
       	var var_opts = {}; // An object. Not Array []
       	for (var i = 0; i < varCtrls.length; ++i) {
       		var_opts[i] = varCtrls[i].getAttribute('data-customize-setting-link');
       	}
			
		// Pack form data for AJAX
        var formData = {
			'font_val' : this.value,
			'font_opt' : this.getAttribute('data-customize-setting-link'), 
			'var_opts' : JSON.stringify(var_opts),
		}

		// We only do the ajax request if variant-control has been rendered. This allows 
		// us to create a functional font control with no variant field (e.g. if we want
		// to enqueue ALL variants for a given font for example).  
		if (typeof varCtrls != 'undefined'){
			ajax_request( formData, varCtrls, 'update_font_variants');
		}
    });

}); // Close Script 