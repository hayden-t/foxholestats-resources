<?php
if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '119.18.8.148'){
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
}
date_default_timezone_set('UTC');

$settings = file_get_contents(__DIR__."/../foxhole-settings.json");
$settings = json_decode($settings,true);

$servername = $settings["db_server"];
$username = $settings["db_username"];
$password = $settings["db_password"];
$dbname = $settings["db_name"];

$domain = $settings["domain"];
$path = $settings["path"];
$warapiDomain = $settings["warapi"];
$eventPort = $settings["sse_port"];

//dont forget settings in worldmap_overlay.py
//and sse-channel-server.js
	
$capturable = [27,28,29,35,37,45,46,47,56,57,58,59,60,70,71,72,48];//iconIds //used for captures and tally, exception for tally below //todo remove, only used by leaflet
//$dontTally = [28,37,59,60,70,71,72,48];//not used
$idForRegionMap = [5,6,7,29,45,46,47,56,57,58];//used by filter for find closest, but likely can be replaced by icon type/flag

putenv('GDFONTPATH=' . realpath($path.'/fonts/'));

$mapSize = $settings["mapSize"];
$worldSize = $settings["worldSize"];
$gridInterval =$settings["gridInterval"];
$outputImageSize =$settings["outputImageSize"];

$iconPath = $path.'/images/MapIcons/';

$string = file_get_contents($path."/_regions.json");
$localIndex = json_decode($string,true);				  
	
	 
$icons = array(
//	2 => ['name' => 'MapIconFortKeep', 'title' => 'Keep', 'type'=>''],//wg keep
	4 => ['name' => 'MapIconTownHall', 'title' => 'Town Hall', 'type'=>''],//portbase/flag // used in totals
//	5 => ['name' => 'MapIconStaticBase1', 'title' => 'Town Hall T1', 'type'=>''],
//	6 => ['name' => 'MapIconStaticBase2', 'title' => 'Town Hall T2', 'type'=>''],
//	7 => ['name' => 'MapIconStaticBase3', 'title' => 'Town Hall T3', 'type'=>''],
/*	8 => ['name' => 'MapIconForwardBase1', 'type'=>''],
	9 => ['name' => 'MapIconForwardBase2', 'type'=>''],
	10 => ['name' => 'MapIconForwardBase3', 'type'=>''],*/
	11 => ['name' => 'MapIconMedical', 'title' => 'Hospital', 'type'=>''],
	12 => ['name' => 'MapIconVehicle', 'title' => 'Vehicle Factory', 'type'=>''],//, 'color' => [152,48,201]
/*	13 => ['name' => 'MapIconArmory'],
	14 => ['name' => 'MapIconSupplies', 'title' => 'Supplies', 'type'=>''],
	15 => ['name' => 'MapIconWorkshop', 'title' => 'Workshop', 'type'=>''],
	16 => ['name' => 'MapIconManufacturing', 'title' => 'Manufacturing', 'type'=>''],
*/	17 => ['name' => 'MapIconManufacturing', 'title' => 'Refinery', 'type'=>''],//MapIconRefinery
	18 => ['name' => 'MapIconShipyard', 'title' => 'Shipyard', 'type'=>''],
	19 => ['name' => 'MapIconTechCenter', 'title' => 'Engineering Center', 'type'=>''],
	20 => ['name' => 'MapIconSalvage', 'title' => 'Salvage Field', 'color' => [154,122,85], 'type'=>'resource'],
	21 => ['name' => 'MapIconComponents', 'title' => 'Component Field', 'color' => [200,200,200], 'type'=>'resource'],
	22 => ['name' => 'MapIconFuel', 'title' => 'Fuel Field', 'color' => [205,107,35], 'type'=>'resource'],
	23 => ['name' => 'MapIconSulfur', 'title' => 'Sulphur Field', 'color' => [199,199,87], 'type'=>'resource'],
/*	24 => ['name' => 'MapIconWorldMapTent', 'type'=>''],
	25 => ['name' => 'MapIconTravelTent', 'type'=>''],
	26 => ['name' => 'MapIconTrainingArea', 'type'=>''],*/
	27 => ['name' => 'MapIconFortKeep', 'title' => 'Keep', 'type'=>''],//MapIconTownHallNeutral.TGA
	28 => ['name' => 'MapIconObservationTower', 'title' => 'Observation Tower', 'type'=>''],
	29 => ['name' => 'MapIconFort', 'title' => 'Fort', 'type'=>''],
/*	30 => ['name' => 'MapIconTroopShip'],*/
	31 => ['name' => 'MapIconScrapMine', 'title' => 'Salvage Mine', 'color' => [154,122,85], 'type'=>'resource'],
	32 => ['name' => 'MapIconSulfurMine', 'title' => 'Sulphur Mine', 'color' => [199,199,87], 'type'=>'resource'],
	33 => ['name' => 'MapIconStorageFacility', 'title' => 'Storage Facility', 'type'=>''],
	34 => ['name' => 'MapIconFactory', 'title' => 'Factory', 'type'=>''],
	35 => ['name' => 'MapIconSafehouse', 'title' => 'Safehouse', 'type'=>''],
	36 => ['name' => 'MapIconAmmoFactory', 'title' => 'Ammo Factory', 'type'=>''],
	37 => ['name' => 'MapIconRocketSite', 'title' => 'Rocket Launch Site', 'type'=>''],
	38 => ['name' => 'MapIconSalvageMine', 'title' => 'Salvage Mine', 'color' => [154,122,85], 'type'=>'resource'],
	39 => ['name' => 'MapIconConstructionYard', 'title' => 'Construction Yard', 'type'=>''],
	40 => ['name' => 'MapIconComponentMine', 'title' => 'Component Mine', 'color' => [200,200,200], 'type'=>'resource'],	
	//41 => ['name' => 'MapIconOilWell', 'title' => 'Oil Well', 'color' => [205,107,35], 'type'=>''],
	//44 => ['name' => 'MapIconFortCursed', 'title' => 'Cursed Fort', 'type'=>''],
	45 => ['name' => 'MapIconRelicBase', 'title' => 'Relic Base', 'type'=>''],
//	46 => ['name' => 'MapIconRelicBase', 'title' => 'Relic Base', 'type'=>''],
//	47 => ['name' => 'MapIconRelicBase', 'title' => 'Relic Base', 'type'=>''],
	
	51 => ['name' => 'MapIconMassProductionFactory', 'title' => 'Mass Production', 'type'=>''],
	52 => ['name' => 'MapIconSeaport', 'title' => 'Seaport', 'type'=>''],
	53 => ['name' => 'MapIconCoastalGun', 'title' => 'Coastal Gun', 'type'=>''],
	54 => ['name' => 'MapIconSoulFactory', 'title' => 'Font of Balor', 'type'=>''],
	
	56 => ['name' => 'MapIconTownBaseTier1', 'title' => 'Town Base T1', 'type'=>''],
	57 => ['name' => 'MapIconTownBaseTier2', 'title' => 'Town Base T2', 'type'=>''],
	58 => ['name' => 'MapIconTownBaseTier3', 'title' => 'Town Base T3', 'type'=>''],
	
	59 => ['name' => 'MapIconStormCannon', 'title' => 'Storm Cannon', 'type'=>''],	
	60 => ['name' => 'MapIconIntelCenter', 'title' => 'Intel Center', 'type'=>''],
	
	61 => ['name' => 'MapIconCoalField', 'title' => 'Coal Field', 'color' => [75,75,75], 'type'=>'resource'],
	62 => ['name' => 'MapIconOilField', 'title' => 'Oil Field', 'color' => [205,107,35], 'type'=>'resource'],
	
	70 => ['name' => 'MapIconRocketTarget', 'title' => 'Rocket Target', 'type'=>''],
	71 => ['name' => 'MapIconRocketGroundZero', 'title' => 'Rocket Ground Zero', 'type'=>''],
	72 => ['name' => 'MapIconRocketSiteWithRocket', 'title' => 'Rocket Site With Rocket', 'type'=>''],
	
	75 => ['name' => 'MapIconFacilityMineOilRig', 'title' => 'Oil Rig', 'type'=>'resource'],
	
	83 => ['name' => 'MapIconWeatherStation', 'title' => 'Weather Station', 'type'=>''],
	84 => ['name' => 'MapIconMortarHouse', 'title' => 'Mortar House', 'type'=>''],				
	

	96 => ['name' => 'MapIconWaterWell', 'title' => 'Water Well', 'color' => [76,152,179], 'type'=>'resource'],//water well
	97 => ['name' => 'MapIconCivicCenter', 'title' => 'Civic Center', 'type'=>''],//civiccenter
	98 => ['name' => 'MapIconVictory', 'title' => 'Victory Town', 'type'=>''],//underlay circle
	//99 => ['name' => 'MapIconHomeBaseHall', 'title' => 'Home Base Hall'],//star
);

function getWorldTopLeft($gridX, $gridY){

	global $gridInterval;

	$x = $gridInterval['x']*$gridX;
	$y = $gridInterval['y']*$gridY;

return ['x'=> $x, 'y'=> $y];
}

function map($value, $fromLow, $fromHigh, $toLow, $toHigh) {
	$fromRange = $fromHigh - $fromLow;
	$toRange = $toHigh - $toLow;
	$scaleFactor = $toRange / $fromRange;

	// Re-zero the value within the from range
	$tmpValue = $value - $fromLow;
	// Rescale the value to the to range
	$tmpValue *= $scaleFactor;
	// Re-zero back to the to range
	return $tmpValue + $toLow;
}


function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {

	for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
		for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
			$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

   return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
} 
	
?>
