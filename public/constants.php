<?php

	abstract class ETalentSite
	{
		const GetBonkd 		= 0;
		const HotsLogs 		= 1;
		const HeroesFire 	= 2;
	}

	abstract class ETable
	{
		const GetBonkd 		= "getbonkd";
		const HotsLogs 		= "hotslogs";
		const HeroesFire 	= "heroesfire";
		const Skills		= "skills";
		const Videos		= "videos";
		const Time			= "time";
		const Tooltips		= "tooltips";
	}

	$TALENT_LEVELS = array(
		"one" => "1",
		"four" => "4",
		"seven" => "7",
		"ten" => "10",
		"thirteen" => "13",
		"sixteen" => "16",
		"twenty" => "20"
	);

	$CHARACTERS = array(
		"Abathur",
		"Anub'arak",
		"Arthas",
		"Azmodan",
		"Brightwing",
		"Chen",
		"Diablo",
		"E.T.C.",
		"Falstad",
		"Gazlowe",
		"Illidan",
		"Jaina",
		"Johanna",
		"Kael'thas",
		"Kerrigan",
		//"King Leoric",
		"Li Li",
		"Malfurion",
		"Muradin",
		"Murky",
		"Nazeebo",
		"Nova",
		"Raynor",
		"Rehgar",
		"Sgt. Hammer",
		"Sonya",
		"Stitches",
		"Sylvanas",
		"Tassadar",
		"The Butcher",
		"The Lost Vikings",
		"Thrall",
		"Tychus",
		"Tyrael",
		"Tyrande",
		"Uther",
		"Valla",
		"Zagara",
		"Zeratul"
	);

	const MAX_TALENTS = 7;
