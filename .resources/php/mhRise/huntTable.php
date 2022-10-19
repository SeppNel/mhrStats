<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhRise/mhRise.xml");

$fMonster = "0";
$fDate = 0;
$fHunter = "0";
$fOtomo = "0";
$fNum = 0;
$fSort = 1;

if(isset($_POST["fMonster"])){
	$fMonster = $_POST["fMonster"];
}
if(isset($_POST["fDate"])){
	$fDate = $_POST["fDate"];
}
if(isset($_POST["fHunter"])){
	$fHunter = $_POST["fHunter"];
}
if(isset($_POST["fOtomo"])){
	$fOtomo = $_POST["fOtomo"];
}
if(isset($_POST["fNum"])){
	$fNum = $_POST["fNum"];
}
if(isset($_POST["fSort"])){
	$fSort = $_POST["fSort"];
}

$huntArr = $bd->hunt;
$huntCount = count($huntArr);

if($fSort == 1){
	$xmlArray = array();
	foreach ($huntArr as $h) $xmlArray[] = $h;
	$huntArr = array_reverse($xmlArray);
}


echo '<tr>
		<th>Monstruo</th><th>Fecha</th><th>Cazadores</th>
	</tr>';

	
$i = 0;
foreach ($huntArr as $hunt) { // foreach ($bd->hunt as $hunt) {
	if($hunt->date != $fDate && $fDate != 0){
		$i++;
		continue;
	}

	if(count($hunt->player) != $fNum && $fNum != 0){
		$i++;
		continue;
	}
	
	if(!in_array($fHunter, getHuntersFromHunt($hunt)) && $fHunter != "0"){
		$i++;
		continue;
	}

	if(!in_array($fOtomo, getOtomosFromHunt($hunt)) && $fOtomo != "0"){
		$i++;
		continue;
	}

	if(!in_array($fMonster, getMonstersFromHunt($hunt)) && $fMonster != "0"){
		$i++;
		continue;
	}

	echo '
		<tr>
			<td><a href=".resources/php/mhRise/hunt.php?id=';
			if($fSort == 1){
				echo $huntCount - $i - 1;
			}
			else{
				echo $i;
			}
			echo '">';
				$j = 1;
				foreach ($hunt->monster as $monster) {
					echo $monster->name;
					if($j != count($hunt->monster)){
						echo " - ";
					}
					$j++;
				}
	echo 	'</a></td>
			<td>', date("d-m-Y", intval($hunt->date)), '</td>
			<td>';
				$pNames = getPlayersFromHunt($hunt);
				$j = 1;
				foreach ($pNames as $name) {
					if(count($pNames) > 4){
						echo $name, " ";
						if($j % 2 == 0){
							echo "<br>";
						}
					}
					else{
						echo $name, "<br>";
					}
					$j++;
				}
	echo    '</td>
		</tr>
	';
	$i++;
}

?>