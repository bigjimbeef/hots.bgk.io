<?php

include_once("simple_html_dom.php");
include_once("utils.php");

$html = file_get_html("http://www.heroesnexus.com/heroes");

$characters = file_get_contents("characters");

$charArray = explode("\n", rtrim($characters));

foreach($html->find("a.hero-champion") as $heroLink) {

	$heroName = html_entity_decode($heroLink->innertext, ENT_QUOTES, 'UTF-8');

	if ( !in_array($heroName, $charArray) ) {

		echo "Missing $heroName from the list!\n";

		// Get blizz image.
		$blizzName = getBlizzName($heroName);
		$imgName = $blizzName . ".jpg";

		$url = "http://eu.battle.net/heroes/static/images/heroes/busts/";
		$url.= $imgName;

		exec("wget $url");
		exec("mv $imgName images/busts/$heroName.jpg");

		// Now get all information for this hero.
		//exec("php newChar.php $heroName");
	}
}

?>