<?php

	include_once("utils.php");
	include_once("constants.php");

	function queryDB($sQueryString) {
		$link = mysql_connect('localhost', 'minikeen', 'test');
		if (!$link) {
		    die('Could not connect: ' . mysql_error());
		}

		logQuery($sQueryString);

		$result = @mysql_query($sQueryString, $link);

		if ( !$result ) {
			error_log("Query failed: " . mysql_error());
		}

		$id = mysql_insert_id($link);

		mysql_close($link);

		return array("res" => $result, "id" => $id);
	}

	function getClosestTalent($heroName, $talentName, $talentTier) {

		// Special-casing for HotsLogs.
		// TODO: This is awful.
		if ( $talentName == "Player's Choice" ) {
			return $talentName;
		}

		// We get the matching talent from the talents DB.
		$matchQuery = "SELECT name FROM hots_bgk_io." . ETable::Talents . " AS t WHERE t.hero =\"$heroName\" AND t.tier=\"$talentTier\";";

		//echo $matchQuery."\n";
		$result = queryDB($matchQuery);

		$matches = array();
		while ($row = mysql_fetch_assoc($result["res"])) {

			$matches[] = $row["name"];
		}

		$closest = getClosestString($talentName, $matches);

		//echo "Closest '$closest' from '$talentName' at $talentTier...\n";

		return $closest;
	}

	//
	// 	hero
	//		=> Abathur
	//	talents
	//		=> Talent 1
	//		=> Talent 2
	//		...
	//
	function populateTalentTable($array, $targetTable) {

		global $TALENT_LEVELS;

		$tierNames	= array_keys($TALENT_LEVELS);
		$query 		= "INSERT INTO hots_bgk_io.$targetTable (hero, one, four, seven, ten, thirteen, sixteen, twenty ) VALUES ";

		foreach ( $array as $singleHero ) {

			$heroName 	= $singleHero["hero"];
			$talents	= $singleHero["talents"];

			$query .= "(\"" . addslashes($heroName) . "\", ";

			$max = count($talents);
			for ( $i = 0; $i < $max; $i++ ) {
				
				$talent 	= $talents[$i];
				$talentTier = $tierNames[$i];

				$matchedName = getClosestTalent($heroName, $talent, $talentTier);
				$query .= "\"" . addslashes($matchedName) . "\", ";
			}

			$query = rtrim($query, ", ");
			$query .= "), ";
		}

		$query = rtrim($query, ", ");
		$query .= ";";

		//echo $query . "\n";

		queryDB($query);
	}

	function truncateTable($targetTable) {
		$query = "TRUNCATE hots_bgk_io.$targetTable;";

		queryDB($query);
	}

	function populateVideos($array) {
		
		$query = "INSERT IGNORE INTO hots_bgk_io." . ETable::Videos . " (hero, videopath) VALUES";

		$count = 0;
		foreach ($array as $key => $value) {
			$query .= "(\"$key\", \"$value\")";

			++$count;
			if ( $count < count($array) ) {
				$query .= ", ";
			}
		}

		$query .= ";";

		queryDB($query);
	}

	function populateTime() {
		$date 	= date("g:ia \o\\n l jS F Y");

		$query 	= "INSERT INTO hots_bgk_io." . ETable::Time . " (updated) VALUES ('" . $date . "');";

		queryDB($query);
	}

	function populateUrls($hlUrls, $gbUrls, $hfUrls, $ivUrls, $CHARACTERS) {

		$query = "INSERT IGNORE INTO hots_bgk_io." . ETable::Urls . " (name, hotslogs, getbonkd, heroesfire, icyveins) VALUES";

		foreach($CHARACTERS as $characterName) {

			$hl = isset($hlUrls[$characterName]) ? $hlUrls[$characterName] : "";
			$gb = isset($gbUrls[$characterName]) ? $gbUrls[$characterName] : "";
			$hf = isset($hfUrls[$characterName]) ? $hfUrls[$characterName] : "";
			$iv = isset($ivUrls[$characterName]) ? $ivUrls[$characterName] : "";

			$query .= "(\"" . $characterName . "\", \"" . addslashes($hl) . "\", \"" . addslashes($gb) . "\", \"" . addslashes($hf) . "\", \"" . addslashes($iv) . "\"), ";
		}

		$query = rtrim($query, ", ");
		$query .= ";";

		queryDB($query);
	}

	//	hero -> 
	//		tier ->
	//			name, desc
	//
	function populateTalents($talents) {

		$query 	= "INSERT IGNORE INTO hots_bgk_io." . ETable::Talents . "(hero, name, tier, description, imgurl, number) VALUES ";

		// DEBUG
		//$query 	= "INSERT IGNORE INTO hots_bgk_io." . ETable_DEBUG::Talents . "(hero, name, tier, description, imgurl, number) VALUES ";

		foreach ( $talents as $heroName => $talentTiers ) {

			foreach ( $talentTiers as $tierName => $tierTalents ) {

				foreach ( $tierTalents as $singleTalent ) {

					if ( !isset($singleTalent["name"]) || !isset($singleTalent["desc"]) ) {
						error_log("Missing talent name/desc for $heroName on tier $tierName.");
						continue;
					}

					$talentName = $singleTalent["name"];
					$talentDesc = $singleTalent["desc"];
					$talentNum	= $singleTalent["num"];

					$imgurl		= "/images/talents/" . prepImageName($talentName) . ".png";
					$query 		.= 
						"(\"" 			. addslashes($heroName) 	. 
							"\", \"" 	. addslashes($talentName) 	. 
							"\", \"" 	. addslashes($tierName) 	. 
							"\", \"" 	. addslashes($talentDesc) 	. 
							"\", \"" 	. addslashes($imgurl) 		. 
							"\", \"" 	. addslashes($talentNum) 		. 
						"\"), ";
				}
			}
		}

		// Add in the "Player's Choice" talent.
		$query .= "('any', 'Player\'s Choice', 'any', 'Up to you what to pick. All talents are viable.', '/images/talents/playerschoice.png', '?'), ";

		$query = rtrim($query, ", ");
		$query .= ";";

		queryDB($query);
	}
