<?php $time_start = microtime(true); 
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhRise/mhRise.xml");

function getTopDPS($bd){
	$devi = [];
	$huntCount = [];

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
			if(isset($huntCount[$player])){
				$huntCount[$player]++;
			}
			else{
				$huntCount[$player] = 1;
			}

			if(isset($devi[$player])){
				$devi[$player] = $devi[$player] + (($value/$hp) * 100) - $interval[$numHunters];
			}
			else{
				$devi[$player] = (($value/$hp) * 100) - $interval[$numHunters];
			}
		}
	}

	$media = [];
	foreach ($devi as $player => $value) {
		$media[$player] = $value / $huntCount[$player];
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

function getTotalDamageCountFromHunt($hunt, $playerName) {
    $count = 0;

    foreach ($hunt->monster as $monster) {
        foreach ($monster->player as $player) {
            if (strval($player->name) != $playerName) {
                continue;
            }

            $damage = $player->phys + $player->elem + $player->poison + $player->blast;
            $count += $damage;
        }
    }

    return $count;
}

function getTopGato($bd){
	$tops1 = [];
	foreach ($bd->hunt as $hunt) {
		$max = 0;
		$name = "";
		$otomos = getOtomosFromHunt($hunt);

		foreach ($otomos as $otomo) {
			$count = getTotalDamageCountFromHunt($hunt, $otomo);

			if($count > $max){
				$max = $count;
				$name = $otomo;
			}
		}

		if(!isset($tops1[$name])){
			$tops1[$name] = 0;
		}

		$tops1[$name]++;
	}

	arsort($tops1);
	return array_key_first($tops1);
}

function getMostTop1($bd){
	$tops1 = [];
	foreach ($bd->hunt as $hunt) {
		$max = 0;
		$name = "";
		$hunters = getHuntersFromHunt($hunt);

		foreach ($hunters as $hunter) {
			$count = getTotalDamageCountFromHunt($hunt, $hunter);

			if($count > $max){
				$max = $count;
				$name = $hunter;
			}
		}

		if(!isset($tops1[$name])){
			$tops1[$name] = 0;
		}

		$tops1[$name]++;
	}

	arsort($tops1);
	return array_key_first($tops1);
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

function easyHardMonster($bd, $t){
	$fail = [];
	$count = [];

	foreach (getAllMonsters($bd) as $name) {
		$fail[$name] = 0;
		$count[$name] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		if(count($hunt->monster) != 1){
			continue;
		}

		$mName = strval($hunt->monster->name);
		$count[$mName]++;
		if(intval($hunt->failed)){
			$fail[$mName]++;
		}
	}

	foreach ($fail as $name => $fails) {
		if($count[$name] != 0){
			$count[$name] = $fails / $count[$name];
		}
	}

	if($t == "hard"){
		arsort($count);
	}
	else{
		asort($count);
	}

	return array_key_first($count);	
}


function slowestMonster($bd){
	$timesAdded = [];
	$count = [];

	foreach (getAllMonsters($bd) as $name) {
		$timesAdded[$name] = 0;
		$count[$name] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		if(count($hunt->monster) != 1 || intval($hunt->failed)){
			continue;
		}

		$mName = strval($hunt->monster->name);
		$count[$mName]++;
		$timesAdded[$mName] += $hunt->time;
	}

	foreach ($timesAdded as $name => $time) {
		if($count[$name] != 0){
			$count[$name] = $time / $count[$name];
		}
	}

	arsort($count);
	return array_key_first($count);
}

function mostTankyMonster($bd){
	$maxHP = [];

	foreach (getAllMonsters($bd) as $name) {
		$maxHP[$name] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			$mName = strval($hunt->monster->name);
			$mHP = intval($monster->maxHP);

			if($mHP > $maxHP[$mName]){
				$maxHP[$mName] = $mHP;
			}
		}
	}

	arsort($maxHP);
	return array_key_first($maxHP);
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
	<?php $time_start = microtime(true);  include("/mnt/disk/.resources/php/mhRise/menu.html"); ?>
</div>
<div id="content">
	<table id="huntersTable" class="listTable">
		<tr>
			<th>Dato</th><th>Valor</th>
		</tr>
		<tr>
			<td>Top DPS</td><td><?php $time_start = microtime(true); getTopDPS($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Top Consistencia</td><td><?php $time_start = microtime(true); getConsistent($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Top Gato</td><td><?php $time_start = microtime(true); getTopGato($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Propenso a Morir</td><td><?php $time_start = microtime(true); carts($bd, "max"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Alérgico a Morir</td><td><?php $time_start = microtime(true); carts($bd, "min"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Victorioso</td><td><?php $time_start = microtime(true); victoryStar($bd, "max"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Perdedor</td><td><?php $time_start = microtime(true); victoryStar($bd, "min"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Más Tops 1</td><td><?php $time_start = microtime(true); getMostTop1($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Más Elemental</td><td><?php $time_start = microtime(true); getMostDamageType($bd, "elem"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Más Explosivo</td><td><?php $time_start = microtime(true); getMostDamageType($bd, "blast"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Más Tóxico</td><td><?php $time_start = microtime(true); getMostDamageType($bd, "poison"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Monstruo más cazado</td><td><?php $time_start = microtime(true); getMostKilledMonster($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Monstruo más chungo</td><td><?php $time_start = microtime(true); easyHardMonster($bd, "hard"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Monstruo más easy</td><td><?php $time_start = microtime(true); easyHardMonster($bd, "easy"); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Monstruo más lento</td><td><?php $time_start = microtime(true); slowestMonster($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Monstruo más tanque</td><td><?php $time_start = microtime(true); mostTankyMonster($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Dia de mayor vicio</td><td><?php $time_start = microtime(true); getMostVicio($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Misiones Fallidas</td><td><?php $time_start = microtime(true); failQuest($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Tiempo en misiones</td><td><?php $time_start = microtime(true);  timeSpent($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
		<tr>
			<td>Zenis gastados en bombas</td><td><?php $time_start = microtime(true); bombMoney($bd); echo (microtime(true) - $time_start); ?> </td>
		</tr>
	</table>
</div>
</div>
</body>
</html>
