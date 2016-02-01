<?php

if ($argc < 2) {
	echo "Usage: php newChar.php [CHARNAME]\n";
	exit();
}

$newChar = $argv[1];

$file = fopen("characters", "r");
$chars = [];

$prevCharacter = "";
$prevLine = 0;

while(!feof($file)){
    $line = fgets($file);

    // Check if this is where the new character fits.
    if (strcasecmp($newChar, $line) > 0) {
        $prevLine = $line;
        continue;
    }

    $prevCharacter = $prevLine;
    break;
}
fclose($file);


function writeTextAfterCharacter($file, $text, $prevCharacter) {

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
writeTextAfterCharacter("empty.html", "\t\t\t\t\t\t<a href='/$newChar'><img data-name='$newChar' title='$newChar' src='/images/$newChar.png' /></a>", $prevCharacter);

// Populate the new talents, and images, for the new guy.
system("php talents.php --images");

// Get the video path for the new guy.
system("php videos.php");

// Get the data!
system("php populate.php");
