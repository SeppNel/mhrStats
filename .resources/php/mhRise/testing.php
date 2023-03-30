<?php
include_once("helpers.php");
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

		function countTops1OpUnsafe($bd, $p){
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
					$hunterCount = count($hunt->player);
					if(array_keys($damages)[$hunterCount] == $p){
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

/*
		$time_start = microtime(true);
		countTops1($bd, "Keku");
		$time_end = microtime(true);
		echo ($time_end - $time_start);

		echo "<br>";

		$time_start = microtime(true);
		countTops1OpUnsafe($bd, "Keku");
		$time_end = microtime(true);
		echo ($time_end - $time_start);
*/


		$n = 100;
		$sum1 = 0;
		for ($i=0; $i < $n; $i++) { 
			$time_start = microtime(true);
			countTops1($bd, "Keku");
			$time_end = microtime(true);

			$sum1 += $time_end - $time_start;
		}
		echo ($sum1 / $n) * 1000;

		echo "<br>";

		$sum2 = 0;
		for ($i=0; $i < $n; $i++) { 
			$time_start = microtime(true);
			countTops1OpUnsafe($bd, "Keku");
			$time_end = microtime(true);

			$sum2 += $time_end - $time_start;
		}
		echo ($sum2 / $n) * 1000;


		/*
		$time_start = microtime(true);
		getMostKilledMonster($bd, "SeppNel");
		$time_end = microtime(true);
		echo ($time_end - $time_start);
		*/

	?>
</div>
</div>
</body>
</html>