<?php

	function decode($text) {
		$decoded = htmlspecialchars_decode($text);
		return html_entity_decode($decoded, ENT_QUOTES, "UTF-8");
	}

	function prepImageName($talentName) {

		$output = htmlspecialchars_decode($talentName);
		$output = decode($output);
		$output = strtolower($output);
		$output = preg_replace("#[[:punct:]]#", "", $output);
		$output = preg_replace("/ /", "", $output);

		return $output;
	}

	function getClosestString($inputString, $possibilities) {
		
		if ( empty($inputString) ) {
			return "";
		}

		// We remove periods from search terms if none are present in the input.
		$keepPeriods = strpos($inputString, '.') !== false;

		$closest = "";
		$shortest = -1;

		foreach ( $possibilities as $possibility )
		{
			$temp = $possibility;
			if ( !$keepPeriods )
			{
				$temp = str_replace(".", "", $possibility);
			}

		    $lev = levenshtein($inputString, $temp);

		    // Check for exact match.
		    if ($lev == 0) {
		        $closest = $possibility;
		        $shortest = 0;

		        break;
		    }

		    if ($lev <= $shortest || $shortest < 0) {
		        $closest  = $possibility;
		        $shortest = $lev;
		    }
		}

		return $closest;
	}

	function getBlizzName($character) {

		$character 	= strtolower($character);
		$character 	= preg_replace("/['|\.]/", "", $character);
		$character 	= preg_replace("/ /", "-", $character);

		return $character;
	}
	