<?php

include_once '../_settings.php';
proc_nice(20);
set_time_limit(600);//hmm ??
$layers = [
			//'_colors',
			'_roads',
			//'_roads_tiers',
			//'_shadows',
			//'_topography_values',
			//'_tracks',
			//'_rdz'//comment source below
			];

foreach($layers as $layer){
	stitch($layer);
}

function stitch($tileSuffix){

global $worldSize;
global $path;
global $localIndex;
$scale = 2;

$outputPath = '/tiles/stitched/worldmap'.$tileSuffix.'.png';

$canvas = imagecreatetruecolor($worldSize['x']*$scale, $worldSize['y']*$scale);
imagealphablending($canvas, true);
$transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);

imagefill($canvas, 0, 0, $transparent);
imagesavealpha($canvas, true);


foreach($localIndex as $tileName => $tileData){
	if($tileData['id'] < 20)continue;

		$mapPath = $path.'/tiles/bmm/Map'.$tileName.$tileSuffix.'.png';
		//$mapPath = $path.'/tiles/bmm/Decay_layer.png';//
		$map = imagecreatefrompng($mapPath);
		$origin = getWorldTopLeft($tileData["grid"]['x'], $tileData["grid"]['y']);
		imagecopy($canvas, $map, ($origin["x"]*$scale), ($origin["y"]*$scale), 0, 0, imagesx($map), imagesy($map));

}

$white = imagecolorallocate($canvas, 0xFF, 0xFF, 0xFF);

$result = imagepng($canvas, $path.$outputPath);

echo "<img style='width:1000px;' src='".$outputPath."' />";


}
?>

