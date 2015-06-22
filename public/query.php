<?php

	function queryDB($sQueryString) {
		$link = mysql_connect('localhost', 'root', 'root');
		if (!$link) {
		    die('Could not connect: ' . mysql_error());
		}

		$result = @mysql_query($sQueryString, $link);

		if ( !$result ) {
			error_log("Query failed: " . mysql_error());
		}

		$id = mysql_insert_id($link);

		mysql_close($link);

		return array("res" => $result, "id" => $id);
	}

	function populateTalentTable($array, $targetTable) {

		foreach ($array as $key => $value) {
			
			$query = "INSERT INTO hots_bgk_io.$targetTable (hero, one, four, seven, ten, thirteen, sixteen, twenty ) VALUES(";

			$count = 0;
			foreach ( $value as $innerKey => $innerVal ) {
				$query .= "\"$innerVal\"";
				
				$count++;
				if ( $count < count($value) )
				{
					$query .= ", ";
				}
			}

			$query .= ");";

			queryDB($query);
		}
	}

	function truncateTable($targetTable) {
		$query = "TRUNCATE hots_bgk_io.$targetTable;";

		queryDB($query);
	}

	function populateSkills($array) {
		$query = "INSERT IGNORE INTO hots_bgk_io." . ETable::Skills . " (name, imgpath) VALUES";

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
