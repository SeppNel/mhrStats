<?php
require_once("helpers.php");
$name = strval($_GET["name"]);
$bd = simplexml_load_file("../../bd/mhRise/mhRise.xml");
$isOtomo = isOtomo($bd, $name);

function getFastestHunt($bd, $p){
	$minTime = 999999.99999;
	foreach ($bd->hunt as $hunt) {
		if(!intval($hunt->failed) && floatval($hunt->time) < $minTime && in_array($p, getPlayersFromHunt($hunt))){
			$minTime = $hunt->time;
		}
	}

	return $minTime;
}

function getMedia($bd, $p, $numP){
	$percents = array();
	foreach ($bd->hunt as $hunt) {
		$damage = 0;
		$maxHP = 0;
		if(count($hunt->player) != $numP){
			continue;
		}
		foreach ($hunt->monster as $monster) {
			foreach ($monster->player as $player) {
				if($player->name == $p){
					$damage = $damage + ($player->phys + $player->elem + $player->poison + $player->blast);
					$maxHP = $maxHP + $monster->maxHP;
					break;
				}
			}
		}

		if($maxHP != 0){
			array_push($percents, ($damage/$maxHP)*100);
		}
	}

	if(count($percents) == 0){
		return 0;
	}

	return array_sum($percents) / count($percents);
}

function getTotals($bd, $p){
	$r["phys"] = 0;
	$r["elem"] = 0;
	$r["poison"] = 0;
	$r["blast"] = 0;
	$r["bombs"] = 0;
	$r["carts"] = 0;

	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		foreach ($hunt->monster as $monster) {
			foreach ($monster->player as $player) {
				if($player->name == $p){
					$r["phys"] = $r["phys"] + $player->phys;
					$r["elem"] = $r["elem"] + $player->elem;
					$r["poison"] = $r["poison"] + $player->poison;
					$r["blast"] = $r["blast"] + $player->blast;
					$r["bombs"] = $r["bombs"] + $player->bombs;
					break;
				}
			}
		}

		foreach ($hunt->player as $player) {
			if($player->name == $p && $player->carts != -1){
				$r["carts"] = $r["carts"] + $player->carts;
				break;
			}
		}
	}

	return $r;
}

function getMostKilledMonster($bd, $p){
	$n = array();

	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		$players = getPlayersFromHunt($hunt);
		if(!in_array($p, $players)){
			continue;
		}

		$monsters = $hunt->monster;
		foreach ($monsters as $monster) {
			$mName = strval($monster->name);
			if(isset($n[$mName])){
				$n[$mName]++;
			}
			else{
				$n[$mName] = 1;
			}
		}
	}

	return array_search(max($n), $n);	
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

?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $name; ?> - Per-Server</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="../../css/mh_style.css">
	<link rel="shortcut icon" type="image/x-icon" href="/.resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>
<body>
	<div class="image-hero-area"></div>
	<div id="container">
		<div id="menu">
			<?php include("/mnt/disk/.resources/php/mhRise/menu.html"); ?>
		</div>
		<div id="content">
			<div class="title">
				<h1><?php echo $name; ?></h1>
			</div>
			<div id="top">
				<div id="hunterPhoto">
					<?php
						if(file_exists("/mnt/disk/.resources/img/mhRise/players/" . strtolower($name) . ".webp")){
							echo '<img src="/.resources/img/mhRise/players/', strtolower($name), '.webp">';
						}
						else{
							echo '<img src="/.resources/img/mhRise/players/default.jpg">';
						}
					?>
				</div>
				<div id="dataTop">
					<div class="complem">
						<h1>General</h1>
						<span>Cacerías: <?php echo getHuntCount($bd, $name); ?></span><br>
						<span>Speedrun: <?php $time = getFastestHunt($bd, $name); echo floor($time / 60), ":", sprintf('%02d', $time % 60); ?></span><br>
						<span>Tops 1: <?php echo countTops1($bd, $name); ?></span>
					</div>
					<div class="complem">
						<h1>Medias</h1>
						<span>Media daño 2J: <?php echo round(getMedia($bd, $name, 2), 2), "%"; ?></span><br>
						<span>Media daño 3J: <?php echo round(getMedia($bd, $name, 3), 2), "%"; ?></span><br>
						<span>Media daño 4J: <?php echo round(getMedia($bd, $name, 4), 2), "%"; ?></span><br>
						<span>% Victorias: <?php echo round(questCompleteRatio($bd, $name), 2), "%"; ?></span><br>
						<?php
							if(!$isOtomo){
								echo '<span>Muertes x misión: ', round(avgCarts($bd, $name), 2), '</span>';
							}
						?>
					</div>
					<div class="complem">
						<h1>Totales</h1>
						<?php $totals = getTotals($bd, $name); ?>
						<span>Daño físico: <?php echo $totals["phys"]; ?></span><br>
						<span>Daño elemental: <?php echo $totals["elem"]; ?></span><br>
						<span>Daño veneno: <?php echo round($totals["poison"], 2); ?></span><br>
						<span>Daño nitro: <?php echo round($totals["blast"], 2); ?></span><br>
						<?php
							if(!$isOtomo){
								echo '<span>Bombas usadas: ', $totals["bombs"], '</span><br>';
								echo '<span>Muertes: ', $totals["carts"], '</span>';
							}
						?>
					</div>
				</div>
			</div>
			<div id="bot">
				<div>
					<h1>Monstruo más Cazado</h1>
					<?php
						echo '<img src="/.resources/img/mhRise/monsters/', strtolower(getMostKilledMonster($bd, $name)), '.png">';
					?>
				</div>
				<div>
					<h1>Te abusa</h1>
					<?php
						echo '<img src="/.resources/img/mhRise/monsters/', strtolower(getMinMaxMonster($bd, $name, "min")), '.png">';
					?>
				</div>
				<div>
					<h1>Abusas de</h1>
					<?php
						echo '<img src="/.resources/img/mhRise/monsters/', strtolower(getMinMaxMonster($bd, $name, "max")), '.png">';
					?>
				</div>
			</div>
		</div>
	</div>
</body>
</html>