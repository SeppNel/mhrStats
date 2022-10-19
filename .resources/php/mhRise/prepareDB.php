<?php
$fl = fopen("/mnt/disk/.resources/bd/mhRise/lock.txt", "w");
flock($fl, LOCK_EX);

require_once("/mnt/disk/.resources/php/lib/json_decode.php");

function getHunterName($fDecoded, $id){
	$hunters = $fDecoded->PLAYERINFO;
	$pets = $fDecoded->OTOMOINFO;
	$players = array_merge($hunters, $pets);
	
	foreach ($players as $player) {
		if($player->id == $id){
			return $player->name;
		}
	}

	return "";
}

function processOtomos($fDecoded){
	$pets = $fDecoded->OTOMOINFO;
	$otomos = array();
	foreach ($pets as $pet) {
		array_push($otomos, $pet->name);
	}

	$n = array();
	$monsters = $fDecoded->MONSTERS;
	foreach ($monsters as $monster) {
		$damages = $monster->damageSources;
		if(is_null($damages)){
			continue;
		}
		foreach ($damages as $damage) {
			$id = $damage->id;
			$name = getHunterName($fDecoded, $id);
			if(in_array($name, $otomos) && !in_array($name, $n)){
				array_push($n, $name);
			}
		}
	}

	return $n;
}

function processHunters($fDecoded){
	$hunters = $fDecoded->PLAYERINFO;
	$h = array();
	foreach ($hunters as $hunter) {
		if(isset($hunter->carts)){
			$h[strval($hunter->name)] = intval($hunter->carts);
		}
		else{
			$h[strval($hunter->name)] = -1;
		}
	}

	return $h;
}

function readFiletoDB($f){
	$rFile = file_get_contents("/mnt/disk/.resources/bd/mhRise/logs/" . $f);
	$fDecoded = json_decode($rFile);

	$date = strtotime(substr($f, 0, 10));
	$time = 0;
	$mId = 0;
	$failed = false;
	$otomos = processOtomos($fDecoded);
	$hunters = processHunters($fDecoded);

	if(count($hunters) == 1){
		return;
	}

	foreach ($fDecoded->MONSTERS as $monster) {
		$time = max($time, $monster->lastTime);

		if(isset($monster->isQuestTarget)){ // Quest Target, 85% for older reports
			$isTarget = boolval($monster->isQuestTarget);
		}
		else{
			$isTarget = $monster->hp->percent < 0.85;
		}

		if(!$isTarget){
			continue;
		}

		if($monster->isInCombat){
			$failed = true;
		}

		if(!is_null($monster->damageSources)){
			foreach ($monster->damageSources as $damage) {
				$id = $damage->id;
				$name = getHunterName($fDecoded, $id);
				if($name == ""){
					continue;
				}
				
				$phys = 0;
				$elem = 0;
				$poison = 0;
				$blast = 0;
				$rider = 0;

				foreach ($damage->counters as $key => $counter) {
					if($key == "barrelbombl" || $key == "BarrelBombLarge"){
						$bombs = $counter->numHit;
					}

					if($key == "marionette"){
						$ail = (array)$counter->ailment;
						if(isset($ail["4"])){ //poison and blast on new version
							$rider = $counter->physical + $counter->elemental + $ail["4"] + $ail["5"];
						}
						else{
							$rider = $counter->physical + $counter->elemental + $counter->ailment[0] + $counter->ailment[1];
						}
						
					}
					else{
						$phys = $phys + $counter->physical;
						$elem = $elem + $counter->elemental;
						$ail = (array)$counter->ailment;
						if(isset($ail["4"])){//poison and blast on new version
							$poison = $poison + $ail["4"];
							$blast = $blast + $ail["5"];
						}
						else{
							$poison = $poison + $counter->ailment[0];
							$blast = $blast + $counter->ailment[1];
						}
						
					}
				}

				$player[$name]["phys"] = $phys;
				$player[$name]["elem"] = $elem;
				$player[$name]["poison"] = $poison;
				$player[$name]["blast"] = $blast;
				$player[$name]["bombs"] = $bombs;
				$player[$name]["rider"] = $rider;
			}
		}
		if(is_null($player)){
			$player = array();
		}
		$monsters[$mId]["name"] = strval($monster->name);
		$monsters[$mId]["damages"] = $player;
		$monsters[$mId]["maxHP"] = $monster->hp->max;
		$mId++;
	}
	
	if(!is_null($monsters)){
		saveBDAsXML($monsters, $date, $time, $failed, $hunters, $otomos);
	}
}

function saveBdAsXml($h, $date, $time, $failed, $hunters, $otomos){
	if (!file_exists("/mnt/disk/.resources/bd/mhRise/mhRise.xml")) {
		$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Hunts></Hunts>");
	}
	else{
		$xml = simplexml_load_file("/mnt/disk/.resources/bd/mhRise/mhRise.xml");
	}

	$hunt = $xml->addChild('hunt');
	$hunt->date = $date;
	$hunt->time = $time;
	if($failed){
		$hunt->failed = 1;
	}
	else{
		$hunt->failed = 0;
	}

	foreach ($hunters as $name => $deaths) {
		$a = $hunt->addChild('player');
		$a->name = $name;
		$a->carts = $deaths;
	}

	foreach ($otomos as $otomo) {
		$c = $hunt->addChild('otomo');
		$c->name = $otomo;
	}

	foreach ($h as $m) {
	    $monster = $hunt->addChild('monster');
	    $monster->name = $m["name"];
	    $monster->maxHP = $m["maxHP"];

	    $players = $m["damages"];

	    foreach ($players as $pName => $player) {
	    	$p = $monster->addChild('player');
	    	$p->name = $pName;
	    	$p->phys = $player["phys"];
	    	$p->elem = $player["elem"];
	    	$p->poison = $player["poison"];
	    	$p->blast = $player["blast"];
	    	$p->bombs = $player["bombs"];
	    	$p->rider = $player["rider"];
	    }
	}

	$xml->saveXML("/mnt/disk/.resources/bd/mhRise/mhRise.xml");
}

function deleteFiles(){
	$files = glob('/mnt/disk/.resources/bd/mhRise/logs/*'); // get all file names
	foreach($files as $file){ // iterate files
	  if(is_file($file)) {
	    unlink($file); // delete file
	  }
	}
}

$files = array_slice(scandir("/mnt/disk/.resources/bd/mhRise/logs"), 2);
foreach ($files as $file) {
	readFiletoDB($file);
}

deleteFiles();

flock($fl, LOCK_UN);
fclose($fl);
?>

