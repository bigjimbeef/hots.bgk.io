$(document).ready(function(){

	var search = $('#char-name');

	// Search
	//

	function doSearch() {
		var value = $(search).val();

		window.location.href = value;
	}

	$(search).keyup(function(e) {
		if ( e.which == 13 ) {
			doSearch();
		}
	});

	$(search).focusout(function() {
		$(this).focus();
		
		return false;
	});
	$(search).focus();

	$(search).autocomplete({
		source: characterJson
	});


	$("li.ui-menu-item").click(function() {
		console.log("GO");
		doSearch();
	});
	//
});