<?php
	include("simple_html_dom.php");
	include("constants.php");
	include("query.php");

	function getBlizzName($character) {

		$character 	= strtolower($character);
		$character 	= preg_replace("/['|\.]/", "", $character);
		$character 	= preg_replace("/ /", "-", $character);

		return $character;
	}

	$videos = array();

	foreach($CHARACTERS as $characterName) {

		$character = $characterName;

		if ( $characterName == "Li Li" ) {
			$character = "lili";
		}
		else {
			$character = getBlizzName($characterName);
		}

		$url = "http://us.battle.net/heroes/en/heroes/$character/";

		$html = file_get_html($url);

		$video = $html->find("div.header-video-overlay div", 0);

		$targetAttr = $video->{"data-ng-class"};
		$targetAttr = htmlspecialchars_decode($targetAttr);

		preg_match("/currentSkin.slug == \"(\w+)\"/", $targetAttr, $matches);

		if ( !empty($matches) )
		{
			$videoUrl = "http://media.blizzard.com/heroes/videos/heroes/skins/" . $character . "_" . $matches[1] . ".webm";

			echo "Adding video path for $characterName...\n";
			$videos[$characterName] = $videoUrl;
		}
	}

	populateVideos($videos);
