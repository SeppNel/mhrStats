<?php
// ((100 - avg(%otomoDamagePX) * playerCount) / playerCount) || (100 - avg(allOtomoDamageinHuntPX) / playerCount)
const DPS_INTERVAL = [0, 0, 40.4729, 27.9414, 20.6517]; // Just use the hunterAVG function from testing.php

function getHuntersFromHunt($hunt){
	$pNames = array();
	foreach ($hunt->player as $player) {
		$pNames[strval($player->name)] = true;
	}

	return array_keys($pNames);
}

function getOtomosFromHunt($hunt){
	$oNames = array();
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
	$mNames = array();
	foreach ($hunt->monster as $monster) {
		$mNames[] = strval($monster->name);
	}

	return $mNames;
}

function getTotalDamageFromHunt($hunt){
	$monsters = $hunt->monster;
	$damage = array();

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
	$damage = array();

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

function getAllHunters($bd){
	$n = array();
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->player as $player) {
			$n[strval($player->name)] = true;
		}
	}

	return array_keys($n);
}

function getAllOtomos($bd){
	$n = array();
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->otomo as $otomo) {
			$n[strval($otomo->name)] = true;
		}
	}

	return array_keys($n);
}

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
	$otomos = getAllOtomos($bd);
	$isOtomo = in_array($p, $otomos);

	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		if(!in_array($p, getPlayersFromHunt($hunt))){
			continue;
		}

		$damages = getTotalDamageFromHunt($hunt);
		arsort($damages);
		if($isOtomo){
			foreach ($damages as $player => $nan) {
				if(in_array($player, $otomos)){
					if($p == $player){
						$count++;
					}
					break;
				}
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

function avgCarts($bd, $name){
	$c = array();
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

function isOtomo($bd, $name){
	$otomos = getAllOtomos($bd);
	return in_array($name, $otomos);
}

function getCartsFromHunt($hunt, $name){
	foreach ($hunt->player as $player) {
		if($player->name == $name){
			return intval($player->carts);
		}
	}
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

?>