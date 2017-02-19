<?php

	abstract class ETalentSite
	{
		const GetBonkd 		= 0;
		const HotsLogs 		= 1;
		const HeroesFire 	= 2;
		const IcyVeins		= 3;
	}

	abstract class ETable
	{
		const GetBonkd 		= "getbonkd";
		const HotsLogs 		= "hotslogs";
		const HeroesFire 	= "heroesfire";
		const IcyVeins		= "icyveins";
		const Videos		= "videos";
		const Time			= "time";
		const Urls			= "urls";
		const Talents		= "talents";
	}

	abstract class ETable_DEBUG
	{
		const GetBonkd 		= "getbonkd_debug";
		const HotsLogs 		= "hotslogs_debug";
		const HeroesFire 	= "heroesfire_debug";
		const IcyVeins		= "icyveins_debug";
		const Videos		= "videos_debug";
		const Time			= "time_debug";
		const Urls			= "urls_debug";
		const Talents		= "talents_debug";
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
		"Alarak",
		"Anub'arak",
		"Artanis",
		"Arthas",
		"Auriel",
		"Azmodan",
		"Brightwing",
		"Chen",
		"Cho",
		"Chromie",
		"Dehaka",
		"Diablo",
		"E.T.C.",
		"Falstad",
		"Gall",
		"Gazlowe",
		"Greymane",
		"Gul'dan",
		"Illidan",
		"Jaina",
		"Johanna",
		"Kael'thas",
		"Kerrigan",
		"Kharazim",
		"Leoric",
		"Li Li",
		"Li-Ming",
		"Lt. Morales",
		"Lucio",
		"Lunara",
		"Malfurion",
		"Medivh",
		"Muradin",
		"Murky",
		"Nazeebo",
		"Nova",
		"Ragnaros",
		"Raynor",
		"Rehgar",
		"Rexxar",
		"Samuro",
		"Sgt. Hammer",
		"Sonya",
		"Stitches",
		"Sylvanas",
		"Tassadar",
		"The Butcher",
		"The Lost Vikings",
		"Thrall",
		"Tracer",
		"Tychus",
		"Tyrael",
		"Tyrande",
		"Uther",
		"Valeera",
		"Valla",
		"Varian",
		"Xul",
		"Zarya",
		"Zagara",
		"Zeratul",
		"Zul'jin",
	);

	const MAX_TALENTS = 7;
