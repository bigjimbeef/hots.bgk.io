<?php
	include_once("constants.php");
	include_once("utils.php");

	foreach($CHARACTERS as $characterName) {

		$blizzName = "";
		$isChogall = $characterName == "Cho" || $characterName == "Gall";

		if ( $characterName == "Li Li" ) {
			$blizzName = "lili";
		}
		else if ( $isChogall ) {
			$blizzName = "chogall";
		}
		else {
			$blizzName = getBlizzName($characterName);
		}

		echo "Getting image for $characterName ($blizzName.jpg)\n";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://eu.battle.net/heroes/static/images/heroes/busts/${blizzName}.jpg");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

		$fp = fopen("images/busts/${characterName}.jpg", "w+");
		curl_setopt($ch, CURLOPT_FILE, $fp);

		$result = curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}
