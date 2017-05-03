<?php

include_once("simple_html_dom.php");
include_once("utils.php");

$html = file_get_html("http://www.heroesfire.com/hots/wiki/heroes");

$charArray = getCharacterList();

$updated = false;
$filePath = dirname(__FILE__);

foreach($html->find(".card-wrap a div") as $heroText) {

	$heroName = html_entity_decode($heroText->innertext, ENT_QUOTES, 'UTF-8');
	$heroName = replaceAccents($heroName);

	if ( !in_array($heroName, $charArray) ) {

		$heroName = addslashes($heroName);

		printWithDate("Missing $heroName from the list!");

		// Get blizz image.
		$blizzName = getBlizzName($heroName);

		// Check if we should use the new way.
		system("wget -q --spider http://media.blizzard.com/heroes/$blizzName/bust.jpg", $newHotness);

		if ( $newHotness != 0 ) {

			$imgName = $blizzName . ".jpg";

			$url = "http://eu.battle.net/heroes/static/images/heroes/busts/";
			$url.= $imgName;
		}
		else {

			$url = "http://media.blizzard.com/heroes/$blizzName/bust.jpg";
		}

		printWithDate("Getting image from $url...");
		system("wget -q $url -O $filePath/images/busts/$heroName.jpg");

		// Now get all information for this hero.
		printWithDate("Getting info for $heroName...");

		system("/usr/bin/php $filePath/newChar.php $heroName");

		$updated = true;
	}
}

exit();

if ($updated) {

	// Populate the new talents, and images, for the new guy.
	system("/usr/bin/php $filePath/talents.php --images");

	// Get the video path for the new guy.
	system("/usr/bin/php $filePath/videos.php");
}

?>
