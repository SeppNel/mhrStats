<?php
// ((100 - avg(%otomoDamagePX) * playerCount) / playerCount) || (100 - avg(allOtomoDamageinHuntPX) / playerCount)
const DPS_INTERVAL = [0, 0, 40.4729, 27.9414, 20.6517]; // Just use the hunterAVG function from testing.php

// --- HUNTS ---

function getHuntersFromHunt($hunt){
	$pNames = [];
	foreach ($hunt->player as $player) {
		$pNames[strval($player->name)] = true;
	}

	return array_keys($pNames);
}

function getOtomosFromHunt($hunt){
	$oNames = [];
	foreach ($hunt->otomo as $otomo) {
		$oNames[strval($otomo->name)] = true;
	}

	return array_keys($oNames);
}

function getPlayersFromHunt($hunt){
	$h = getHuntersFromHunt($hunt);
	$o = getOtomosFromHunt($hunt);

	return array_merge($h, $o);
}

function getMonstersFromHunt($hunt){
	$mNames = [];
	foreach ($hunt->monster as $monster) {
		$mNames[] = strval($monster->name);
	}

	return $mNames;
}

function getTotalDamageFromHunt($hunt){
	$monsters = $hunt->monster;
	$damage = [];

	foreach ($monsters as $monster) {
		foreach ($monster->player as $player) {
			$name = strval($player->name);
			if(isset($damage[$name])){
				$damage[$name] = $damage[$name] + $player->phys + $player->elem + $player->poison + $player->blast;
			}
			else{
				$damage[$name] = $player->phys + $player->elem + $player->poison + $player->blast;
			}
		}
	}

	return $damage;
}

function getDamageTypeFromHunt($hunt){
	$monsters = $hunt->monster;
	$damage = [];

	$first = true;
	foreach ($monsters as $monster) {
		foreach ($monster->player as $player) {
			$name = strval($player->name);
			if($first){
				$damage[$name]["phys"] = $player->phys;
				$damage[$name]["elem"] = $player->elem;
				$damage[$name]["poison"] = $player->poison;
				$damage[$name]["blast"] = $player->blast;
				$first = false;
			}
			else{
				$damage[$name]["phys"] = $player->phys + $damage[$name]["phys"];
				$damage[$name]["elem"] = $player->elem + $damage[$name]["elem"];
				$damage[$name]["poison"] = $player->poison + $damage[$name]["posion"];
				$damage[$name]["blast"] = $player->blast + $damage[$name]["blast"];
			}
			
		}
	}

	return $damage;
}

function getCartsFromHunt($hunt, $name){
	foreach ($hunt->player as $player) {
		if($player->name == $name){
			return intval($player->carts);
		}
	}
}

// --- HUNTERS ---

function getAllHunters($bd){
	$n = [];
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->player as $player) {
			$n[strval($player->name)] = true;
		}
	}

	return array_keys($n);
}

function avgCarts($bd, $name){
	$c = [];
	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		foreach ($hunt->player as $player) {
			if($player->name == $name){
				if($player->carts != -1){
					array_push($c, intval($player->carts));
				}
					
				break;
			}
		}
	}

	if(count($c) == 0){
		return 0;
	}

	return array_sum($c) / count($c);
}

// --- OTOMOS ---

function getAllOtomos($bd){
	$n = [];
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->otomo as $otomo) {
			$n[strval($otomo->name)] = true;
		}
	}

	return array_keys($n);
}

function isOtomo($bd, $name){
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->otomo as $otomo) {
			if(strval($otomo->name) == $name){
				return true;
			}
		}
	}
	return false;
}

// --- PLAYERS ---

function getAllPlayers($bd){
	$h = getAllHunters($bd);
	$o = getAllOtomos($bd);

	return array_merge($h, $o);
}

function getHuntCount($bd, $p){
	$count = 0;
	foreach ($bd->hunt as $hunt) {
		if(in_array($p, getPlayersFromHunt($hunt))){
			$count++;
		}
	}

	return $count;
}

function countTops1($bd, $p){
	$count = 0;
	$isOtomo = isOtomo($bd, $p);

	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		if(!in_array($p, getPlayersFromHunt($hunt))){
			continue;
		}

		$damages = getTotalDamageFromHunt($hunt);
		arsort($damages);
		if($isOtomo){
			if(array_keys($damages)[count($hunt->player)] == $p){
				$count++;
			}
		}
		else{
			if(array_key_first($damages) == $p){
				$count++;
			}
		}
	}

	return $count;
}

function questCompleteRatio($bd, $p){
	$v = 0;
	$c = 0;
	foreach ($bd->hunt as $hunt) {
		if(in_array($p, getPlayersFromHunt($hunt))){
			if(!intval($hunt->failed)){
				$v++;
			}
			$c++;
		}
	}

	return $v / $c * 100;
}

// --- MONSTERS ---

function getAllMonsters($bd){ //TODO: Optimize by just writing the array myself
	$mNames = [];
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			$mNames[strval($monster->name)] = true;
		}
	}

	return array_keys($mNames);
}

function monsterVictoryPercent($bd, $name){
	$count = 0;
	$fail = 0;

	foreach ($bd->hunt as $hunt) {
		if(count($hunt->monster) != 1){
			continue;
		}

		if(strval($hunt->monster->name) != $name){
			continue;
		}

		$count++;

		if(intval($hunt->failed)){
			$fail++;
		}
	}

	if($count == 0){
		return -1;
	}

	return 100 - ($fail / $count * 100);
}

function huntedMeanTime($bd, $name){
	$count = 0;
	$time = 0;

	foreach ($bd->hunt as $hunt) {
		if(intval($hunt->failed) || count($hunt->monster) != 1){
			continue;
		}

		if(strval($hunt->monster->name) == $name){
			$count++;
			$time += $hunt->time;
		}
	}

	if($count == 0){
		return -1;
	}

	return $time / $count;
}

function monsterHPRange($bd, $name){
	$hp = [9999999, -9999999];

	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			if(strval($monster->name) != $name){
				continue;
			}

			$mHP = intval($monster->maxHP);

			if($mHP < $hp[0]){
				$hp[0] = $mHP;
			}

			if($mHP > $hp[1]){
				$hp[1] = $mHP;
			}
		}
	}

	return $hp;
}

// --- MISC ---
function humanTime($time){
	echo floor($time / 60), ":", sprintf('%02d', $time % 60);
}

?>