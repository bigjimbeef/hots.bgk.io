<?php
	include("simple_html_dom.php");
	include("query.php");
	include("constants.php");

	function getSingleGetBonkdCharacterData($character, &$images, &$tooltips)
	{
		// Remove punctuation and replace spaces with dashes.
		$character 	= preg_replace("/['|\.]/", "", $character);
		$character 	= preg_replace("/ /", "-", $character);
		$url		= "http://getbonkd.com/guides/$character/";

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

	function getSingleHotsLogsCharacterData($character, &$images, &$tooltips)
	{
		// Ensure the character name is capitalised, because HL needs that for some reason.
		$character 	= ucwords($character);
		$character 	= rawurlencode($character);
		$url		= "https://www.hotslogs.com/Sitewide/HeroDetails?Hero=$character";

		$talents = array();

		$html = file_get_html($url);

		$table 	= $html->find("table", 2);
		$row 	= $table->find("tr.rgRow", 0);

		$count = 0;
		foreach($row->find("td img") as $singleTalent)
		{
			$matches = null;
			$returnValue = preg_match("/(.*):/", $singleTalent->title, $matches);

			if ( !empty($matches) )
			{
				$talentName = html_entity_decode($matches[1], ENT_QUOTES);
				$talents[] = $talentName;

				$imgSrc = $singleTalent->src;
				if ( !isset($images[$talentName]) ) {
					$images[$talentName] = $imgSrc;
				}
			}
		}

		return $talents;
	}

	$gbTalents 	= array();
	$hlTalents	= array();
	$images		= array();
	$tooltips 	= array();

	function addSingleCharacterTalents($characterName, &$targetArray, &$images, &$tooltips, $targetSite)
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
				$singleChar = getSingleGetBonkdCharacterData($characterName, $images, $tooltips);
			}
			break;

			case ETalentSite::HotsLogs:
			{
				$singleChar = getSingleHotsLogsCharacterData($characterName, $images, $tooltips);
			}
			break;

			case ETalentSite::HeroesFire:
			{
				// TODO!
			}
			break;
		}

		// ... to a single array.
		$entry = array_merge($entry, $singleChar);

		$targetArray[] = $entry;
	}

	foreach($CHARACTERS as $characterName) {

		//echo "Getting HL information for $characterName...\n";
		//addSingleCharacterTalents($characterName, $hlTalents, $images, ETalentSite::HotsLogs);

		echo "Getting GB information for $characterName...\n";
		addSingleCharacterTalents($characterName, $gbTalents, $images, $tooltips, ETalentSite::GetBonkd);

		// TODO: Heroesfire.
	}

	/*
	truncateTable(ETable::Skills);
	populateSkills($images);

	truncateTable(ETable::HotsLogs);
	populateTalentTable($hlTalents, ETable::HotsLogs);

	truncateTable(ETable::GetBonkd);
	populateTalentTable($gbTalents, ETable::GetBonkd);
	*/

	truncateTable(ETable::Tooltips);
	populateTooltips($tooltips);

	/*
	truncateTable(ETable::Time);
	populateTime();
	*/
