<?php
	include_once("simple_html_dom.php");
	include_once("constants.php");
	include_once("query.php");

	function getSingleGetBonkdCharacterData($character, &$urls)
	{
		$charCache 	= $character;

		// Remove punctuation and replace spaces with dashes.
		$character 	= preg_replace("/['|\.]/", "", $character);
		$character 	= preg_replace("/ /", "-", $character);
		$url		= "http://getbonkd.com/guides/$character/";

		$urls[$charCache] = $url;

		$html = file_get_html($url);

		$talents = array();

		$count = 0;
		foreach($html->find("#toptalents span.tooltips") as $singleTalent) 
		{
			$text 	= $singleTalent->title;
			$img 	= $singleTalent->find("img", 0);

			$useImgBackup = false;

			// Fall back to scraping the image name.
			if ( empty($text) ) {

				$nameFromImg = preg_match("/^.*\/([\w_]+)/", $img->src, $matches);
				if ( !empty($matches) ) {

					$imgName 	= $matches[1];
					$text		= preg_replace("/_/", " ", $imgName);

					$useImgBackup = true;
				}
			}

			if ( !empty($text) ) {

				$testText = htmlspecialchars_decode($text);

				$matches = null;
				$returnValue = preg_match("/<strong>(.*)<\/strong><br \/> (.*)/", $testText, $matches);

				if ( !empty($matches) ) {

					$talentName = $matches[1];
					$talents[] 	= $talentName;
				}
				else if ( $useImgBackup ) {

					$talents[]	= $text;
				}

				$count++;
				if ( $count >= MAX_TALENTS )
				{
					break;
				}
			}
		}

		return $talents;
	}

	function getSingleHotsLogsCharacterData($character, &$urls)
	{
		$charCache 	= $character;

		// Ensure the character name is capitalised, because HL needs that for some reason.
		$character 	= ucwords($character);
		$character 	= rawurlencode($character);
		$url		= "https://www.hotslogs.com/Sitewide/HeroDetails?Hero=$character";

		$urls[$charCache] = $url;

		$talents = array();

		$html = file_get_html($url);

		$table 	= $html->find("table", 2);
		$row 	= $table->find("tr.rgRow", 0);

		$count = 0;
		foreach($row->find("td img") as $singleTalent)
		{
			$decoded = html_entity_decode($singleTalent->title, ENT_QUOTES);

			$matches = null;
			$returnValue = preg_match("/([\w\s'!,\.]+:?[\w\s!',]+):(.*)/", $decoded, $matches);

			if ( !empty($matches) )
			{
				$talentName = $matches[1];
				$talents[] = $talentName;
			}
		}

		return $talents;
	}

	function doHeroesFireCharFormat($character) {

		if ( $character == "Sgt. Hammer" ) {
			return "sergeant-hammer";
		}
		else if ( $character == "E.T.C." ) {
			return "elite-tauren-chieftain";
		}

		$output = strtolower($character);
		$output = preg_replace("#[[:punct:]]#", "", $output);
		$output = preg_replace("/[\s]/", "-", $output);

		return $output;
	}

	// BLOODY HEROESFIRE JESUS COME ON
	function findCharacterID($character) {

		$html = file_get_html("http://www.heroesfire.com/hots/guides");

		$id = -1;
		foreach ( $html->find(".select-guides .heroes img") as $val ) {

			$src 			= $val->src;
			$charFromSrc 	= preg_match("/heroes\/([\w-]+).png/", $src, $matches);

			if ( empty($matches) ) {
				continue;
			}

			if ( $matches[1] == $character )
			{
				$parent = $val->parent();
				$id 	= $parent->{"data-id"};
				break;
			}
		}

		return $id;
	}


	function getSingleHeroesfireCharacterData($character, &$urls)
	{
		$talents 		= array();

		$charCache		= $character;

		$hfCharacter 	= doHeroesFireCharFormat($character);
		$id 			= findCharacterID($hfCharacter);
		$baseURL		= "http://www.heroesfire.com";
		$ajaxURL		= $baseURL . "/ajax/tooltip?relation_type=WikibaseArticle&relation_id=";

		if ( $id < 0 ) {
			error_log("SOMETHING WENT BADLY WRONG WITH HEROESFIRE.");
		}

		// Build the new URL for the character's list of guides.
		$url			= "$baseURL/hots/guides?s=t&fHeroes=" . $id . "&fMaps=&fCategory=";
		$html 			= file_get_html($url);

		// Build the URL for the top guide.
		$bestGuide		= $html->find(".browse-item-list a", 0);
		$bestGuideURL	= $baseURL . $bestGuide->href;

		$urls[$charCache] = $bestGuideURL;
		
		// Get the top guide's HTML.
		$guideHTML		= file_get_html($bestGuideURL);

		foreach( $guideHTML->find("article.selected .skills img") as $val ) {

			$class 			= $val->class;
			preg_match("/i:'(\d+)'/", $class, $matches);

			if ( !empty($matches) ) {

				$ajaxTooltipID 	= $matches[1];
				$fullAjaxURL	= $ajaxURL . $ajaxTooltipID;

				$skillHTML		= file_get_html($fullAjaxURL);
				$name			= $skillHTML->find("h5", 0)->innertext;
				$talents[]		= $name;
			}
		}

		return $talents;
	}

	$gbTalents 	= array();
	$hlTalents	= array();
	$hfTalents	= array();
/*
	$images		= array();
	$tooltips 	= array();
*/
	$hlUrls 	= array();
	$gbUrls 	= array();
	$hfUrls 	= array();

	function addSingleCharacterTalents($characterName, &$targetArray, &$urls, $targetSite)
	{
		$entry = array();
		// Add the character name...
		$entry["hero"] = $characterName;

		// ... and the character talent data ...
		$singleChar = null;
		switch ( $targetSite )
		{
			default:
			case ETalentSite::GetBonkd:
			{
				$singleChar = getSingleGetBonkdCharacterData($characterName, $urls);
			}
			break;

			case ETalentSite::HotsLogs:
			{
				$singleChar = getSingleHotsLogsCharacterData($characterName, $urls);
			}
			break;

			case ETalentSite::HeroesFire:
			{
				$singleChar = getSingleHeroesfireCharacterData($characterName, $urls);
			}
			break;
		}

		// ... to a single array.
		$entry["talents"] = $singleChar;

		$targetArray[] = $entry;
	}

	foreach($CHARACTERS as $characterName) {

		echo "Getting HL information for $characterName...\n";
		addSingleCharacterTalents($characterName, $hlTalents, $hlUrls, ETalentSite::HotsLogs);
		
		echo "Getting GB information for $characterName...\n";
		addSingleCharacterTalents($characterName, $gbTalents, $gbUrls, ETalentSite::GetBonkd);

		echo "Getting HF information for $characterName...\n";
		addSingleCharacterTalents($characterName, $hfTalents, $hfUrls, ETalentSite::HeroesFire);
	}

	truncateTable(ETable::HotsLogs);
	populateTalentTable($hlTalents, ETable::HotsLogs);

	truncateTable(ETable::GetBonkd);
	populateTalentTable($gbTalents, ETable::GetBonkd);

	truncateTable(ETable::HeroesFire);
	populateTalentTable($hfTalents, ETable::HeroesFire);

	truncateTable(ETable::Time);
	populateTime();

	truncateTable(ETable::Urls);
	populateUrls($hlUrls, $gbUrls, $hfUrls, $CHARACTERS);
