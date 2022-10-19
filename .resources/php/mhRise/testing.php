<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhRise/mhRise.xml");
/*
$time_start = microtime(true); 
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
*/
?>

<!DOCTYPE html>
<html>
<head>
	<title>Testing</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="/.resources/css/mh_style.css">
	<link rel="shortcut icon" type="image/x-icon" href=".resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>
<body>
<div class="image-hero-area" style="background: url('/.resources/img/mhRise/begimo.webp') no-repeat center center; background-size: cover;"></div>
<div id="container">
<div id="menu">
	<?php include("/mnt/disk/.resources/php/mhRise/menu.html"); ?>
</div>
<div id="content" style="color: white;">
	<?php
		function hunterAVG($bd, $numP){
			// We only have reliable info on these cats
			$cats = ["Keku", "Onikuro", "Cereza"];

			$percents = array();
			foreach ($bd->hunt as $hunt) {
				if(count($hunt->player) != $numP || count($hunt->otomo) == 0){
					continue;
				}
				if(intval($hunt->failed)){
					continue;
				}

				$otomos = getOtomosFromHunt($hunt);
				$inter = array_intersect($otomos, $cats);
				if (count($inter) == count($otomos)) {
					continue;
				}

				$hp = 0;
				$damage = 0;
				$hunters = getHuntersFromHunt($hunt);

				foreach ($hunt->monster as $monster) {
					$hp = $hp + intval($monster->maxHP);
					foreach ($monster->player as $player) {
						if(!in_array($player->name, $hunters)){
							continue;
						}
						$damage = $damage + ($player->phys + $player->elem + $player->poison + $player->blast);
					}
				}

				$percents[] = $damage / $hp * 100;
			}

			return array_sum($percents) / count($percents);
		}



		function otomoAVG($bd, $numP){
			$percents = array();
			foreach ($bd->hunt as $hunt) {
				if(count($hunt->player) != $numP || count($hunt->otomo) == 0){
					continue;
				}

				$hp = 0;
				$damage = 0;

				foreach ($hunt->monster as $monster) {
					$hp = $hp + intval($monster->maxHP);
					foreach ($monster->player as $player) {
						if(isOtomo($bd, $player->name)){
							$damage = $damage + ($player->phys + $player->elem + $player->poison + $player->blast);
						}
					}
				}

				$percents[] = $damage / $hp * 100;
			}

			return array_sum($percents) / count($percents);
		}

		function getMaxMonster($bd, $p){
			$monstersPlayed = array();
			$monstersWon = array();
			foreach ($bd->hunt as $hunt) {
				$hunters = getHuntersFromHunt($hunt);
				if(!in_array($p, $hunters)){
					continue;
				}

				foreach ($hunt->monster as $monster) {
					$mName = strval($monster->name);
					$percents = array();
					foreach ($monster->player as $player) {
						$pName = strval($player->name);
						$percents[$pName] = ($player->phys + $player->elem + $player->poison + $player->blast) / $monster->maxHP;
						if($pName == $p){
							if(isset($monstersPlayed[$mName])){
								$monstersPlayed[$mName]++;
							}
							else{
								$monstersPlayed[$mName] = 1;
							}
						}
					}

					arsort($percents);
					$winner = array_key_first($percents);
					if($winner == $p){
						if(isset($monstersWon[$mName])){
							$monstersWon[$mName]++;
						}
						else{
							$monstersWon[$mName] = 1;
						}
					}
				}
			}

			$results = array();
			foreach ($monstersWon as $mName => $value) {
				$results[$mName] = $value / $monstersPlayed[$mName];
			}
			
			arsort($results);


			foreach ($monstersWon as $mName => $value) {
				echo $mName, ": ", $value, " / ", $monstersPlayed[$mName], "<br>";
			}
			var_dump($results);

			return array_key_first($results);
		}

		function getMinMonster($bd, $p){
			$monstersPlayed = array();
			$monstersWon = array();
			foreach ($bd->hunt as $hunt) {
				$hunters = getHuntersFromHunt($hunt);
				if(!in_array($p, $hunters)){
					continue;
				}

				foreach ($hunt->monster as $monster) {
					$mName = strval($monster->name);
					$percents = array();
					foreach ($monster->player as $player) {
						$pName = strval($player->name);
						if(!in_array($pName, $hunters)){
							continue;
						}
						$percents[$pName] = ($player->phys + $player->elem + $player->poison + $player->blast) / $monster->maxHP;
						if($pName == $p){
							if(isset($monstersPlayed[$mName])){
								$monstersPlayed[$mName]++;
							}
							else{
								$monstersPlayed[$mName] = 1;
							}
						}
					}

					asort($percents);
					$winner = array_key_first($percents);
					if($winner == $p){
						if(isset($monstersWon[$mName])){
							$monstersWon[$mName]++;
						}
						else{
							$monstersWon[$mName] = 1;
						}
					}
				}
			}

			$results = array();
			foreach ($monstersWon as $mName => $value) {
				$results[$mName] = $value / $monstersPlayed[$mName];
			}
			
			arsort($results);


			foreach ($monstersWon as $mName => $value) {
				echo $mName, ": ", $value, " / ", $monstersPlayed[$mName], "<br>";
			}
			var_dump($results);

			return array_key_first($results);
		}


		function getMinMaxMonster($bd, $p, $type){
			$percents = array();
			$count = array();

			$hunts = $bd->hunt;
			foreach ($hunts as $hunt) {
				if(count($hunt->otomo) == 0){
					$interval = [0, 0, 50, 33.33, 25];
				}
				else{
					$interval = DPS_INTERVAL;
				}
				$numHunters = count($hunt->player);
				foreach ($hunt->monster as $monster) {
					foreach ($monster->player as $player) {
						if($player->name != $p){
							continue;
						}
						
						$mName = strval($monster->name);
						$damage = $player->phys + $player->elem + $player->poison + $player->blast;

						if(isset($percents[$mName])){
							$percents[$mName] = $percents[$mName] + (($damage/$monster->maxHP)*100) - $interval[$numHunters];
							$count[$mName]++;
						}
						else{
							$percents[$mName] = (($damage/$monster->maxHP)*100) - $interval[$numHunters];
							$count[$mName] = 1;
						}
						break;
					}
				}
			}

			foreach ($percents as $mName => $value) {
				$results[$mName] = $value / $count[$mName];
			}

			if($type == "min"){
				asort($results);
			}
			else{
				arsort($results);
			}

			return array_key_first($results);
		}





		//echo "2 players: ", hunterAVG($bd, 2), " | ", hunterAVG($bd, 2) / 2, "<br>";
		//echo "3 players: ", hunterAVG($bd, 3), " | ", hunterAVG($bd, 3) / 3, "<br>";
		//echo "4 players: ", hunterAVG($bd, 4), " | ", hunterAVG($bd, 4) / 4, "<br>";

		$time_start = microtime(true);
		getMostKilledMonster($bd, "SeppNel");
		$time_end = microtime(true);
		echo ($time_end - $time_start);

		echo "<br>";

		$time_start = microtime(true);
		getMostKilledMonsterOp($bd, "SeppNel");
		$time_end = microtime(true);
		echo ($time_end - $time_start);

		//getMaxMonster($bd, "SeppNel");
		//getMinMonster($bd, "SeppNel");

	?>
</div>
</div>
</body>
</html>