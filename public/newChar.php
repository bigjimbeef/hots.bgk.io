<?php

include_once("utils.php");

if ($argc < 2) {
	echo "Usage: php newChar.php [CHARNAME]\n";
	exit();
}

$newChar = $argv[1];

$filepath = dirname(__FILE__) . "/characters";
$file = fopen($filepath, "r");
$chars = [];

$prevCharacter = null;
$prevLine = 0;
$currCharacter = "";

while(!feof($file)){
    $line = fgets($file);
    
    if (!empty($line)) {
        $currCharacter = $line;        
    }

    // Check if this is where the new character fits.
    if (strcasecmp($newChar, $line) > 0) {
        $prevLine = $line;
        continue;
    }

    $prevCharacter = $prevLine;
    break;
}
fclose($file);

// Special case for this being the final character in the list.
if (is_null($prevCharacter)) {

    $prevCharacter = $currCharacter;
}

printWithDate("Previous character: $prevCharacter");

function writeTextAfterCharacter($file, $text, $prevCharacter) {

    $file = dirname(__FILE__) . "/" . $file;

    $lines = file($file);
    $constantsF = fopen($file, "w+");

    foreach($lines as $line)
    {
        fwrite($constantsF, $line);

        if (stripos($line, $prevCharacter) !== false) {
            fwrite($constantsF, $text . "\n");
        }
    }

    fclose($constantsF);
}

$prevCharacter = trim($prevCharacter);

// We now have the previous character, so read until we hit that character, then add our new one.
writeTextAfterCharacter("characters", "$newChar", $prevCharacter);
writeTextAfterCharacter("constants.php", "\t\t\"$newChar\",", $prevCharacter);
writeTextAfterCharacter("empty.html", "\t\t\t\t\t\t<a href=\"/$newChar\"><img data-name=\"$newChar\" title=\"$newChar\" src=\"/images/busts/$newChar.jpg\" /></a>", $prevCharacter);
