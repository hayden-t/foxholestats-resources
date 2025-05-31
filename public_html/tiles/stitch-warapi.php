<?php

include_once '../_settings.php';

$imagePath = $path.'/tiles/stitched/worldmap_warapi.png';


$canvas = imagecreatetruecolor($worldSize['x'], $worldSize['y']);
imagealphablending($canvas, true);
$transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);

imagefill($canvas, 0, 0, $transparent);
imagesavealpha($canvas, true);


foreach($localIndex as $tileName => $tileData){
	if($tileData['id'] < 20)continue;
	

		$mapPath = $path.'/images/Maps/Map'.$tileName.'.png';
		$map = imagecreatefrompng($mapPath);
		$origin = getWorldTopLeft($tileData["grid"]['x'], $tileData["grid"]['y']);
		imagecopy($canvas, $map, $origin["x"], $origin["y"], 0, 0, imagesx($map), imagesy($map));
}

$white = imagecolorallocate($canvas, 0xFF, 0xFF, 0xFF);

$result = imagepng($canvas, $imagePath );

echo "<img style='width:1000px;' src='/tiles/stitched/worldmap_warapi.png' />";



?>



