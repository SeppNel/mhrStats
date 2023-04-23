<?php
require_once("helpers.php");
$id = intval($_GET["id"]);
$bd = simplexml_load_file("../../bd/mhRise/mhRise.xml");
$hunt = $bd->hunt[$id];
?>

<!DOCTYPE html>
<html>
<head>
	<title>Cacería - Per-Server</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="../../css/mh_style.css">
	<link rel="shortcut icon" type="image/x-icon" href="/.resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<script src="/.resources/js/chart.min.js"></script>
</head>
<body>
	<div class="image-hero-area"></div>
	<div id="container">
		<div id="menu">
			<?php include("/mnt/disk/.resources/php/mhRise/menu.html"); ?>
		</div>
		<div id="content">
			<div class="title">
				<h1><?php 
						$monsters = getMonstersFromHunt($hunt);  
						for ($i=0; $i < count($monsters); $i++) { 
							if($i == count($monsters) - 1){
								echo $monsters[$i];
							}
							else{
								echo $monsters[$i], " - ";
							}
						}
						echo " en ";
						humanTime($hunt->time);
					?>
				</h1>
			</div>
			<div id="fail">
				<?php
					if(intval($hunt->failed)){
						echo '<img src="../../img/mhRise/fail.png">';
					}
				?>
			</div>
			<div id="icons">
				<?php
					foreach ($monsters as $monster) {
						echo '<img src="../../img/mhRise/monsters/', strtolower($monster), '.png">';
					}
				?>
			</div>
			<div id="graph">
				<canvas id="damageGraph" height="100"></canvas>

				<script>
					var width = document.getElementById('graph').clientWidth;
                	var size = Math.round(width / 40);

					<?php
						echo 'var xValues = [';
						$damages = getTotalDamageFromHunt($hunt);
						arsort($damages);
						$i = 1;
						foreach ($damages as $player => $damage) {
							echo '"', $player, '"';
							if($i != count($damages)){
								echo ", ";
							}
							$i++;
						}
						echo "];\n";

						echo 'var yValues = [';
						
						$i = 1;
						foreach ($damages as $player => $damage) {
							echo '"', $damage, '"';
							if($i != count($damages)){
								echo ", ";
							}
							$i++;
						}
						echo '];';
					?>

					var barColors = ["red", "blue", "green", "yellow"];

					new Chart("damageGraph", {
					  type: "bar",
					  data: {
					    labels: xValues,
					    datasets: [{
					      backgroundColor: barColors,
					      data: yValues
					    }]
					  },
					  options: {
					  	indexAxis: 'y',
					    scales: {
				            y: {
				                ticks: { 
				                	color: "white",
				            		font: {
					                    size: size,
					                }}
				            },
				            x: {
				                ticks: { color: "white"}
				            }
				        },
				        plugins: {
				        	legend: {
				        		display: false
				        	}
				        }
				        
					  }
					});
					</script>
			</div>
			<div id="desglose">
				<?php
				$players = getPlayersFromHunt($hunt);
				$damage = getDamageTypeFromHunt($hunt);
				foreach ($players as $player) {
					echo '<div class="desg">';
						echo "<h1>", $player, "</h1>";
						echo "<span>Daño físico: ", $damage[$player]["phys"], "</span><br>";
						echo "<span>Daño elemental: ", $damage[$player]["elem"], "</span><br>";
						echo "<span>Daño veneno: ", round($damage[$player]["poison"], 2), "</span><br>";
						echo "<span>Daño nitro: ", round($damage[$player]["blast"], 2), "</span><br>";
						if(!isOtomo($bd, $player)){
							$carts = getCartsFromHunt($hunt, $player);
							if($carts != -1){
								echo "<span>Muertes: ", $carts, "</span><br>";
							}
						}

					echo '</div>';
				}

				?>
			</div>
		</div>
	</div>
</body>
</html>