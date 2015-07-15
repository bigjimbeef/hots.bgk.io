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


	$( 'img' ).tooltip({
		position: {
			my: "left top",
			at:	"right+5px top"
		},
		content: function() {
			var element = $(this);

			var content = $(this).attr('title');
			if ( !content ) {
				content = "No tooltip found.";
			}

			content = content.replace(/([\d]+)/g, "<span class='number'>$1</span>");

			return "<div>" + content + "</div>";
		},
		show: false,
		hide: false
	});

	function removeHovered() {
		$('img').removeClass('hovered');
	}

	$('img').hover(
		function() {
			removeHovered();

			$(this).addClass('hovered');
		}, 
		function() {

			removeHovered();
		}
	);
	//
});