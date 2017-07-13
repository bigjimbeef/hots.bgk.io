<?php
	include_once("simple_html_dom.php");
	include_once("constants.php");
	include_once("utils.php");
	include_once("query.php");

	$talents 	= array();
	$images 	= array();

	function getSingleTalent($url) {

		global $TALENT_LEVELS;
		$tierNames = array_keys($TALENT_LEVELS);

		if (!isUrlOk($url) ) {
			error_log("URL is busted: $url");
			return;
		}

		$content = file_get_html($url);
		$textBoxObj = $content->find("div.text-box", 0);
		if (!is_object($textBoxObj)) {
			error_log("Text box is busted at [$url].");
			return;
		}

		$talentImgObj	= $textBoxObj->find("img", 0);
		$talentTierObj 	= $textBoxObj->find("span", 0);
		$talentNameObj 	= $textBoxObj->find("h4", 0);
		$talentDescObj 	= $textBoxObj->find("p", 0);
		if (!is_object($talentImgObj) || !is_object($talentTierObj) || !is_object($talentNameObj) || !is_object($talentDescObj)) {
			error_log("Talent is busted at [$url].");
			return;
		}

		// image stuff
		$talentImgUrl = $talentImgObj->{"src"};

		// talent stuff
		$talentTier = $talentTierObj->innertext;
		$talentNum 	= intval(preg_replace("/[^0-9]/", "", $talentTier)) - 1;
		$tierName	= $tierNames[$talentNum];

		$talentName = strip_tags($talentNameObj->innertext);
		$talentDesc = strip_tags($talentDescObj->innertext);
 
		return [$talentImgUrl, $talentName, $talentDesc];
	}

	function scrapeData(&$talents, &$images) {

		global $TALENT_LEVELS;

		$tierNames	= array_keys($TALENT_LEVELS);
		$baseURL	= "http://www.heroesfire.com";
		$heroesURL 	= "/hots/wiki/heroes/";
		$abilities	= "/abilities-talents";

		$characters = file_get_contents("characters");
		$charArray 	= explode("\n", rtrim($characters));

		foreach ($charArray as $charName) {
			
			$blizzName 	= getBlizzName($charName);
			$url 		= $baseURL . $heroesURL . $blizzName . $abilities;

			echo $blizzName . ": $url\n";
			
			$content	= file_get_html($url);

			$tiers		= array();

			// The talents, per "level" (tier)
			$currentTier = 0;

			foreach ( $content->find(".talents .level") as $levelTalents ) {

				$singleTier = array();

				$currentIndex = 0;
				foreach ( $levelTalents->find("a") as $singleTalent ) {
				
					$href = $singleTalent->{"href"};

					$talURL = $baseURL . $href;

					// Get the talent information.
					list($imgUrl, $talentName, $talentDesc) = getSingleTalent($talURL, $talents, $images);

					// Image
					$images[$talentName] = $baseURL . $imgUrl;

					// Talent
					$singleTalent = [
						"name" => decode($talentName),
						"desc" => decode($talentDesc),
						"num" => $currentIndex
					];
					array_push($singleTier, $singleTalent);

					++$currentIndex;
				}

				$tierName = $tierNames[$currentTier];
				$tiers[$tierName] = $singleTier;

				++$currentTier;
			}

			$talents[$charName] = $tiers;
		}
	}

	function getImages($images) {

		$imagePath = "/images/talents/";

		foreach ( $images as $talentName => $url ) {

			$imageTarget = end(explode('/', $url));
			$imageName 	= prepImageName($talentName);

			$cmd		= "wget $url";
			exec($cmd);

			$dest		= dirname(__FILE__) . $imagePath . $imageName . ".png";
			$cmd		= "mv $imageTarget $dest";
			exec($cmd);
		}
	}

	scrapeData($talents, $images);

	$doImages = in_array('--images', $argv);
	$skipSql = in_array('--nosql', $argv);

	// Don't want to repopulate the images table unless we're specifically being told to.
	if ( $doImages ) {
		getImages($images);
	}
	if ( !$skipSql ) {
		truncateTable(ETable::Talents);
		populateTalents($talents);
	}
