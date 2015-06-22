<?php
	include("simple_html_dom.php");
	include("constants.php");
	include("query.php");

	function getClosestName($input)
	{
		if ( empty($input) ) {
			return "";
		}

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

	function drawTalents($character, $baseHTML, $targetTable) {

		global $TALENT_LEVELS;

		$html = "";

		$query = "SELECT one, four, seven, ten, thirteen, sixteen, twenty FROM hots_bgk_io.$targetTable AS gb WHERE gb.hero LIKE '%" . addslashes($character) . "%';";
		$result = queryDB($query);
	
		while ( $row = mysql_fetch_assoc($result["res"]) )
	    {
	    	$html .= "<table>";
	        foreach ( $row as $col_name => $col_val )
	        {
	        	$query = "SELECT * FROM hots_bgk_io.skills AS s WHERE name LIKE '%" . addslashes($col_val) . "%'";
	        	$result = queryDB($query);

	        	$res = mysql_fetch_assoc($result["res"]);

	        	$imgpath = $res["imgpath"];
	        	$talentNum = $TALENT_LEVELS[$col_name];

	        	$html .= "<tr><td class='talentNum'>$talentNum</td><td>$col_val</td><td><img src='http:$imgpath' /></tr>";
	        }
	        $html .=  "</table>";
	    }

	    $baseHTML->find("#$targetTable div.talents", 0)->innertext = $html;

	    return $baseHTML;
	}

	function setupVideoBackground($character, $baseHTML) {

		$query = "SELECT videopath FROM hots_bgk_io." . ETable::Videos . " AS v WHERE v.hero LIKE '%" . addslashes($character) . "%'";
		$result = queryDB($query);

		$res = mysql_fetch_assoc($result["res"]);

		$videopath = $res["videopath"];

		$baseHTML->find("#bg-video", 0)->src = $videopath;

		return $baseHTML;
	}

	// The base contents of the page.
	$pageContents = "";

	$character 	= getCharacterFromURL();
	$closest 	= getClosestName($character);

	$baseHTML 	= file_get_html("base.html");

	if ( !empty($character) )
	{
		$moddedHTML = $baseHTML;
		$moddedHTML = drawTalents($closest, $baseHTML, ETable::GetBonkd);
		
		$moddedHTML = drawTalents($closest, $baseHTML, ETable::HotsLogs);

		// Draw the background video.
		$moddedHTML = setupVideoBackground($closest, $moddedHTML);

		// Character name.
		$moddedHTML->find("h1", 0)->innertext = $closest;

		$pageContents .= $moddedHTML;
	}
	else
	{
		$pageContents = $baseHTML;
	}

	echo $pageContents;
