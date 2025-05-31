<?php


//todo: fix below for logs start end etc
		
			
				$sql = "SELECT * FROM warapi_wars ORDER BY id DESC LIMIT 2";				
				$result = $mysqli->query($sql);
				
				$latestRow = mysqli_fetch_assoc($result);
				$pastRow = mysqli_fetch_assoc($result);

	if($pastRow && ($pastRow['casualties'] == 0)){//need to rotate	
							
				echo "need to rotate<br/>";
				
				$warNum = $latestRow['id']-1;
				$startTime = $latestRow['conquestStartTime'];//new start
				$rollTime = $pastRow['conquestEndTime'];//scrap resistance

				echo "<br />";
			
				//update timers
			
				$sql = "SELECT * FROM warapi_dynamic WHERE regionId != 0";				
				$result = $mysqli->query($sql);
				while($row = mysqli_fetch_assoc($result)){
					$dynamic = json_decode($row['dynamic'], true);
					foreach($dynamic['mapItems'] as $key => $item){
						//if(isset($item['timer']) && $item['timer'] < $startTime)$dynamic['mapItems'][$key]['timer'] = '';		
						unset($dynamic['mapItems'][$key]['timer']);			
					}	
					$sql = "UPDATE warapi_dynamic SET dynamic = '".$mysqli->real_escape_string(json_encode($dynamic))."' WHERE regionId = ".$row['regionId'];				
					if($mysqli->query($sql))echo "reset timer ok<br />";
					else echo "problem update: ".$sql."<br />";
							
				}
				
				//update timers			
				
				$sql = "SELECT * FROM warapi_logs_latest WHERE time < ".$rollTime." ORDER BY id ASC LIMIT 1";				
				$firstLogs = $mysqli->query($sql);
				$firstLogs = mysqli_fetch_assoc($firstLogs);
				
										
				$sql = "SELECT * FROM warapi_logs_latest WHERE time < ".$rollTime." ORDER BY id DESC LIMIT 1";				
				$lastLogs = $mysqli->query($sql);
				$lastLogs = mysqli_fetch_assoc($lastLogs);
				
				$sql = "SELECT * FROM warapi_events_latest WHERE time < ".$rollTime." ORDER BY id ASC LIMIT 1";				
				$firstEvents = $mysqli->query($sql);
				$firstEvents = mysqli_fetch_assoc($firstEvents);
						
				$sql = "SELECT * FROM warapi_events_latest WHERE time < ".$rollTime." ORDER BY id DESC LIMIT 1";				
				$lastEvents = $mysqli->query($sql);
				$lastEvents = mysqli_fetch_assoc($lastEvents);
				
				$sql = "SELECT * FROM warapi_logs_latest WHERE time < ".$rollTime." AND regionId = 0 ORDER BY id DESC LIMIT 1";				
				$lastTotal = $mysqli->query($sql);
				$lastTotal = mysqli_fetch_assoc($lastTotal);	
				
				$casualties = $lastTotal['wardenCasualties'] + $lastTotal['colonialCasualties'];
				
				if($firstLogs){
					$sql = "
						UPDATE warapi_wars SET casualties = ".$casualties.", logsFrom = ".$firstLogs['id'].", logsTo = ".$lastLogs['id'].", eventsFrom = ".$firstEvents['id'].", eventsTo = ".$lastEvents['id']." WHERE id = ".$warNum.";	
					";
				}else{
					$sql = "
						UPDATE warapi_wars SET casualties = -1 WHERE id = ".$warNum.";	
					";
				}
				
				if($firstLogs)$sql .= "INSERT INTO warapi_logs SELECT * FROM warapi_logs_latest WHERE time < ".$rollTime." ORDER BY id ASC; 
										DELETE FROM warapi_logs_latest WHERE 1;";
				if($firstEvents)$sql .= "INSERT INTO warapi_events SELECT * FROM warapi_events_latest WHERE time < ".$rollTime." ORDER BY id ASC;
										DELETE FROM warapi_events_latest WHERE 1; ";	

				if(!$mysqli->multi_query($sql))
					  {
						echo("<br />Rollover Failed: Error description: " . mysqli_error($mysqli));
						echo $sql;
						echo "<br />";
					  }else{
					  
						echo "<br />Rollover SUCCESS";
						if($warState['warNumber'])shell_exec("rm -rf " . $path.'/../history/'.$warState['warNumber']);
					
						}
			
			

			}
?>
