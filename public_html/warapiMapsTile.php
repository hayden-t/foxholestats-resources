<?php

include_once '_settings.php';
set_time_limit(0);
putenv('GDFONTPATH=' . realpath($path.'/fonts/'));

$imagePath = $path.'/../history/videos/'.(isset($warState['warNumber'])? 'WC'.$warState['warNumber']:'').'_worldmap.jpg';

if(!isset($timeStamp) ||(($timeStamp - ($warState['conquestStartTime']/1000)) > (5*60) && !file_exists($imagePath))){//5mins



$canvas = imagecreatetruecolor($worldSize['x'], $worldSize['y']);
imagealphablending($canvas, true);
$transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);

imagefill($canvas, 0, 0, $transparent);
imagesavealpha($canvas, true);

if(!isset($mysqli))$mysqli = new mysqli($servername, $username, $password, $dbname);

$sql = "SELECT * FROM warapi_dynamic WHERE regionId != 0";
$dynamicResult = $mysqli->query($sql);

while($row = mysqli_fetch_assoc($dynamicResult)){

		$tileName = $region = $row['mapName'];
		$tileData = $localIndex[$tileName];		
		echo $region.'<br>';

		$sql = "SELECT * FROM warapi_static WHERE regionId = ".$row['regionId'];
		$static = mysqli_fetch_assoc($mysqli->query($sql));

		
		
		$dynamic = json_decode($row['dynamic'], true);
		$static = json_decode($static['static'], true);
		
		//var_dump($dynamic);
		//var_dump($static);
		
		include "warapiMaps.php";//generate for this



		$mapPath = $path.'/images/cache/'.strtolower($tileName).'.png';
		$map = imagecreatefrompng($mapPath);
		$origin = getWorldTopLeft($tileData["grid"]['x'], $tileData["grid"]['y']);
		imagecopy($canvas, $map, $origin["x"], $origin["y"], 0, 0, imagesx($map), imagesy($map));
		
}

$white = imagecolorallocate($canvas, 0xFF, 0xFF, 0xFF);

$result = imagefttext($canvas, 60, 0, 10, 80, $white, 'LinLibertine_R', (isset($warState['warNumber'])? "WC".$warState['warNumber']:"")." ".date(DATE_RFC2822)." foxholestats.com ".imagesx($canvas)."x".imagesy($canvas)."px");
$result = imagejpeg($canvas, $imagePath , 85);

//echo "<br /><img style='width:1000px;' src='images/history/videos/".(isset($warState['warNumber']) ? 'WC'.$warState['warNumber']:'')."_worldmap.jpg' />";
//moved out of public

}else echo "tile: not 5mins yet<br />";
?>
