jQuery(function($){  

  "use strict";

	// For responsive menu toggle on hambuger icon click
	$(document).ready(function(){
    	$(".pagoda-navbar-after").click(function(){
    	    $(".menu").toggle();
    	});	
	});

	// Scroll to top button and sticky nav
	$(document).scroll(function() {
    
    if ($(this).scrollTop() > 0) {
      // Sticky nav
      $(".pagoda-navbar").addClass("pagoda-navbar-scroll");

      // Scroll to top icon selectors
      if( $("#pagoda-scroll-mobile-post").length && 
          $("#pagoda-scroll-mobile-post").css('display') == 'inline'){$(".pagoda-scroll-button").fadeIn();}
      
      else if( $("#pagoda-scroll-mobile-page").length && 
          $("#pagoda-scroll-mobile-page").css('display') == 'inline'){$(".pagoda-scroll-button").fadeIn();}

      else if( $("#pagoda-scroll-mobile-home").length && 
          $("#pagoda-scroll-mobile-home").css('display') == 'inline'){$(".pagoda-scroll-button").fadeIn();}  

    }
    else {
        // Sticky nav
        $(".pagoda-navbar").removeClass("pagoda-navbar-scroll");
        
        // Scroll to top icon selectors
        if( $("#pagoda-scroll-mobile-post").length && 
            $("#pagoda-scroll-mobile-post").css('display') == 'inline') {$(".pagoda-scroll-button").fadeOut();}

        else if( $("#pagoda-scroll-mobile-page").length && 
            $("#pagoda-scroll-mobile-page").css('display') == 'inline') {$(".pagoda-scroll-button").fadeOut();}

        else if( $("#pagoda-scroll-mobile-home").length && 
            $("#pagoda-scroll-mobile-home").css('display') == 'inline') {$(".pagoda-scroll-button").fadeOut();}  
    }    
	});
	$(".pagoda-scroll-button").click(function() {
		$("html, body").animate({ scrollTop: 0 }, "slow");
  		return false;
	});
  
});