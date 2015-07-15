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

	/*<?php

    include('simple_html_dom.php');
    include('constants.php');
    include("index.php");

    CONST HEROES_URL = 'http://www.heroesfire.com';

    $debug = in_array('--debug', $argv);

    $chars = [];

    $file1 = file_get_html(HEROES_URL);
    $everything= [];

    foreach ($file1->find('.hero a') as $heroKey => $hero) {
        $file2 = file_get_html(HEROES_URL . $hero->href);
        $name = explode('/', $hero->href)[4];
        $guide = $file2->find('.browse-table a')[0]->href;

        $file3 = file_get_html(HEROES_URL . $guide);
        $skills = $file3->find('.skills', 0);

        $everything[$heroKey][] = $name;

        foreach ($skills->find('.skill') as $level => $skill) {
            $imgUrl = $skill->find('.level .pic img', 0)->src;
            $nameArray = explode("-", explode('.png', explode('/', $imgUrl)[5])[0]);
            $nameArray = array_map(function ($str) {
                return  ucfirst($str);
            }, $nameArray);

            $name = implode(' ', $nameArray);
            $points = $skill->find('.points', 0);
            foreach ($points->find('div') as $key => $point) {
                if (isset($point->class) && $point->class == 'selected') {
                    $everything[$heroKey][] = [$name, HEROES_URL . $imgUrl];
                    break;
                }
            }
        }

		break;
    }
print_r($everything);
*/

	function doHeroesFireCharFormat($character) {

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

	function getSingleHeroesfireCharacterData($character, &$images, &$tooltips)
	{
		$hfCharacter 	= doHeroesFireCharFormat($character);
		$id 			= findCharacterID($hfCharacter);

		if ( $id < 0 ) {
			error_log("SOMETHING WENT BADLY WRONG WITH HEROESFIRE.");
		}

		// Build the new, and proper, URL.
		$url			= "http://www.heroesfire.com/hots/guides?s=t&fHeroes=" . $id . "&fMaps=&fCategory=";
		$html 			= file_get_html($url);

		$bestGuide		= $html->find(".browse-item-list a", 0);
		$bestGuideURL	= "http://www.heroesfire.com" . $bestGuide->href;


		return "hello";
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
				$singleChar = getSingleHeroesfireCharacterData($characterName, $images, $tooltips);
			}
			break;
		}

		// ... to a single array.
		$entry = array_merge($entry, $singleChar);

		$targetArray[] = $entry;
	}

	foreach($CHARACTERS as $characterName) {

		/*
		echo "Getting HL information for $characterName...\n";
		addSingleCharacterTalents($characterName, $hlTalents, $images, $tooltips, ETalentSite::HotsLogs);

		echo "Getting GB information for $characterName...\n";
		addSingleCharacterTalents($characterName, $gbTalents, $images, $tooltips, ETalentSite::GetBonkd);
		*/

		echo "Getting HF information for $characterName...\n";
		addSingleCharacterTalents($characterName, $gbTalents, $images, $tooltips, ETalentSite::HeroesFire);

		break;
	}

	/*
	truncateTable(ETable::Skills);
	populateSkills($images);

	truncateTable(ETable::HotsLogs);
	populateTalentTable($hlTalents, ETable::HotsLogs);

	truncateTable(ETable::GetBonkd);
	populateTalentTable($gbTalents, ETable::GetBonkd);

	truncateTable(ETable::Tooltips);
	populateTooltips($tooltips);

	truncateTable(ETable::Time);
	populateTime();
	*/
