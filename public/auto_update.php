<?php

include_once("simple_html_dom.php");
include_once("utils.php");

chdir("/var/www/hotsbuilds.info/public");

$html = file_get_html("http://www.heroesnexus.com/heroes");

$charArray = getCharacterList();

foreach($html->find("a.hero-champion") as $heroLink) {

	$heroName = html_entity_decode($heroLink->innertext, ENT_QUOTES, 'UTF-8');

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
	}
}

?>
