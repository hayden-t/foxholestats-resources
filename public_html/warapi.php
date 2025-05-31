<html><head>
<meta charset="utf-8">
</head>
<body>
<?php 
error_reporting(E_ALL ^ E_NOTICE);  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', TRUE); // Error logging engine
ini_set('max_execution_time',300);
echo time()."<br>";
echo "time limit:".ini_get('max_execution_time')."<br />"; 
function shutdown()
{
    $a=error_get_last();
    if($a==null)  
        echo "No errors";
    else
         print_r($a);

}
register_shutdown_function('shutdown');



include "_settings.php";
ini_set('error_log', $path.'/errors.txt'); // Logging file path

foreach(glob($path.'/languages/*.php') as $file) {
     include $file;
}

date_default_timezone_set('UTC');
if(!isset($timeStamp))$timeStamp = date('U');
$hour = date('G');//?

$combinedStatus=[];

$colors = ['Warden'=>[72 , 125, 169],'Colonial'=>[101, 135, 94],'Rocket'=>[200,50,50]];

			//make icon cache/shades
			
	foreach($icons as $iconData){
		$iconName = $iconData['name'];
		foreach(['Warden','Colonial','Rocket','','Color'] as $teamVar){
			if(!file_exists($iconPath.'cache/'.$iconName.$teamVar.".png")){//lets shade
				if(file_exists($iconPath.'full/'.$iconName.".png")){//lets shade
					$icon = imagecreatefrompng($iconPath.'full/'.$iconName.".png");
					if($teamVar){
						if($teamVar=='Color'){
							if(isset($iconData['color']))$color = $iconData['color'];
							else continue;
						}else $color = $colors[$teamVar];
						
						imagefilter($icon, IMG_FILTER_NEGATE);
						imagefilter($icon, IMG_FILTER_COLORIZE, 255-$color[0], 255-$color[1], 255-$color[2], 0);
						imagefilter($icon, IMG_FILTER_NEGATE);
					}
					
					imagesavealpha($icon, true);
					imagepng($icon, $iconPath.'cache/'.$iconName.$teamVar.".png");	
				}else echo 'icon missing:'.$iconName."<br/>";
			}	
		}	
	
	}		

$discordMessages=[];


$mysqli = new mysqli($servername, $username, $password, $dbname);//move to settings ?
if(!$mysqli){echo "no db fail";return;}


$ch = curl_init();
function curlRequest($ch, $url, $etag = ''){
	$headers = [];

	curl_setopt($ch, CURLOPT_HEADERFUNCTION,
	  function($curl, $header) use (&$headers)
	  {
		$len = strlen($header);
		$header = explode(':', $header, 2);
		if (count($header) < 2) // ignore invalid headers
		  return $len;

		$name = strtolower(trim($header[0]));
		if (!array_key_exists($name, $headers))
		  $headers[$name] = [trim($header[1])];
		else
		  $headers[$name][] = trim($header[1]);

		return $len;
	  }
	);		
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 		
	curl_setopt($ch, CURLOPT_URL, $url);
	if($etag)curl_setopt($ch, CURLOPT_HTTPHEADER , ['If-None-Match: "'.$etag.'"']);	
	$curl_result = curl_exec($ch);
	
	$info = curl_getinfo($ch);//200 ok, 304 not modified
	$returnCode = $info['http_code'];
	
	$result = [
		'code' => $returnCode,
		'etag' => '',
		'json' => ''
	];
	$error = curl_error($ch);
	if($error)echo "Curl error: ". curl_error($ch)."<br>";	

	if($returnCode == 200){
		$result['etag'] = (isset($headers['etag'])? $headers['etag'][0] : '');
		$result['json'] = $curl_result;
	}

	return $result;
}

function findClosest($items, $target, $filter = false){
			$currentClosest = '';
			$currentDistance = '';
			$key = '';
			//lets find closest
			foreach($items as $k => $item){
				if($filter && !in_array($item['iconType'], $filter))continue;
				$a = ( pow($item['x']-$target['x'],2));
				$b = ( pow($item['y']-$target['y'],2));

				$distance = ( sqrt($a + $b) );
				if($distance < $currentDistance || !$currentClosest){
					$currentClosest = $item;
					$currentDistance = $distance;
					$key = $k;
				}
			}
			return [$key => $currentClosest];
}

function createEvent($mysqli, $regionId, $regionName, $itemCurrent, $itemPast, $townName, $team, $action, $day, $timeStamp, $players){

			global $discordMessages;

			$sql = "INSERT INTO warapi_events_latest VALUES (
				NULL,
				'".$regionId."',
				'".$regionName."',
				'".$itemCurrent['iconType']."',
				'".$mysqli->real_escape_string($townName)."',
				'".$team."',
				'".$action."',
				'".$mysqli->real_escape_string(json_encode($itemPast))."',
				'".$mysqli->real_escape_string(json_encode($itemCurrent))."',		
				'".$itemCurrent['x']."',
				'".$itemCurrent['y']."',						
				'".$day."',
				'".$timeStamp."'
				)";
				
				if(!$mysqli->query($sql))
				  {
					echo("Error description: " . mysqli_error($mysqli));
					echo $sql;
					echo "<br />";
				  }	

			
			$discordMessages[] = [
				//'region' => $region,
				'regionName' => $regionName,
				'team' => $team,
				'townName' => $townName,
				'action' => $action,
				'timeStamp' => $timeStamp,
				'itemCurrent' => $itemCurrent,
				'day' => $day,
				'players' => $players,
			];
							
}


//lets get total players

$players_result = curlRequest($ch, "http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v0001/?format=json&appid=505460");

$players = 0;
if($players_result['json']){
		$players_decoded = json_decode($players_result['json'], true);
		$players = $players_decoded['response']['player_count'];
}
echo "Players: ".$players."<br />";

$warState = curlRequest($ch, $warapiDomain."/api/worldconquest/war");

$regions = [];

if($warState['code'] == 200){

		$warState = json_decode($warState['json'],true);
		
		$sql = "REPLACE INTO warapi_wars ( id,winner,conquestStartTime,conquestEndTime,resistanceStartTime,requiredVictoryTowns,warId) VALUES (
			". $warState['warNumber'].",
			'". $warState['winner']."',
			'". intval($warState['conquestStartTime']/1000)."',
			'". intval($warState['conquestEndTime']/1000)."',
			'".intval( $warState['resistanceStartTime']/1000)."',
			". $warState['requiredVictoryTowns'].",
			'". $warState['warId']."'
			)";

			if(!$mysqli->query($sql))
			  {
				echo("Error description: " . mysqli_error($mysqli));
				echo $sql;
				echo "<br />";
			  }
			  
			  
		//calc global game day	  
		$day = intval((time() - intval($warState['conquestStartTime']/1000))/3600);//irl hour is game day // +1??
		echo 'Game Day: '.$day.'<br />';
		//get regions

		$regions = curlRequest($ch, $warapiDomain."/api/worldconquest/maps");
		
		if($regions['code'] == 200){	
		
			$regions = json_decode($regions['json'], true);
			echo "Regions: " .count($regions)."<br /><br />";	
			
			$offlineRegions = array_diff(array_slice(array_keys($localIndex),14), $regions);
			
			$sql = "DELETE FROM warapi_dynamic WHERE `mapName` in ( '".implode("','", $offlineRegions)."');
					DELETE FROM warapi_static WHERE `mapName` in ( '".implode("','", $offlineRegions)."');
			";
				
			if(!$mysqli->multi_query($sql))
			  {
				echo("Error description: " . mysqli_error($mysqli));
				echo $sql;
				echo "<br />";
			  }else{
				while(mysqli_more_results($mysqli)){mysqli_next_result($mysqli);}
			  }
		
		}else echo "maps endpoint failed";
			  	  
}else echo "wars endpoint failed";
	

foreach($regions as $region){

if($region == 'HomeRegionW')continue;
if($region == 'HomeRegionC')continue;

	echo "<br />".$region."<br />";	
	
	if(!isset($localIndex[$region])){
		echo "<br />REGION MISSING FROM INDEX<br />";	
		continue;
	}

$regionId = $localIndex[$region]['id'];
$regionName = $localIndex[$region]['name'];
	


	
//static	
	//select for compare
		$sql = "SELECT * FROM warapi_static WHERE `mapName` LIKE '".$region."'";
			
		if(!$past = $mysqli->query($sql))
		  {
			echo("Error description: " . mysqli_error($mysqli));
			echo $sql;
			echo "<br />";
		  }
		$pastStatic = mysqli_fetch_assoc($past);

		$staticRequest = curlRequest($ch, $warapiDomain."/api/worldconquest/maps/".$region."/static",($pastStatic ? $pastStatic['etag'] : '') );

		if($staticRequest['code'] == 200){
		
				$static = json_decode($staticRequest['json'], true);

				$sql = "REPLACE INTO warapi_static (regionId, mapName, static,etag,time) VALUES (
					'".$regionId."',
					'".$region."',
					'".$mysqli->real_escape_string(json_encode($static))."',
					".$staticRequest['etag'].",
					'".$timeStamp."'
					)";

					if(!$mysqli->query($sql))
					  {
						echo("Error description: " . mysqli_error($mysqli));
						echo $sql;
						echo "<br />";
					  }
			}else if($staticRequest['code'] == 304){
				echo 'static: no change, skipping<br />';
				$static = json_decode($pastStatic['static'], true);
			}
//static			  
	
//select for compare
	$sql = "SELECT * FROM warapi_dynamic WHERE `mapName` LIKE '".$region."'";
		
	if(!$past = $mysqli->query($sql))
	  {
		echo("Error description: " . mysqli_error($mysqli));
		echo $sql;
		echo "<br />";
	  }
	$pastDynamic = mysqli_fetch_assoc($past);	
//select for compare
		  
//warReport
		
		$reportRequest = curlRequest($ch, $warapiDomain."/api/worldconquest/warReport/".$region,($pastDynamic ? $pastDynamic['reportEtag'] : '') );		
	
	if($reportRequest['code'] == 200){
		
		$reportRequestJson = json_decode($reportRequest['json'], true);
			
		//$day = $reportRequestJson['dayOfWar'];
		$enlistments = $reportRequestJson['totalEnlistments'];
		$wardenCasualties = $reportRequestJson['wardenCasualties'];
		$colonialCasualties = $reportRequestJson['colonialCasualties'];
		
		//handle casualtylog
		if($pastDynamic)$casualtyLog = json_decode($pastDynamic['casualtyLog'], true);
		else $casualtyLog = [];//init
		if(!is_array($casualtyLog))$casualtyLog = [];//init
		
		$newCasualtyLog = [];
		foreach($casualtyLog as $entry){//remove old entries
			if($timeStamp-$entry['time'] <= (60 * 30))$newCasualtyLog[] = $entry;//x min average
		}
		if(count($newCasualtyLog) == 0)$newCasualtyLog[] = end($casualtyLog);//put back in latest if all too old

		if($wardenCasualties && $colonialCasualties){//add current entry/rates
			$newCasualtyLog[] = [
								'time'=>$timeStamp,
								'warden'=>$wardenCasualties,
								'collie'=>$colonialCasualties,							
							];
		}
						
		//calc rates
		$wardenKillRate = $collieKillRate = 0;
		if(count($newCasualtyLog)>1){//do we have a history ?
			$oldestLog = $newCasualtyLog[0];
						
			$wardenKillRate = $wardenCasualties - $oldestLog['warden'];
			$collieKillRate = $colonialCasualties - $oldestLog['collie'];		
		
			$timeDifference = $timeStamp - $oldestLog['time'];
			if($timeDifference){
				$timeBase = $timeDifference / (60*60);//per minute
				$wardenKillRate = round($wardenKillRate / $timeBase);
				$collieKillRate = round($collieKillRate / $timeBase);
				$wardenKillRate = max($wardenKillRate,0);
				$collieKillRate = max($collieKillRate,0);
			}
		}
		
		$casualtyLog = json_encode($newCasualtyLog);//as json
		
		
	}else if($reportRequest['code'] == 304){
			//$day = $pastDynamic['day'];	
			$enlistments = $pastDynamic['totalEnlistments'];
			$wardenCasualties = $pastDynamic['wardenCasualties'];
			$colonialCasualties = $pastDynamic['colonialCasualties'];
			$wardenKillRate = $pastDynamic['wardenRate'];
			$collieKillRate = $pastDynamic['colonialRate'];
			$casualtyLog = $pastDynamic['casualtyLog'];
			$reportRequest['etag'] = $pastDynamic['etag'];//quick hack
	}
//warReport	
	
//dynamic		  

		
		$dynamicRequest = curlRequest($ch, $warapiDomain."/api/worldconquest/maps/".$region."/dynamic/public",($pastDynamic ? $pastDynamic['etag'] : '') );
		
			
	if($dynamicRequest['code'] == 200){
		
		$dynamic = json_decode($dynamicRequest['json'], true);//dynamic
		
		//put nukes first
		$tempMapItems = $dynamic['mapItems'];
		$tempNukes = [];
		foreach($dynamic['mapItems'] as $key => $item){
			if($item['iconType'] == 37){
				$tempNukes[] = $item;//move to end
				unset($tempMapItems[$key]);
			}
		}
		$dynamic['mapItems'] = array_merge($tempNukes,$tempMapItems);
		//array_reverse($dynamic['mapItems']);//reverse to put at start
		//put nukes first
		
		
		$scorchedVictoryTowns = (isset($dynamic['scorchedVictoryTowns'])?$dynamic['scorchedVictoryTowns'] : 0);
				
/*		echo "Dynamic:<pre>";
		var_dump($dynamic);
		echo "</pre>";	*/

		if($pastDynamic)$pastDynamicDecoded = json_decode($pastDynamic['dynamic'], true);
		else $pastDynamicDecoded = ['mapItems'=>[]];
		$changes = $captures = array();
		
		//match Major Markers against town
		foreach($static['mapTextItems'] as $i => $itemCurrent){
			if($itemCurrent['mapMarkerType'] != 'Major')continue;
			$closestTown = findClosest($dynamic['mapItems'], $itemCurrent, $idForRegionMap);
			$key = key($closestTown);
			
			//put label coords into db for region control maps
			$dynamic['mapItems'][$key]['regionData'] = ['x' => $itemCurrent['x'], 'y' => $itemCurrent['y']];
			
			$teamLetter = substr($dynamic['mapItems'][$key]['teamId'],0,1);
			if(isset($captures[$teamLetter]))$captures[$teamLetter]++;//tally majors
			else $captures[$teamLetter] = 1;

		}
		


		foreach($dynamic['mapItems'] as $i => $itemCurrent){
		
			if($itemCurrent['x'] == '-Infinity' || $itemCurrent['x'] == 'Infinity' || $itemCurrent['y'] == '-Infinity' || $itemCurrent['y'] == 'Infinity')continue;//offline
			if(!isset($icons[$itemCurrent['iconType']])){
				mail('hayden@httech.com.au', 'FHS New Icon Detected: '.$itemCurrent['iconType'], $warapiDomain.$region.var_export($itemCurrent, true));
				continue;//unknown  id
			}
			
			if($itemCurrent['iconType'] == 44){//deadharvest
				$dynamic['mapItems'][$i]['teamId'] = $itemCurrent['teamId'] = 'NONE';
				if(isset($captures['Z']))$captures['Z']++;
				else $captures['Z'] = 1;
			}

			$closestLabel = findClosest($static["mapTextItems"], $itemCurrent);
	
			$townName = current($closestLabel)['text'];
			
			//cheap fixes
		/*	discontinued
			//ignore hospitals and shit
			if(in_array($itemCurrent['iconType'], $idForRegionMap)){//(used in maps.php)
				$static['mapTextItems'][key($closestLabel) ]['town'] = true;//set text type as town for later
				$static['mapTextItems'][key($closestLabel) ]['townX'] = $itemCurrent['x'];//save town icon location with town name
				$static['mapTextItems'][key($closestLabel) ]['townY'] = $itemCurrent['y'];
			}
		*/

			$townName .= ' '.$icons[$itemCurrent['iconType']]['title'];//icon type title suffix

			
			//lets put town name into db
			$dynamic['mapItems'][$i]['name'] = $itemCurrent['name'] = $townName;

			
/*		echo "group:<pre>";
		var_dump($itemCurrent);
		var_dump($townName);
		echo "</pre>";*/
		$match = false;	//exists before or new
		//find match
			foreach($pastDynamicDecoded['mapItems'] as $itemPast){
				if($itemCurrent['x'] == $itemPast['x'] && $itemCurrent['y'] == $itemPast['y']){//crude match by location
					//we got a match		
					$match = true;
					//timer on hold time 
					// is a change of teams and not noot/loss reset timer 
					if(( isset($itemPast['wasTeam']) && $itemPast['wasTeam'] != $itemCurrent['teamId'] && $itemCurrent['teamId'] != 'NONE' ) 
													|| ($itemCurrent['iconType'] == 70 && (!isset($itemPast['timer']) || !$itemPast['timer'])))$dynamic['mapItems'][$i]['timer'] = $timeStamp;
					else $dynamic['mapItems'][$i]['timer'] = (isset($itemPast['timer']) ? $itemPast['timer'] : '');//carry on
					

					//last real team (not noot), used by python script and timers
					if($itemCurrent['teamId'] != 'NONE')$dynamic['mapItems'][$i]['wasTeam'] = $itemCurrent['teamId'];//set real team
					else if(isset($itemPast['wasTeam']))$dynamic['mapItems'][$i]['wasTeam'] = $itemPast['wasTeam'];//carry over last real team
					else $dynamic['mapItems'][$i]['wasTeam'] = '';//wasTeam never been set / real team
									
					unset($itemPast['timer'], $itemCurrent['timer'], $itemPast['objectIndex'],$itemPast['objectSerialNumber'],$itemCurrent['objectIndex'],$itemCurrent['objectSerialNumber'],$itemPast['name'], $itemCurrent['name'],$itemPast['regionData'], $itemCurrent['regionData'],$itemPast['wasTeam'], $itemCurrent['wasTeam']);//otherwise will trigger event
					
					//compare dynamics, before and after
					$diff = array_diff_assoc($itemPast, $itemCurrent);
				
					if(!empty($diff)){
						//we get a change
						//add event						
						
						$team = $itemCurrent['teamId'];
		
						if($itemCurrent["flags"] & 0x10){//nuked/scorched
							//if($itemCurrent['iconType'] == 37 && $itemCurrent['teamId'] != 'NONE'){						
							//	$action = "LAUNCHED";
							//	$team = $itemCurrent['teamId'];
							//}else{
								$action = "NUKED";
								if($itemPast['teamId'] == 'WARDENS')$team = 'COLONIALS';
								else if($itemPast['teamId'] == 'COLONIALS')$team = 'WARDENS';
								else 
								$team = "SOMEONE";
							//}
						}/*else if(($itemPast["flags"] | 0x20) && ($itemCurrent["flags"] & 0x20)){
								$action = "UPGRADED Civic";
						}*/else if($itemPast['teamId'] != 'NONE' && $itemCurrent['teamId'] == 'NONE'){//lost
							$team = $itemPast['teamId'];
							$action = "LOST";						
						}else if($itemPast['iconType'] != $itemCurrent['iconType'] && ($itemPast['teamId'] == 'NONE' && $itemCurrent['teamId'] == 'NONE')){//reset
							$action = "RESET";
							$team = "SOMEONE";
						
						}else if($itemCurrent["flags"] & 0x04){//build site
							$action = "CONSTRUCTION";
						}else if($itemPast['teamId'] == $itemCurrent['teamId']){//no team change
						
							if(($itemPast["flags"] & 0x04) && ($itemCurrent["flags"] | 0x04)){//finished or failed construction
								if(in_array($itemCurrent['iconType'],[57,58]))$action = "UPGRADED T".($itemCurrent['iconType']-55);
								//else if(in_array($itemCurrent['iconType'],[59,60]))$action = "BUILT";
								else $action = "TAKEN";
							}
							else if($itemPast['iconType'] != $itemCurrent['iconType']){
								if($itemCurrent['iconType'] == 71 && $itemPast['iconType'] == 70)$action = "NUKED";//target
								else if($itemCurrent['iconType'] == 72 && $itemPast['iconType'] == 37)$action = "BUILT";//pad
								else if($itemCurrent['iconType'] == 37 && $itemPast['iconType'] == 72)$action = "LAUNCHED";//pad
								else $action = 'ICONCHANGED';
							}
							else if((($itemPast["flags"] & 0x08) && ($itemCurrent["flags"] | 0x08)) || (($itemPast["flags"] | 0x08) && ($itemCurrent["flags"] * 0x08)))continue;//flag 8 ?
							else if(($itemPast["flags"] & 0x02) && ($itemCurrent["flags"] | 0x02))continue;//home base transfer from fort
							else $action = 'UNKNOWN';
						
						}else{//team has changed
							$action = "TAKEN";
						}
						
						createEvent($mysqli, $regionId, $regionName, $itemCurrent, $itemPast, $townName, $team, $action, $day, $timeStamp, $players);
							
					}

				}			
				
			}
			
			if(/*in_array($itemCurrent['iconType'],$capturable) && */$enlistments){//only if active

				$teamLetter = substr($itemCurrent['teamId'],0,1);
				//if(!in_array($itemCurrent['iconType'],$dontTally)){//dont tally rockets
				//	if(isset($captures[$teamLetter]))$captures[$teamLetter]++;//tally captures
				//	else $captures[$teamLetter] = 1;
				//}//tally moved
				
				if($itemCurrent["flags"] & 0x01 || in_array($itemCurrent['iconType'], [59, 60, 37, 70, 71, 72, 83])){//only store victory//used by drawState.php
					$capData = ['T' => $teamLetter, 'F' => $itemCurrent["flags"]];
					
					if($itemCurrent['iconType'] == 70)$capData['S'] = $dynamic['mapItems'][$i]['timer'];//target
					
					if(isset($captures[$itemCurrent['iconType']]))$captures[$itemCurrent['iconType']][] = $capData;
					else $captures[$itemCurrent['iconType']] = [$capData];
				}
			}

			if(!$match){//new icon/item reported
				if(in_array($itemCurrent['iconType'], [59, 60, 70, 71, 72, 83])){//can appear mid game
					
					$team = $itemCurrent['teamId'];
					$action = 'BUILT';
					//if($itemCurrent['iconType'] == 48)$dynamic['mapItems'][$i]['timer'] = $timeStamp;
					//if($itemCurrent['iconType'] == 70)$action = 'BUILT';//targeted
					//else 
					$itemPast = [];
					
					createEvent($mysqli, $regionId, $regionName, $itemCurrent, $itemPast, $townName, $team, $action, $day, $timeStamp, $players);
					
				}else{
					echo 'new icon no event: '. $itemCurrent['iconType'].'<br />';
					//mail('hayden@httech.com.au', 'FHS New Icon No Event: '.$itemCurrent['iconType'], var_export($itemCurrent, true));
				}
			
			}			
		}//foreach mapitems		
/*
		foreach($pastDynamicDecoded['mapItems'] as $itemPast){
			$exists = false;
			if($itemPast['iconType'] == 48){
				foreach($dynamic['mapItems'] as $itemCurrent){
					if($itemCurrent['iconType'] == 48 && $itemCurrent['x'] == $itemPast['x'] && $itemCurrent['y'] == $itemPast['y']){
						$exists = true;
						continue;
					}			
				}				
				if(!$exists){
					if(!isset($itemPast['timer']))$itemPast['timer'] = $timestamp;
					$dynamic['mapItems'][] = $itemPast;//copy over bunker
				}
			}
		}
*/
		//draw map files	
		//include "warapiMaps.php";

	//update raw status		  	
		$sql = "REPLACE INTO warapi_dynamic ( regionId, mapName,day,totalPlayers,scorchedVictoryTowns,totalEnlistments,wardenCasualties,colonialCasualties,wardenRate,colonialRate,captures,casualtyLog,dynamic,etag,reportEtag,time) VALUES (
			'".$regionId."',
			'".$region."',
			'".$day."',
			'0',
			'".$scorchedVictoryTowns."',
			'".$enlistments."',
			'".$wardenCasualties."',
			'".$colonialCasualties."',
			'".$wardenKillRate."',
			'".$collieKillRate."',		
			'".json_encode($captures)."',	
			'".$mysqli->real_escape_string($casualtyLog)."',
			'".$mysqli->real_escape_string(json_encode($dynamic))."',
			".$dynamicRequest['etag'].",
			".$reportRequest['etag'].",
			'".$timeStamp."'
			)";
			
			if(!$mysqli->query($sql))
			  {
				echo("Error description: " . mysqli_error($mysqli));
				echo $sql;
				echo "<br />";
			  } 
		
		}else if($dynamicRequest['code'] == 304){
			echo 'dynamic: no change, skipping<br />';
			$scorchedVictoryTowns = $pastDynamic['scorchedVictoryTowns'];			
			$captures = json_decode($pastDynamic['captures'],true);
			
			if($reportRequest['code'] == 200){	
				echo "update warReport in DB<br />";
				
			$sql = "UPDATE warapi_dynamic SET 
			day = '".$day."',
			totalEnlistments = '".$enlistments."',
			wardenCasualties = '".$wardenCasualties."',
			colonialCasualties = '".$colonialCasualties."',
			wardenRate = '".$wardenKillRate."',
			colonialRate = '".$collieKillRate."',
			casualtyLog = '".$mysqli->real_escape_string($casualtyLog)."',
			reportEtag = ".$reportRequest['etag']."
			
			WHERE regionId = '".$regionId."'
			";
			
			if(!$mysqli->query($sql))
			  {
				echo("Error description: " . mysqli_error($mysqli));
				echo $sql;
				echo "<br />";
			  } 		
			
			}
		}
			
			if(isset($combinedStatus[0]))$combinedStatus[0] += $scorchedVictoryTowns;
			else $combinedStatus[0] = $scorchedVictoryTowns;
			
			if(isset($combinedStatus[1]))$combinedStatus[1] += $enlistments;
			else $combinedStatus[1] = $enlistments;
			
			if(isset($combinedStatus[2]))$combinedStatus[2] += $wardenCasualties;
			else $combinedStatus[2] = $wardenCasualties;
			
			if(isset($combinedStatus[3]))$combinedStatus[3] += $colonialCasualties;
			else $combinedStatus[3] = $colonialCasualties;	
			
			if(isset($combinedStatus[4]))$combinedStatus[4] += $wardenKillRate;
			else $combinedStatus[4] = $wardenKillRate;
			
			if(isset($combinedStatus[5]))$combinedStatus[5] += $collieKillRate;
			else $combinedStatus[5] = $collieKillRate;
			
			if(!isset($combinedStatus[8]))$combinedStatus[8] = [];
		if(is_array($captures)){
			foreach($captures as $key => $value){
				if(isset($combinedStatus[8][$key])){
					if(is_numeric($key))$combinedStatus[8][$key] = array_merge($combinedStatus[8][$key],$value);
					else $combinedStatus[8][$key] += $value;
				}else{
					 $combinedStatus[8][$key] = $value;
				}							
			}
		}
			$combinedStatus[9] = (!isset($combinedStatus[9]) || ($day > $combinedStatus[9] && ($colonialCasualties || $wardenCasualties ) ) ? $day : $combinedStatus[9]);



}
echo "<br /><br />done<br />";
curl_close($ch);

		  
	//if($regions)		  
		$sql = "REPLACE INTO warapi_dynamic ( regionId, mapName,day,totalPlayers,scorchedVictoryTowns,totalEnlistments,wardenCasualties,colonialCasualties,wardenRate,colonialRate,captures,casualtyLog,dynamic,etag,reportEtag,time) VALUES (
			0,
			'Conquest_Total',
			'".(isset($combinedStatus[9])?$combinedStatus[9]:0)."',
			'".$players."',
			'".(isset($combinedStatus[0])?$combinedStatus[0]:0)."',
			'".(isset($combinedStatus[1])?$combinedStatus[1]:0)."',
			'".(isset($combinedStatus[2])?$combinedStatus[2]:0)."',
			'".(isset($combinedStatus[3])?$combinedStatus[3]:0)."',
			'".(isset($combinedStatus[4])?$combinedStatus[4]:0)."',
			'".(isset($combinedStatus[5])?$combinedStatus[5]:0)."',
			'".json_encode((isset($combinedStatus[8])?$combinedStatus[8]:[]) )."',
			'',
			'',
			0,
			0,
			".$timeStamp."
			)";
			
			if(!$mysqli->query($sql))
			  {
				echo("Error description: " . mysqli_error($mysqli));
				echo $sql;
				echo "<br />";
			  }	
		echo 	  '<br />Conquest_Total saved<br />';
			  		  
	include 'warapiLog.php';		  
			  
	include 'warapiRotate.php';
	
	//include 'warapiDiscord.php';//saved out for later to not slow down main loop
	if($discordMessages){
		echo "saving discord events file<br />";
		file_put_contents($path.'/warapiDiscordEvents.json', json_encode($discordMessages));
	}else echo "no events<br />";
			
	//include "warapiMapsTile.php";
			  
echo "done warapi";
?>


</body>
</html>
