<?php
	include_once("simple_html_dom.php");
	include_once("constants.php");
	include_once("query.php");

	function getSingleGetBonkdCharacterData($character, &$images, &$tooltips, &$urls)
	{
		// Remove punctuation and replace spaces with dashes.
		$character 	= preg_replace("/['|\.]/", "", $character);
		$character 	= preg_replace("/ /", "-", $character);
		$url		= "http://getbonkd.com/guides/$character/";

		$urls[$character] = $url;

		$html = file_get_html($url);

		$talents = array();

		$count = 0;
		foreach($html->find("#toptalents span.tooltips") as $singleTalent) 
		{
			$text 	= $singleTalent->title;

			$img 	= $singleTalent->find("img", 0);
			$img->description = $text;

			$imgSrc = $img->src;

			if ( !empty($text) )
			{
				$testText = htmlspecialchars_decode($text);

				$matches = null;
				$returnValue = preg_match("/<strong>(.*)<\/strong><br \/> (.*)/", $testText, $matches);

				if ( !empty($matches) )
				{
					$talentName = $matches[1];
					$talents[] 	= $talentName;

					if ( !isset($images[$talentName]) ) {
						$images[$talentName] = $imgSrc;
					}

					$tooltips[$talentName] = $matches[2];
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

	function getSingleHotsLogsCharacterData($character, &$images, &$tooltips, &$urls)
	{
		// Ensure the character name is capitalised, because HL needs that for some reason.
		$character 	= ucwords($character);
		$character 	= rawurlencode($character);
		$url		= "https://www.hotslogs.com/Sitewide/HeroDetails?Hero=$character";

		$urls[$character] = $url;

		$talents = array();

		$html = file_get_html($url);

		$table 	= $html->find("table", 2);
		$row 	= $table->find("tr.rgRow", 0);

		$count = 0;
		foreach($row->find("td img") as $singleTalent)
		{
			$matches = null;
			$returnValue = preg_match("/(.*):?:(.*)/", $singleTalent->title, $matches);

			if ( !empty($matches) )
			{
				$talentName = html_entity_decode($matches[1], ENT_QUOTES);
				$talents[] = $talentName;

				$imgSrc = $singleTalent->src;
				if ( !isset($images[$talentName]) ) {
					$images[$talentName] = $imgSrc;
				}

				if ( !isset($tooltips[$talentName]) ) {
					$tooltips[$talentName] = html_entity_decode($matches[2], ENT_QUOTES);
				}
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


	function getSingleHeroesfireCharacterData($character, &$images, &$tooltips, &$urls)
	{
		$talents 		= array();

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

		$urls[$character] = $bestGuideURL;
		
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

				if ( !isset($images[$name]) ) {

					$images[$name] = "//www.heroesfire.com" . $val->src;
				}

				$pieces 		= explode("/h6>", $skillHTML);
				$smallPieces 	= explode("<div style=", $pieces[1]);
				$tooltip		= strip_tags($smallPieces[0]);
				$tooltip		= ltrim(preg_replace('/\s+/', ' ', $tooltip));

				$talents[]		= $name;

				if ( !isset($tooltips[$name]) ) {

					$tooltips[$name]	= $tooltip;
				}
			}
		}

		return $talents;
	}

	$gbTalents 	= array();
	$hlTalents	= array();
	$hfTalents	= array();
	$images		= array();
	$tooltips 	= array();
	$hlUrls 	= array();
	$gbUrls 	= array();
	$hfUrls 	= array();

	function addSingleCharacterTalents($characterName, &$targetArray, &$images, &$tooltips, &$urls, $targetSite)
	{
		$entry = array();
		// Add the character name...
		$entry[] = $characterName;

		// ... and the character talent data ...
		$singleChar = null;
		switch ( $targetSite )
		{
			default:
			case ETalentSite::GetBonkd:
			{
				$singleChar = getSingleGetBonkdCharacterData($characterName, $images, $tooltips, $urls);
			}
			break;

			case ETalentSite::HotsLogs:
			{
				$singleChar = getSingleHotsLogsCharacterData($characterName, $images, $tooltips, $urls);
			}
			break;

			case ETalentSite::HeroesFire:
			{
				$singleChar = getSingleHeroesfireCharacterData($characterName, $images, $tooltips, $urls);
			}
			break;
		}

		// ... to a single array.
		$entry = array_merge($entry, $singleChar);

		$targetArray[] = $entry;
	}

	foreach($CHARACTERS as $characterName) {
		
		echo "Getting HL information for $characterName...\n";
		addSingleCharacterTalents($characterName, $hlTalents, $images, $tooltips, $hlUrls, ETalentSite::HotsLogs);

		echo "Getting GB information for $characterName...\n";
		addSingleCharacterTalents($characterName, $gbTalents, $images, $tooltips, $gbUrls, ETalentSite::GetBonkd);
		
		echo "Getting HF information for $characterName...\n";
		addSingleCharacterTalents($characterName, $hfTalents, $images, $tooltips, $hfUrls, ETalentSite::HeroesFire);
	}

	truncateTable(ETable::Skills);
	populateSkills($images);

	truncateTable(ETable::HotsLogs);
	populateTalentTable($hlTalents, ETable::HotsLogs);

	truncateTable(ETable::GetBonkd);
	populateTalentTable($gbTalents, ETable::GetBonkd);
	
	truncateTable(ETable::HeroesFire);
	populateTalentTable($hfTalents, ETable::HeroesFire);
	
	truncateTable(ETable::Tooltips);
	populateTooltips($tooltips);

	truncateTable(ETable::Time);
	populateTime();

	truncateTable(ETable::Urls);
	populateUrls($hlUrls, $gbUrls, $hfUrls, $CHARACTERS);
