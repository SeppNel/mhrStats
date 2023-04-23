<?php
require_once("helpers.php");
$name = strval($_GET["name"]);
$bd = simplexml_load_file("../../bd/mhRise/mhRise.xml");

function monsterCount($bd, $name){
	$count = 0;

	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			if(strval($monster->name) == $name){
				$count++;
			}
		}
	}

	return $count;
}

function monsterFailedCount($bd, $name){
	$count = 0;

	foreach ($bd->hunt as $hunt) {
		if(!intval($hunt->failed) || count($hunt->monster) != 1){
			continue;
		}

		if(strval($hunt->monster->name) == $name){
			$count++;
		}
	}

	return $count;
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
						echo '<img src="/.resources/img/mhRise/monsters/', strtolower($name), '.png">';
					?>
				</div>
				<div id="dataTop">
					<div class="complem">
						<span>Cazado: <?php echo monsterCount($bd, $name); ?></span><br>
						<span>Fails*: <?php echo monsterFailedCount($bd, $name); ?></span><br>
						<span>Victorias*: 
							<?php 
							$per = monsterVictoryPercent($bd, $name);
							if($per == -1){
								echo "No Data";
							}
							else{
								echo round($per, 2), "%";
							}
							?>
						</span><br>
						<span>Vida: <?php $hp = monsterHPRange($bd, $name); echo $hp[0], " - ", $hp[1]; ?></span><br>
						<span>Tiempo*: 
							<?php
							$time = huntedMeanTime($bd, $name);
							if($time == -1){
								echo "No Data";
							}
							else{
								humanTime($time);
							}
							?>
						</span><br>
						<span style="font-size: 0.9rem;">* Solo cuenta misiones en las que es el único objetivo</span>
					</div>
				</div>
			</div>
			<div id="bot">
			</div>
		</div>
	</div>
</body>
</html>