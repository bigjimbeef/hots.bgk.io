$(document).ready(function(){

	// GetBonkd
	//
	(function() {
		$('#getbonkd li').each(function() {
			var text = $(this).text();

			text = getTalentName(text);

			$(this).html(text);
		});
	})();

	function getTalentName(input) {

		var talentRegex = /<strong>([\w+|\d+|\s+|=|"|#]*)<\/strong>/;

		var matches = input.match(talentRegex);

		return matches.length > 0 ? matches[1] : "";
	}

	function removeLinkTags(input) {

		var anchorOpenRegex 	= /\<a [\w+|\d+|\s+|=|"|#]*\>/g;
		var anchorCloseRegex 	= /\<\/a\>/g;

		input = input.replace(anchorOpenRegex, '');
		input = input.replace(anchorCloseRegex, '');

		return input;
	}

	/*
	$('#getbonkd img').mouseover(function() {

		var desc = $(this).attr('description');

		// Remove links.
		desc = removeLinkTags(desc);

		$('#getbonkd div.description').html(desc);

		$('#getbonkd img').removeClass('highlighted');
		$(this).addClass('highlighted');
	});
	$($('#getbonkd img').get(0)).trigger('mouseover');
	*/
	//

	// HotSlogs
	//
	function emphasiseName(input) {

		var nameRegex = /^([\w|\s|\!|\?|-|'|,|-|_]+): /;
		input = input.replace(nameRegex, "<strong>" + input.match(nameRegex)[1] + "</strong><br />")

		return input;
	}

	/*
	$('#hotslogs img').mouseover(function() {

		var desc = $(this).attr('title');

		desc = emphasiseName(desc);

		$('#hotslogs div.description').html(desc);

		$('#hotslogs img').removeClass('highlighted');
		$(this).addClass('highlighted');
	});
	$($('#hotslogs img').get(0)).trigger('mouseover');
	//
	*/

	// Search
	//
	$('#char-name').keyup(function(e) {
		if ( e.which == 13 ) {
			var value = $(this).val();

			window.location.href = value;
		}
	});
	//
});