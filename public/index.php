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

	function getBlizzName($character) {

		$character 	= strtolower($character);
		$character 	= preg_replace("/['|\.]/", "", $character);
		$character 	= preg_replace("/ /", "-", $character);

		return $character;
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

		$query = "SELECT one, four, seven, ten, thirteen, sixteen, twenty FROM hots_bgk_io.$targetTable AS gb WHERE gb.hero LIKE '%" . addslashes($character) . "%';";
		$result = queryDB($query);

		$row = mysql_fetch_assoc($result["res"]);

		if ( !isset($row) || empty($row) ) {
			
			$baseHTML->find("#$targetTable", 0)->outertext = "";
			return $baseHTML;
		}
	    
    	$html = "<table>";
        foreach ( $row as $col_name => $col_val )
        {
        	$escaped = addslashes($col_val);

        	$query = "SELECT * FROM hots_bgk_io." . ETable::Talents . " AS t WHERE t.hero LIKE '%" . addslashes($character) . "%' AND t.name LIKE '%" . $escaped . "%'";
        	$result = queryDB($query);

        	$res = mysql_fetch_assoc($result["res"]);

        	$imgpath = $res["imgurl"];
        	$talentNum = $TALENT_LEVELS[$col_name];

        	$tooltip = htmlspecialchars($res["description"], ENT_QUOTES);

        	// Create talent hotkey indicator.
        	$talentHotkey 	= $res["number"];
        	$hotkeyHTML		= createTalentHotkeyIndicator($character, $talentHotkey, $col_name);

        	$html .= "<tr><td class='talentNum'>$talentNum</td><td>$col_val</td><td><img title='$tooltip' src='http://www.hotsbuilds.info$imgpath' /></td><td>$hotkeyHTML</td></tr>";
        }
        $html .=  "</table>";

	    $baseHTML->find("#$targetTable div.talents", 0)->innertext = $html;

	    return $baseHTML;
	}

	function createTalentHotkeyIndicator($character, $hotkey, $tier) {

		$colName = "count";
		$query = "SELECT count(*) AS $colName FROM hots_bgk_io." . ETable::Talents . " AS t WHERE t.hero LIKE '%" . addslashes($character) . "%' AND t.tier LIKE '%" . $tier . "%'";
        $result = queryDB($query);

        $res = mysql_fetch_assoc($result["res"]);

        $numTalents = intval($res[$colName]);

		$outHTML = "<div class='talent-numbers'>";
		for ( $i = 0; $i < $numTalents; ++$i ) {

			$outHTML .= "\t<div class='talent-number ";
			if ( $i == intval($hotkey) ) {
				$outHTML .= "highlighted";
			}
			$outHTML .= "'>" . ($i + 1) . "</div>\n";
		}
		$outHTML .= "</div>";

		return $outHTML;
	}

	function setupVideoBackground($character, $baseHTML) {

		$query = "SELECT videopath FROM hots_bgk_io." . ETable::Videos . " AS v WHERE v.hero LIKE '%" . addslashes($character) . "%'";
		$result = queryDB($query);

		$res = mysql_fetch_assoc($result["res"]);

		$videopath = $res["videopath"];

		$baseHTML->find("#bg-video", 0)->src = $videopath;

		return $baseHTML;
	}

	function setupTime($baseHTML) {

		$query = "SELECT updated FROM hots_bgk_io." . ETable::Time . ";";
		$result = queryDB($query);

		$res = mysql_fetch_assoc($result["res"]);
		$updated = $res["updated"];

		$baseHTML->find("#update-time", 0)->innertext = "Last updated: $updated";

		return $baseHTML;
	}

	function addCharacterJSON($baseHTML) {

		global $CHARACTERS;

		$json = "<script>var characterJson = [";

		$count = 0;
		foreach ( $CHARACTERS as $character ) {

			$json .= "\"$character\"";

			++$count;
			if ( $count < count($CHARACTERS) ) {
				$json .= ", ";
			}
		}

		$json .= "];</script>";

		$baseHTML .= $json;

		return $baseHTML;
	}

	function addTitle($baseHTML, $character) {

		$baseHTML->find("title", 0)->innertext = "HotS builds: $character";

		return $baseHTML;
	}

	function setUrl($character, $moddedHTML, $targetTable) {

		$query = "SELECT $targetTable FROM hots_bgk_io." . ETable::Urls . " AS u WHERE u.name LIKE '%" . addslashes($character) . "%'";
		$result = queryDB($query);

		$res = mysql_fetch_assoc($result["res"]);
		$url = $res[$targetTable];

		$moddedHTML->find("#$targetTable h2 a", 0)->href = $url;

		return $moddedHTML;
	}

	function stripBuildName($name) {

		$name = strtolower($name);
		$name = preg_replace("/ /", "", $name);

		return $name;
	}

	function doIcyVeins($character, $baseHTML) {

		global $TALENT_LEVELS;
		$talentLevelKeys = array_keys($TALENT_LEVELS);
		$talentLevelValues = array_values($TALENT_LEVELS);

		$blizzName = getBlizzName($character);

		// Get the JSON.
		$baseUrl = "http://www.icy-veins.com/heroes/";
		$targetUrl = $baseUrl . $blizzName . ".json";
		if ( !urlOk($targetUrl) ) {
			return $baseHtml;
		}

		$jsonStr = file_get_contents($targetUrl);
		$json = json_decode($jsonStr, true);

		// Tables and tables and tables.
		$html = "<table>";

		// Add the table header to switch builds.
		$html .= "<thead>";
		$isFirst = true;
		foreach ($json as $singleBuild) {

			$buildName = $singleBuild["name"];
			$strippedBuild = stripBuildName($buildName);

			$html .= "<tr><th colspan=3><input type='radio' name='iv-builds' id='$strippedBuild-input' value='$strippedBuild'";
			if ( $isFirst ) {
				$html .= " checked";
				$isFirst = false;
			}
			$html .= (" /><label for='$strippedBuild-input'>" . trim($buildName) . "</label></th></tr>");
		}
		$html .= "</thead>";

		foreach ($json as $singleBuild) {

			$buildName = stripBuildName($singleBuild["name"]);
			$talents = $singleBuild["talents"];

			$html .= "<tbody id='$buildName'>";
			foreach ( $talents as $index => $talent ) {

				$query = "SELECT * FROM hots_bgk_io." . ETable::Talents . " AS t WHERE t.hero LIKE '%" . addslashes($character) . "%' AND t.shortname LIKE '%" . $talent . "%'";
	        	$result = queryDB($query);

	        	$res = mysql_fetch_assoc($result["res"]);

	        	$talentName = $res["name"];
	        	$imgpath = $res["imgurl"];
	        	$tooltip = htmlspecialchars($res["description"], ENT_QUOTES);

	        	// Create talent hotkey indicator.
	        	$talentHotkey 	= $res["number"];
	        	$talentLevel	= $talentLevelKeys[$index];
	        	$hotkeyHTML		= createTalentHotkeyIndicator($character, $talentHotkey, $talentLevel);

				$talentNum = $talentLevelValues[$index];
				$html .= "<tr><td class='talentNum'>$talentNum</td><td>$talentName</td><td><img title='$tooltip' src='http://www.hotsbuilds.info$imgpath' /></td><td>$hotkeyHTML</td></tr>";
			}
			$html .= "</tbody>";
		}
		$html .= "</table>";

		$baseHTML->find("#icyveins div.talents", 0)->innertext = $html;

		return $baseHTML;
	}

	// The base contents of the page.
	$pageContents = "";

	$character 	= getCharacterFromURL();
	$loadChar	= !empty($character);

	// If we attempt to load a URL with a slash in it, we reroute the request to the index and update the browser.
	if ( strpos($character, "/") !== FALSE ) {
		
		header("LOCATION: http://www.hotsbuilds.info");
		$loadChar = false;
	}

	$closest 	= getClosestString($character, $CHARACTERS);

	if ( $loadChar )
	{
		$baseHTML 	= file_get_html("base.html");

		if ( file_exists("is_beta") ) {
			$baseHTML->find("#splash", 0)->innertext = "<div id='beta'>BETA</div>";
		}

		// Add the talents.
		$moddedHTML = drawTalents($closest, $baseHTML, ETable::GetBonkd);
		$moddedHTML = drawTalents($closest, $baseHTML, ETable::HotsLogs);
		//$moddedHTML = drawTalents($closest, $baseHTML, ETable::IcyVeins);

		// IV is bespoke.
		$moddedHTML = doIcyVeins($closest, $baseHTML);

		$moddedHTML = drawTalents($closest, $baseHTML, ETable::HeroesFire);

		// Add the URLs.
		$moddedHTML = setUrl($closest, $moddedHTML, ETable::GetBonkd);
		$moddedHTML = setUrl($closest, $moddedHTML, ETable::HotsLogs);
		$moddedHTML = setUrl($closest, $moddedHTML, ETable::IcyVeins);
		$moddedHTML = setUrl($closest, $moddedHTML, ETable::HeroesFire);

		// Draw the background video.
		$moddedHTML = setupVideoBackground($closest, $moddedHTML);

		$moddedHTML = setupTime($moddedHTML);

		$moddedHTML = addTitle($moddedHTML, $closest);

		// Character name.
		$moddedHTML->find("h1", 0)->innertext = $closest;
		$moddedHTML = addCharacterJSON($moddedHTML);

		$pageContents .= $moddedHTML;
	}
	else
	{
		$baseHTML		= file_get_html("empty.html");

		if ( file_exists("is_beta") ) {
			$baseHTML->find("#splash", 0)->innertext = "<div id='beta'>BETA</div>";
		}

		$baseHTML		= addCharacterJSON($baseHTML);
		$pageContents 	= $baseHTML;
	}

	echo $pageContents;
