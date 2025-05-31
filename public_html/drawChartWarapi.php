<?php

		$selectTable = ($latestTables ? 'warapi_logs_latest' : 'warapi_logs' );		
		
		if(isset($logsFrom) && isset($logsTo)) $sql = "SELECT * FROM ".$selectTable." WHERE mapName = '".strtolower($mapName)."' AND id BETWEEN ". $logsFrom ." AND ". $logsTo ." ORDER BY id";
		else $sql = "SELECT * FROM ".$selectTable." WHERE mapName = '".strtolower($mapName)."' AND time BETWEEN ". $timeFrom ." AND ". $timeTo ." ORDER BY id";

			$serverLogs = $mysqli->query($sql);		
			$first = 1521052211;
			$daysOfData = ceil((($now - $first)/3600)/24);
			$wardenHours = $collieHours = $zero=0;

				while($row = $serverLogs->fetch_assoc()) {
				
				//in index.php too
				
				
				
			//	$casualtiesWardensRate = $row['wardenCasualties'];
			//	$casualtiesColliesRate = $row['colonialCasualties'];
			//	if($casualtiesColliesRate > $casualtiesWardensRate)$conquestTotalKillRateImbalance = round((($casualtiesColliesRate-$casualtiesWardensRate) / $casualtiesWardensRate)*100);
			//	else $conquestTotalKillRateImbalance = -1*round((($casualtiesWardensRate-$casualtiesColliesRate) / $casualtiesColliesRate)*100);
				$captures = json_decode($row['captures'],true);	
				if(!isset($captures['W']))$captures['W'] = 0;
				if(!isset($captures['C']))$captures['C'] = 0;
				
			//	if(!is_finite($conquestTotalKillRateImbalance))$conquestTotalKillRateImbalance = 0;				
				
				//if($row['wardenRate'] > 3000)$row['wardenRate'] = 0;
				//if($row['colonialRate'] > 3000)$row['colonialRate'] = 0;		
				
				$proto = json_decode($row['aux'],true);
				
				if(isset($lastTime) && $proto){
					$secondsElapsed = $row['time'] - $lastTime;
					$wardenHours += $proto['players']['wardens'] * ($secondsElapsed / 3600);
					$collieHours += $proto['players']['collies'] * ($secondsElapsed / 3600);
				}

				$lastTime = $row['time'];

				$dataRow = "["
							."new Date(".$row['time']*1000 ."),"//0
							.($proto ? $proto['players']['wardens'] : $zero).","//1
							.($proto ? $proto['players']['collies'] : $zero).","//2
							.$captures['W'].","	//3					
							.$captures['C'].","//4
							.$row['wardenCasualties'].","//5
							.$row['colonialCasualties'].","//6
							.$row['wardenRate'].","//7
							."'".$row['wardenRate']."/hr',"//8
							.$row['colonialRate'].","//9
							."'".$row['colonialRate']."/hr',"//10
							.$row['totalPlayers'].","//11
							//.($row['wardenCasualties']+$row['colonialCasualties']).","//12
							.intval($wardenHours).","
							.intval($collieHours).","
							.($proto ? $proto['queued']['wardens'] : $zero).","//2
							.($proto ? $proto['queued']['collies'] : $zero).","//2
							.($proto && $proto['players']['wardens'] > $proto['players']['collies'] ? $proto['players']['wardens'] - $proto['players']['collies'] : $zero).","//2
							.($proto && $proto['players']['wardens'] < $proto['players']['collies'] ? $proto['players']['collies'] - $proto['players']['wardens'] : $zero).","//2
							.($proto ? $proto['warning']['wardens'] : $zero).","//2
							.($proto ? $proto['warning']['collies'] : $zero).","//2
							."]";

				
					echo "data1.addRow($dataRow);".PHP_EOL;	
				

				}
				
				
				?>