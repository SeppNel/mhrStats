<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhRise/mhRise.xml");

function getTopDPS($bd){
	$devi = array();

	foreach ($bd->hunt as $hunt) {
		if(count($hunt->otomo) == 0){
			$interval = [0, 0, 50, 33.33, 25];
		}
		else{
			$interval = DPS_INTERVAL;
		}

		$hp = 0;
		foreach ($hunt->monster as $monster) {
			$hp = $hp + $monster->maxHP;
		}

		$numHunters = count($hunt->player);
		$damages = getTotalDamageFromHunt($hunt);

		foreach ($damages as $player => $value) {
			if(isset($devi[$player])){
				$devi[$player] = $devi[$player] + (($value/$hp) * 100) - $interval[$numHunters];
			}
			else{
				$devi[$player] = (($value/$hp) * 100) - $interval[$numHunters];
			}
		}
	}

	$media = array();
	foreach ($devi as $player => $value) {
		$media[$player] = $value / getHuntCount($bd, $player);
	}

	arsort($media);
	return array_key_first($media);
}

function getConsistent($bd){
	$ant = array();
	$cons = array();
	$count = array();
	$otomos = getAllOtomos($bd);

	foreach ($bd->hunt as $hunt) {
		if(count($hunt->otomo) == 0){
			$interval = [0, 0, 50, 33.33, 25];
		}
		else{
			$interval = DPS_INTERVAL;
		}

		$hp = 0;
		foreach ($hunt->monster as $monster) {
			$hp = $hp + $monster->maxHP;
		}

		$numHunters = count($hunt->player);
		$damages = getTotalDamageFromHunt($hunt);

		foreach ($damages as $player => $value) {
			if (in_array($player, $otomos)) {
				continue;
			}

			$devi = (($value/$hp) * 100) - $interval[$numHunters];

			if(isset($ant[$player])){
				$cons[$player] = $cons[$player] + abs($ant[$player] - $devi);
				$count[$player]++;
				$ant[$player] = $devi;
			}
			else{
				$cons[$player] = 0;
				$count[$player] = 1;
				$ant[$player] = $devi;
			}
		}
	}

	foreach ($cons as $player => $value) {
		$cons[$player] = $value / $count[$player];
	}

	asort($cons);
	return array_key_first($cons);
}

function getTopGato($bd){ // O(numOtomos * (numCacerias * 2 + 3))
	$otomos = getAllOtomos($bd);
	$max = 0; 
	$name = "";
	foreach ($otomos as $otomo) {
		$top1 = countTops1($bd, $otomo);
		if($top1 > $max){
			$max = $top1;
			$name = $otomo;
		}
	}

	return $name;
}

function getMostTop1($bd){
	$players = getAllPlayers($bd);
	$max = 0;
	$n = "";
	foreach ($players as $p) {
		$t = countTops1($bd, $p);
		if($t > $max){
			$max = $t;
			$n = $p;
		}
	}

	return $n;
}

function getMostDamageType($bd, $type){
	$result = array();
	$players = getAllPlayers($bd);
	foreach ($players as $p) {
		$result[$p] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		$damages = getDamageTypeFromHunt($hunt);
		foreach ($damages as $player => $nan) {
			$result[$player] = $result[$player] + $damages[$player][$type];
		}
	}

	arsort($result);
	return array_key_first($result);
}


function getMostKilledMonster($bd){
	$n = array();

	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
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

function getMostVicio($bd){
	$n = array();

	foreach ($bd->hunt as $hunt) {
		$date = strval($hunt->date);
		if(isset($n[$date])){
			$n[$date]++;
		}
		else{
			$n[$date] = 1;
		}
	}

	return array_search(max($n), $n);
}

function timeSpent($bd){
	$time = 0;
	foreach ($bd->hunt as $hunt) {
		$time = $time + $hunt->time;
	}

	return $time;
}

function bombMoney($bd){
	$c = 0;
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			foreach ($monster->player as $player) {
				 $c = $c + $player->bombs;
			}
		}
	}

	return $c * 518;
}

function carts($bd, $t){
	$h = "";
	$max = 0;
	$min = 999999999;

	$hunters = getAllHunters($bd);
	if($t == "max"){
		foreach ($hunters as $hunter) {
			$avg = avgCarts($bd, $hunter);
			if($avg > $max){
				$max = $avg;
				$h = $hunter;
			}
		}
	}
	else{
		foreach ($hunters as $hunter) {
			$avg = avgCarts($bd, $hunter);
			if($avg < $min){
				$min = $avg;
				$h = $hunter;
			}
		}
	}
	
	return $h;
}

function failQuest($bd){
	$c = 0;
	foreach ($bd->hunt as $hunt) {
		$c = $c + $hunt->failed;
	}

	return $c;
}

function victoryStar($bd, $t){
	$h = "";
	$max = 0;
	$min = 100;

	$players = getAllHunters($bd);
	if($t == "max"){
		foreach ($players as $p) {
			$ratio = questCompleteRatio($bd, $p);
			if($ratio > $max){
				$max = $ratio;
				$h = $p;
			}
		}
	}
	else{
		foreach ($players as $p) {
			$ratio = questCompleteRatio($bd, $p);
			if($ratio < $min){
				$min = $ratio;
				$h = $p;
			}
		}
	}

	return $h;
}

function mostDifficultMonster($bd){
	$m = "";
	$p = 101;

	foreach (getAllMonsters($bd) as $monster) {
		$new = monsterVictoryPercent($bd, $monster);
		if($new == -1){
			continue;
		}

		if($new < $p){
			$p = $new;
			$m = $monster;
		}
	}

	return $m;
}

function easiestMonster($bd){
	$m = "";
	$p = 0;

	foreach (getAllMonsters($bd) as $monster) {
		$new = monsterVictoryPercent($bd, $monster);
		if($new == -1){
			continue;
		}

		if($new > $p){
			$p = $new;
			$m = $monster;
		}
	}

	return $m;
}

function slowestMonster($bd){
	$m = "";
	$t = 0;

	foreach (getAllMonsters($bd) as $monster) {
		$new = huntedMeanTime($bd, $monster);
		if($new > $t){
			$t = $new;
			$m = $monster;
		}
	}

	return $m;
}

function mostTankyMonster($bd){
	$m = "";
	$h = 0;

	foreach (getAllMonsters($bd) as $monster) {
		$new = monsterHPRange($bd, $monster)[1];
		if($new > $h){
			$h = $new;
			$m = $monster;
		}
	}

	return $m;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>MH Stats - Per-Server</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="/.resources/css/mh_style.css">
	<link rel="shortcut icon" type="image/x-icon" href="/.resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>
<body>
<div class="image-hero-area" style="background: url('/.resources/img/mhRise/begimo.webp') no-repeat center center; background-size: cover;"></div>
<div id="container">
<div id="menu">
	<?php include("/mnt/disk/.resources/php/mhRise/menu.html"); ?>
</div>
<div id="content">
	<table id="huntersTable" class="listTable">
		<tr>
			<th>Dato</th><th>Valor</th>
		</tr>
		<tr>
			<td>Top DPS</td><td><?php echo getTopDPS($bd); ?></td>
		</tr>
		<tr>
			<td>Top Consistencia</td><td><?php echo getConsistent($bd); ?></td>
		</tr>
		<tr>
			<td>Top Gato</td><td><?php echo getTopGato($bd); ?></td>
		</tr>
		<tr>
			<td>Propenso a Morir</td><td><?php echo carts($bd, "max"); ?></td>
		</tr>
		<tr>
			<td>Alérgico a Morir</td><td><?php echo carts($bd, "min"); ?></td>
		</tr>
		<tr>
			<td>Victorioso</td><td><?php echo victoryStar($bd, "max"); ?></td>
		</tr>
		<tr>
			<td>Perdedor</td><td><?php echo victoryStar($bd, "min"); ?></td>
		</tr>
		<tr>
			<td>Más Tops 1</td><td><?php echo getMostTop1($bd); ?></td>
		</tr>
		<tr>
			<td>Más Elemental</td><td><?php echo getMostDamageType($bd, "elem"); ?></td>
		</tr>
		<tr>
			<td>Más Explosivo</td><td><?php echo getMostDamageType($bd, "blast"); ?></td>
		</tr>
		<tr>
			<td>Más Tóxico</td><td><?php echo getMostDamageType($bd, "poison"); ?></td>
		</tr>
		<tr>
			<td>Monstruo más cazado</td><td><?php echo getMostKilledMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Monstruo más chungo</td><td><?php echo mostDifficultMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Monstruo más easy</td><td><?php echo easiestMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Monstruo más lento</td><td><?php echo slowestMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Monstruo más tanque</td><td><?php echo mostTankyMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Dia de mayor vicio</td><td><?php echo date("d-m-Y", intval(getMostVicio($bd))); ?></td>
		</tr>
		<tr>
			<td>Misiones Fallidas</td><td><?php echo failQuest($bd); ?></td>
		</tr>
		<tr>
			<td>Tiempo en misiones</td><td><?php $time = timeSpent($bd); echo floor($time/3600), ":", floor(($time / 60) % 60), ":", $time%60; ?></td>
		</tr>
		<tr>
			<td>Zenis gastados en bombas</td><td><?php echo bombMoney($bd), "z"; ?></td>
		</tr>
	</table>
</div>
</div>
</body>
</html>
