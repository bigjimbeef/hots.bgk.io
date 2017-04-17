<?php

	function decode($text) {
		$decoded = htmlspecialchars_decode($text);
		return html_entity_decode($decoded, ENT_QUOTES, "UTF-8");
	}

	function isUrlOk($url) {
	    $headers = @get_headers($url);

	    if ( $headers[0] == 'HTTP/1.1 200 OK' ) {
	    	return true;
	    }
	    else {
	    	return false;
	    }
	}

	function replaceAccents($str) {

		$search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ");
		$replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE");
		
		return str_replace($search, $replace, $str);
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
	
	function printWithDate($text) {

		$date = date("F j, Y, H:i");

		echo ("[" . $date . "] " . $text . "\n");
	}

	function logQuery($query) {

		$date = date('m-d-Y_hi');
		$filepath = dirname(__FILE__) . "/logs/" . $date;

		$fh = fopen($filepath, "w+");
		fwrite($fh, $query);

		fclose($fh);
	}

	function getCharacterList() {

		$filepath = dirname(__FILE__) . "/characters";
		$characters = file_get_contents($filepath);

		$charArray = explode("\n", rtrim($characters));

		return $charArray;
	}

	function strippedName($talent) {
		
		$talent = strtolower($talent);
		$talent = preg_replace("/[':,!]/", "", $talent);
		$talent = preg_replace("/[ \.]/", "-", $talent);
		
		return $talent;
	}
