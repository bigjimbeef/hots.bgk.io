<?php

include_once("query.php");

function strippedName($talent) {
	
	$talent = strtolower($talent);
	$talent = preg_replace("/[':,!]/", "", $talent);
	$talent = preg_replace("/[ \.]/", "-", $talent);
	
	return $talent;
}

$query = "SELECT id,name FROM hots_bgk_io.talents;";

$result = queryDB($query);

while ($row = mysql_fetch_assoc($result["res"])) {

	$id = $row["id"];
	$name = $row["name"];

	$stripped = strippedName($name);

	$insert = "UPDATE hots_bgk_io.talents SET shortname='$stripped' WHERE id=$id;";

	$res = queryDB($insert);
}

