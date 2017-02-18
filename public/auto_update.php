<?php

include_once("simple_html_dom.php");
include_once("utils.php");

chdir("/var/www/hotsbuilds.info/public");

$html = file_get_html("http://www.heroesfire.com/hots/wiki/heroes");

$charArray = getCharacterList();

foreach($html->find(".card-wrap a div") as $heroText) {

	$heroName = html_entity_decode($heroText->innertext, ENT_QUOTES, 'UTF-8');
	$heroName = replaceAccents($heroName);

	if ( !in_array($heroName, $charArray) ) {

		$heroName = addslashes($heroName);

		printWithDate("Missing $heroName from the list!");

		// Get blizz image.
		$blizzName = getBlizzName($heroName);
		$imgName = $blizzName . ".jpg";

		$url = "http://eu.battle.net/heroes/static/images/heroes/busts/";
		$url.= $imgName;

		exec("wget $url");
		exec("mv $imgName images/busts/$heroName.jpg");

		// Now get all information for this hero.
		exec("/usr/bin/php newChar.php $heroName");

		exec("/usr/bin/php postpro.php");
	}
}

?>
