<?php
	include_once("simple_html_dom.php");
	include_once("constants.php");
	include_once("query.php");
	include_once("utils.php");

	function urlOk($url) {
	    $headers = @get_headers($url);

	    if ( $headers[0] == 'HTTP/1.1 200 OK' ) {
	    	return true;
	    }
	    else {
	    	return false;
	    }
	}

	function getSingleHotsLogsCharacterData($character, &$urls)
	{
		$charCache 	= $character;

		// Ensure the character name is capitalised, because HL needs that for some reason.
		$character 	= ucwords($character);
		$character 	= rawurlencode($character);
		$url		= "http://www.hotslogs.com/Sitewide/HeroDetails?Hero=$character";

		$urls[$charCache] = $url;

		$talents = array();

		$html = file_get_html($url);

		$table 	= $html->find("table", 2);
		$row 	= $table->find("tr.rgRow", 0);

		if(!is_object($row))
		    return $talents;

		$count = 0;
		foreach($row->find("td") as $td)
		{
			// skip first two tds
			++$count;
			if ($count <= 2)
				continue;

			$img = $td->find("img", 0);

			if (is_object($img))
			{
				$decoded = html_entity_decode($img->title, ENT_QUOTES);

				$matches = null;
				preg_match("/([\w\s'!,\.-]+:?[\w\s!',-]+):(.*)/", $decoded, $matches);

				if ( !empty($matches) )
				{
					$talentName = $matches[1];
					$talents[] = $talentName;
				}
			}
			else
			{
				$talentName = "Player's Choice";
				$talents[] = $talentName;
			}
			
			if (count($talents) >= MAX_TALENTS)
			{
				break;
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

		$html = file_get_html("https://www.heroesfire.com/hots/guides");

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
			return $talents;
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

		// Get the PHP simple DOM node for the selected article.
		$article		= $guideHTML->find("article.selected .skills img");

		// Check if this guide is for the correct character.
		$articleH2		= $guideHTML->find("article.selected h2", 0);
		$name			= trim(strip_tags($articleH2->innertext));

		$isCorrect		= strcasecmp($name, $character) == 0;

		if ( !$isCorrect ) {

			printWithDate("WARNING: Wanted $character and got $name...");

			foreach ( $guideHTML->find("article") as $singleArticle ) {

				$h2 = $singleArticle->find("h2", 0);
				$testName = trim(strip_tags($h2->innertext));

				if ( strcasecmp($testName, $character) == 0 ) {

					printWithDate("Found matching article, using this instead.");

					$article 	= $singleArticle->find(".skills img");
					$isCorrect 	= true;
					break;
				}
			}
		}

		if ( !$isCorrect ) {
			error_log("Unable to find guide for $character from HeroesFire!\n");
			return $talents;
		}

		foreach( $article as $val ) {

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

	$hlTalents	= array();
	$hfTalents	= array();
	$hlUrls 	= array();
	$gbUrls 	= array();
	$hfUrls 	= array();
	$ivUrls		= array();

	function addSingleCharacterTalents($characterName, &$targetArray, &$urls, $targetSite)
	{
		$entry = array();
		// Add the character name...
		$entry["hero"] = $characterName;

		// ... and the character talent data ...
		$singleChar = null;
		switch ( $targetSite )
		{
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

			default:
				break;
		}

		// ... to a single array.
		if ( !empty($singleChar) && count($singleChar) == MAX_TALENTS ) {
			$entry["talents"] = $singleChar;			
		
			$targetArray[] = $entry;
		}
	}

	$characterList = getCharacterList();

	foreach($characterList as $characterName) {

		printWithDate("Getting HL information for $characterName...");
		addSingleCharacterTalents($characterName, $hlTalents, $hlUrls, ETalentSite::HotsLogs);

		printWithDate("Getting HF information for $characterName...");
		addSingleCharacterTalents($characterName, $hfTalents, $hfUrls, ETalentSite::HeroesFire);
	}

	truncateTable(ETable::HotsLogs);
	populateTalentTable($hlTalents, ETable::HotsLogs);

	truncateTable(ETable::HeroesFire);
	populateTalentTable($hfTalents, ETable::HeroesFire);

	truncateTable(ETable::Time);
	populateTime();

	truncateTable(ETable::Urls);
	populateUrls($hlUrls, $gbUrls, $hfUrls, $ivUrls, $characterList);

	// Fix up the data.
	//system("/usr/bin/php postpro.php");

?>
