/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH                                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2016 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

$(document).ready(function(){
	/* The following code is executed once the DOM is loaded */
	
	/* This flag will prevent multiple comment submits: */
	var working = false;
	
	/* Listening for the submit event of the form: */
	$('.jak-ajaxform').submit(function(e){

 		e.preventDefault();
		if(working) return false;
		
		working = true;
		var jakform = $(this);
		var button = $(this).find('.jak-submit');
		$(this).find('.form-group').removeClass("has-error");
		$(this).find('.form-group').removeClass("has-success");
		
		$(button).html(jakWeb.jak_submitwait);
		
		// Now this is ajax
		var data = $(this).serializeArray(); // convert form to array
		data.push({name: "jakajax", value: "yes"});
		
		/* Sending the form fields to any post request: */
		$.post(jakWeb.jak_url+jakWeb.jakrequest_uri, $.param(data), function(msg) {
			
			working = false;
			$(button).html(jakWeb.jak_submit);
			
			if(msg.status) {
			
				$(jakform).find('.jak-thankyou').fadeIn(1000).html('<div class="alert alert-success">'+msg.html+'</div>');
				$(jakform)[0].reset();
				
				// Fade out the form
				$(button).fadeOut().delay('500');
				
				
			} else if(msg.login) {
				
				window.location.replace(msg.link);
				
			} else {
				/*
				/	If there were errors, loop through the
				/	msg.errors object and display them on the page 
				/*/
				
				$.each(msg.errors,function(k,v) {
					$(jakform).find('label[for='+k+']').closest(".form-group").addClass("has-error");
				});
			}
		}, 'json');

	});
	
});