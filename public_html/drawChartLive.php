<?php

if(isset($logsFrom) && isset($logsTo))$sql = "SELECT * FROM logs WHERE mapName = '".$mapName."' AND id BETWEEN ". $logsFrom ." AND ". $logsTo ." ORDER BY id";
else $sql = "SELECT * FROM logs WHERE mapName = '".$mapName."' AND timeStamp BETWEEN ". $from ." AND ". $to ." ORDER BY id";//needed for past 5 days

			$serverLogs = $mysqli->query($sql);
			$first = 1521052211;
			$daysOfData = ceil((($now - $first)/3600)/24);	
			$wardenHours = $collieHours = $zero = 0;
				
				while($row = $serverLogs->fetch_assoc()) {
				
				//in index.php too
			//	if($row['collies'] > $row['wardens'])$imbalance = round((($row['collies']-$row['wardens']) / $row['wardens'])*100);
			//	else $imbalance = -1*round((($row['wardens']-$row['collies']) / $row['collies'])*100);
			//	if(!is_finite($imbalance))$imbalance = 0;

				
				//$accuracy = round(($row['unknown'] / $row['numberOfPlayers'])*100);
			//	if(!is_finite($accuracy))$accuracy = 0;
				
			//	$wardenTime =  round($row['wardenTime'] / $row['wardens']);
			//	$collieTime =  round($row['collieTime'] / $row['collies']);				
				//if($collieTime > $wardenTime)$conquestTotalTimeImbalance = round((($collieTime-$wardenTime) / $wardenTime)*100);
				//else $conquestTotalTimeImbalance = -1*round((($wardenTime-$collieTime) / $collieTime)*100);
			//	if(!is_finite($conquestTotalTimeImbalance))$conquestTotalTimeImbalance = 0;
				
			//	$casualtiesWardensRate = $row['casualtiesWardensRate'];
			//	$casualtiesColliesRate = $row['casualtiesColliesRate'];
			//	if($casualtiesColliesRate > $casualtiesWardensRate)$conquestTotalKillRateImbalance = round((($casualtiesColliesRate-$casualtiesWardensRate) / $casualtiesWardensRate)*100);
			//	else $conquestTotalKillRateImbalance = -1*round((($casualtiesWardensRate-$casualtiesColliesRate) / $casualtiesColliesRate)*100);				if(!is_finite($conquestTotalKillRateImbalance))$conquestTotalKillRateImbalance = 0;							
			
				if(isset($lastTime)){
					$secondsElapsed = $row['timeStamp'] - $lastTime;
					$wardenHours += intval($row['wardens'] * ($secondsElapsed / 3600));
					$collieHours += intval($row['collies'] * ($secondsElapsed / 3600));
				}
				
				$lastTime = $row['timeStamp'];
		
				$dataRow = "["
							."new Date(".$row['timeStamp']*1000 ."),"//0
							.$row['wardens'].","//1					
							.$row['collies'].","	//2
							.$row['townsWarden'].","	//3						
							.$row['townsCollie'].","//4
							.$row['casualtiesWardens'].","//5
							.$row['casualtiesCollies'].","//6
							.$row['casualtiesWardensRate'].","//7
							."'".$row['casualtiesWardensRate']."/hr',"//8
							.$row['casualtiesColliesRate'].","//9
							."'".$row['casualtiesColliesRate']."/hr',"//10
							.$row['numberOfPlayers'].","//11
							//.($row['casualtiesWardens']+$row['casualtiesCollies']).","//12
							.$wardenHours.","
							.$collieHours.","
							.'0,'
							.'0,'
							.($row['wardens'] > $row['collies'] ? $row['wardens'] - $row['collies'] : $zero).","//2
							.($row['wardens'] < $row['collies'] ? $row['collies'] - $row['wardens'] : $zero).","//2

							.'0,'
							.'0'
							."]";
						
								
				
				
					echo "data1.addRow($dataRow);".PHP_EOL;	
				
			//		if($row['timeStamp'] > ($now -(1*24*60*60)))echo "data2.addRow($row);".PHP_EOL;	

				}
				
				
				?>