<?php
	include_once("simple_html_dom.php");
	include_once("constants.php");
	include_once("utils.php");
	include_once("query.php");

	$talents 	= array();
	$images 	= array();

	define("USE_PTR", 0);
	$ptrFilter	= USE_PTR ? "?filter-ptr=1" : "";

	function scrapeData(&$talents, &$images) {

		global $TALENT_LEVELS;
		global $ptrFilter;

		$tierNames	= array_keys($TALENT_LEVELS);
		$baseURL 	= "http://www.heroesnexus.com/";
		$talentCalc = "talent-calculator";

		$html 		= file_get_html($baseURL . $talentCalc);

		foreach( $html->find("li.hero-champion") as $li ) {

			$hero 		= $li->find("figcaption", 0);
			$heroName 	= decode($hero->innertext);

			echo "Scraping " . $heroName . "...\n";

			$anchor		= $li->find("a", 0);
			$url		= $baseURL . $anchor->href . $ptrFilter;
			echo "URL: $url...\n";

			$talentHtml = file_get_html($url);

			$tiers		= array();

			// The counts, per tier, for getting the talent "number".
			$counts		= array();
			foreach ( $talentHtml->find("li.talent") as $talent ) {

				$talentTier = $talent->{"data-tier"};
				// Ensuring we remove HTML special chars from the talent names.
				$talentName = decode($talent->{"data-talent-name"});
				$tipHref	= $talent->{"data-tooltip-href"};

				$tierName	= $tierNames[$talentTier];

				if ( !isset($tiers[$tierName]) ) {
					$tiers[$tierName] = array();
				}
				if ( !isset($counts[$tierName]) ) {
					$counts[$tierName] = 0;
				}

				$tipHtml	= file_get_html($baseURL . $tipHref . $ptrFilter);

				$tipSection	= $tipHtml->find('.t-talent-desc', 0);
				$tooltip	= $tipSection->find('.db-description div', 0);
				$tooltip	= strip_tags($tooltip);

				array_push($tiers[$tierName], [
					"name" => $talentName,
					"desc" => $tooltip,
					"num" => $counts[$tierName]
				]);

				// Strip the image, for later snaffling.
				$img					= $tipSection->find('img', 0);
				$imgPath				= $img->src;
				$images[$talentName] 	= $imgPath;

				++$counts[$tierName];
			}

			$talents[$heroName] = $tiers;
		}
	}

	function getImages($images) {

		$imagePath = "/images/talents/";

		foreach ( $images as $talentName => $url ) {

			$imageName 	= prepImageName($talentName);

			$cmd		= "wget $url";
			exec($cmd);

			$dest		= dirname(__FILE__) . $imagePath . $imageName . ".png";
			$cmd		= "mv icon.png " . $dest;
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

?>