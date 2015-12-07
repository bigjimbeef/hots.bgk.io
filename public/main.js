$(document).ready(function(){

	// Remove the movie if we're on mobile, because it doesn't work.
	if ( $(document).width() < 1000 ) {
		$('#bg-video').remove();
	}

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

	$(search).autocomplete({
		source: characterJson
	});

	$('ul.ui-autocomplete').on("click", "li.ui-menu-item", function() {
		doSearch();
	});
	
	var targetPos = $(document).width() > 500 ? "right+5px top" : "left bottom";

	$( 'img' ).tooltip({
		position: {
			my: "left top",
			at:	targetPos
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

	$('#images').on("click", "img", function() {

		var title = $(this).attr('data-name');

		$('#search input').val(title);
		doSearch();
	});

	$(search).focus();

	// Manage animated background.
	//

	var hideBG = localStorage.getItem("hide-anim-bg") == "true";
	if ( hideBG ) {
		$('#bg-video').hide();

		$('#low-spec').attr('checked', false);
	}

	$('#low-spec').change(function() { 
		var on = $(this)[0].checked; 
		var targetFn = on ? "show" : "hide"; 
	
		$('#bg-video')[targetFn]();

		localStorage.setItem("hide-anim-bg", !on);
	});

	// Manage Icy-veins builds.
	$("input[type='radio']").change(function() {
		
		$('#icyveins tbody').hide();
		$("#" + $(this).attr("value")).show();
	});

	function randomise() {
		$('*').each(function() { $(this).css('color', '#'+(Math.random()*0xFFFFFF<<0).toString(16)); });
	}

	$(window).konami({
		code : [38,38,40,40,37,39,37,39,66,65,13], 
		cheat: function() { 
			setInterval(randomise, 100);
			$('h1').html("PARTY");
		}
	})
	//
});