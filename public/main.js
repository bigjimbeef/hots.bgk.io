$(document).ready(function(){

	var search = $('#char-name');

	// Search
	//
	$(search).keyup(function(e) {
		if ( e.which == 13 ) {
			var value = $(this).val();

			window.location.href = value;
		}
	});

	$(search).focusout(function() {
		$(this).focus();
		
		return false;
	});
	$(search).focus();

	//
});