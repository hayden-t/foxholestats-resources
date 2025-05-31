
<?php 

include_once '_settings.php';

$agent = 'War/++UE4+Release-4.24-CL-0 Windows/6.1.7601.1.256.64bit';


//get regions
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_USERAGENT, $agent);

curl_setopt($ch, CURLOPT_URL, $warapiDomain."/internal/worldconquest/maps"); 
$regions = curl_exec($ch);
$regions = json_decode($regions, true);


//get shard
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_USERAGENT, $agent);

curl_setopt($ch, CURLOPT_URL, $warapiDomain."/external/shardStatus"); 
$shard = curl_exec($ch);
$shard = json_decode($shard, true);

$servers = $shard['serverConnectionInfoList'];


echo "Servers: " .count($servers);
//$servers = array_reverse($servers);
$totalNames = $totalNamesWithTimes = array();
$totalWardensQueued = $totalColliesQueued = $totalDays = $totalPlayers = $totalWardens = $totalCollies = $totalUnknown = $totalTownWardens = $totalTownsCollies = $totalTownsUnclaimed = $totalMaxPlayers = $totalPlayersDev = $totalEnlistments = $totalWardenCasualties = $totalCollieCasualties = $totalWardenCasualtiesRate = $totalCollieCasualtiesRate = $totalPop = 0;

$casualties = [];

$serversCount = 0;	

if(!$servers)return;

foreach($servers as $server){

echo "<hr/>server start: ".$server['currentMap']."<br />";

if(!in_array($server['currentMap'], $regions)){
	echo "skip";continue;
}

	
		$days = $enlistments = $townsUnclaimed	= $townsWarden = $townsWarden = $townsCollie = $wardens = $collies = $campaignState = $serverState = $warStatus = $mode = $region ='';


		$serverState = $server['packedServerState'];
		$warStatus = $server['packedWarStatus'];	
		
		
		echo "packedServerState: " . decbin($serverState);
		echo "<br />";
		$days = (($serverState >> 1) & 0b1111111111);//??? 10 places maybe 11 ??
		
		$region = (($serverState >> 19) & 0b1111);
		$mode = ($serverState >> 23) & 0b1111;
		
		switch($region){
			case 1:
				$region = 'Global';
				break;
			case 2:
				$region = 'Americas';
				break;
			case 3:
				$region = 'China';
				break;
			case 4:
				$region = 'Europe';
				break;
			case 5:
				$region = 'Germany';
				break;
			case 6:
				$region = 'Russia';
				break;
			default:
				break;		
		
		}
		
		switch($mode){
			case 0:
				$mode = 'Skirmish';
				break;
			case 1:
				$mode = 'Conquest';
				break;				
			case 2:
				$mode = 'Campaign';
				break;
			case 3:
				$mode = 'Event';
				break;				
			case 4:
				$mode = 'Training';
				break;
			case 5:	
			case 6:
				$mode = 'Home Region';
				break;						
			default:
				break;		
		
		}	
		

		echo "days: ".$days;
		echo "<br />";
		echo "region: ".$region;
		echo "<br />";
		echo "mode: ".$mode;
		echo "<br />";

		echo "<br />";
		
		
		echo "packedWarStatus: " . decbin($warStatus);

		echo "<br />";//12 bits on right side dont know what for //$warStatus >> +1
		$enlistments = ($warStatus >> 13) & 0b1111111111111111;//16
		echo "enlistments: ".$enlistments;
		echo "<br />";
		
		$towns = ($warStatus >> 29) & 0b111111111111111111;//18
		$townsUnclaimed = $towns & 0b111111;
		$townsWarden = ($towns >> 6) & 0b111111;
		$townsCollie = ($towns >> 12) & 0b111111;

		echo "towns c/w/u: " .$townsCollie .'/'.$townsWarden. '/'.$townsUnclaimed;
		echo "<br />";
		
		$players = $warStatus >> 47;
		$wardens = $players & 0b11111111;//6
		$collies = $players >> 8;
		echo "players c/w: " .$collies .'/'.$wardens;
		echo "<br />";
		echo "queues c/w: ". $server['colonialQueueSize'] .'/'.$server['wardenQueueSize'];
		echo "<br />";
	

			
			$totalWardens += $wardens;
			$totalCollies += $collies;
			
			$totalWardensQueued += $server['wardenQueueSize'];
			$totalColliesQueued += $server['colonialQueueSize'];

				$totalTownWardens += $townsWarden;
				$totalTownsCollies += $townsCollie;
				$totalTownsUnclaimed += $townsUnclaimed;

			//	$totalWardenCasualties += $casualtiesWardens;
			//	$totalCollieCasualties += $casualtiesCollies;

				$serversCount++;


	echo "done server";
}



		  
		echo "<hr />done<br /><br />";
		echo "Active Maps: ".$serversCount."<br />";
		echo "normalizedGlobalPopulation: " .$shard['normalizedGlobalPopulation'].'<br />';
		echo "Total Players  c/w: " .$totalCollies .'/'.$totalWardens.'<br />';
		echo "Total Queued  c/w: " .$totalColliesQueued .'/'.$totalWardensQueued.'<br />';
		echo "Queue Warning  c/w: " .intval($shard['bShowColonialQueueWarning']) .'/'.intval($shard['bShowWardenQueueWarning']).'<br />';
		echo "Total Towns  c/w/u: " .$totalTownsCollies .'/'.$totalTownWardens.'/'.$totalTownsUnclaimed.'<br />';
		
		
?>
