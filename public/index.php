<?php
	include("simple_html_dom.php");

	const 	DEBUG = 0;

	const 	MAX_TALENTS = 7;
	$CHARACTERS = array(
		"Abathur",
		"Anub'arak",
		"Arthas",
		"Azmodan",
		"Brightwing",
		"Chen",
		"Diablo",
		"E.T.C.",
		"Falstad",
		"Gazlowe",
		"Illidan",
		"Jaina",
		"Johanna",
		"Kael'thas",
		"Kerrigan",
		"Li Li",
		"Malfurion",
		"Muradin",
		"Murky",
		"Nazeebo",
		"Nova",
		"Raynor",
		"Rehgar",
		"Sgt Hammer",
		"Sonya",
		"Stitches",
		"Sylvanas",
		"Tassadar",
		"The Butcher",
		"The Lost Vikings",
		"Thrall",
		"Tychus",
		"Tyrael",
		"Tyrande",
		"Uther",
		"Valla",
		"Zagara",
		"Zeratul"
	);

	function getClosestName($input)
	{
		// We remove periods from search terms if none are present in the input.
		$keepPeriods = strpos($input, '.') !== false;

		global $CHARACTERS;

		$closest = "";
		$shortest = -1;

		foreach ( $CHARACTERS as $character )
		{
			$temp = $character;
			if ( !$keepPeriods )
			{
				$temp = str_replace(".", "", $character);
			}

		    $lev = levenshtein($input, $temp);

		    // Check for exact match.
		    if ($lev == 0) {
		        $closest = $character;
		        $shortest = 0;

		        break;
		    }

		    if ($lev <= $shortest || $shortest < 0) {
		        $closest  = $character;
		        $shortest = $lev;
		    }
		}

		return $closest;
	}

	function getBonkd($character, $baseHTML)
	{
		// Remove punctuation and replace spaces with dashes.
		$character 	= preg_replace("/['|\.]/", "", $character);
		$character 	= preg_replace("/ /", "-", $character);
		$url		= "http://getbonkd.com/guides/$character/";
		
		$output = "";

		if ( DEBUG )
		{
			$output = ("GB:" . $url . PHP_EOL);
		}
		else 
		{
			$html = file_get_html($url);

			$count = 0;
			foreach($html->find("#toptalents span.tooltips") as $singleTalent) 
			{
				$text 	= $singleTalent->title;

				$img 	= $singleTalent->find("img", 0);
				$img->description = $text;

				if ( !empty($text) )
				{
					if ( $count >= MAX_TALENTS )
					{
						break;
					}
				}

				$output .= $img;

				$count++;
			}
		}

		$baseHTML->find("#getbonkd div.talents", 0)->innertext = $output;

		return $baseHTML;
	}

	function hotsLogs($character, $baseHTML)
	{
		// Ensure the character name is capitalised, because HL needs that for some reason.
		$character 	= ucwords($character);
		$character 	= rawurlencode($character);
		$url		= "https://www.hotslogs.com/Sitewide/HeroDetails?Hero=$character";

		$output = "";

		if ( DEBUG )
		{
			$output = ("HL:" . $url . PHP_EOL);
		}
		else
		{
			$html = file_get_html($url);

			$table 	= $html->find("table", 2);
			$row 	= $table->find("tr.rgRow", 0);

			$count = 0;
			foreach($row->find("td img") as $singleTalent)
			{
				if ( !empty($singleTalent) )
				{
					if ( $count >= MAX_TALENTS )
					{
						break;
					}
				}

				$output .= $singleTalent;

				$count++;
			}
		}

		$baseHTML->find("#hotslogs div.talents", 0)->innertext = $output;

		return $baseHTML;
	}

	function getCharacterFromURL()
	{
		$retVal = "";

		$uri = $_SERVER['REQUEST_URI'];
		$uri = trim($uri, '/');

		if ( !empty($uri) )
		{
			$retVal = $uri;
		}

		$retVal = urldecode($retVal);

		return $retVal;
	}

	// The base contents of the page.
	$pageContents = "";

	$character = getCharacterFromURL();
	$closest = getClosestName($character);

	if ( !empty($closest) )
	{
		$baseHTML = file_get_html("base.html");

		$moddedHTML = $baseHTML;
		$moddedHTML = getBonkd($closest, $baseHTML);
		$moddedHTML = hotsLogs($closest, $moddedHTML);

		$moddedHTML->find("h1", 0)->innertext = $closest;

		$pageContents .= $moddedHTML;
	}

	echo $pageContents;
?>
