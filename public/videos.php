<?php
	include_once("simple_html_dom.php");
	include_once("constants.php");
	include_once("query.php");

	$videos = array();
	$characterList = getCharacterList();

	foreach($characterList as $characterName) {

		$character = (string)$characterName;
		$matchQuery = "SELECT * FROM hots_bgk_io." . ETable::Videos . " AS t WHERE t.hero=";
		$matchQuery .= "\"" . addslashes($character) . "\"";

		$result = queryDB($matchQuery);
		$output = mysql_fetch_assoc($result["res"]);

		if ( $output !== false ) {

			continue;
		}

		echo "Made it through with $character...\n";

		$isChogall = $characterName == "Cho" || $characterName == "Gall";

		if ( $characterName == "Li Li" ) {
			$character = "lili";
		}
		else if ( $isChogall ) {
			$character = "chogall";
		}
		else {
			$character = getBlizzName($characterName);
		}

		$url = "http://us.battle.net/heroes/en/heroes/$character/";

		$html = file_get_html($url);

		$video = $html->find("div.header-video-overlay div", 0);

		$targetAttr = $video->{"data-ng-class"};
		$targetAttr = htmlspecialchars_decode($targetAttr);

		preg_match("/currentSkin.slug == '([a-zA-Z_-]+)'/", $targetAttr, $matches);

		print_r($matches);

		if ( !empty($matches) )
		{
			if ( !$isChogall ) {

				// Greymane special casing.
				if ( $characterName == "Greymane" ) {
					$character = "greymane-human";
				}
				// Valeera special casing.
				else if ( $characterName == "Valeera" ) {
					$character = "valeera-standard";
				}
				// Varian special casing.
				else if ( $characterName == "Varian" ) {
					$character = "varian-warrior";
				}

				$videoUrl = "http://media.blizzard.com/heroes/$character/skins/videos/" . $matches[1] . ".webm";
			}
			else {

				$lowerCharName = strtolower($characterName);
				$videoUrl = "http://media.blizzard.com/heroes/videos/heroes/skins/" . $lowerCharName . "_" . $matches[1] . ".webm";

				// More fixup for Cho'Gall!
				if ( $characterName == "Gall" ) {
					$videoUrl = preg_replace("/Cho/", "Gall", $videoUrl);
				}
			}

			echo "Adding video path for $characterName: $videoUrl\n";
			$videos[$characterName] = $videoUrl;
		}
	}

	if ( !empty($videos) ) {

		populateVideos($videos);
	}

?>