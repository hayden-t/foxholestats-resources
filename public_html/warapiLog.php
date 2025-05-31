<?php
$saveEvery = 30;//minutes
$allCasualties=[];

		$sql = "SELECT * FROM warapi_state WHERE id = 'Conquest_Total'";
			
		if(!$test = $mysqli->query($sql))
		  {
			echo("Error description: " . mysqli_error($mysqli));
			echo $sql;
			echo "<br />";
		  }
		if($test = mysqli_fetch_assoc($test)){
			$lastSave = $test['value'];
			if($timeStamp - $lastSave < $saveEvery * 60)return;
		}//else return;


echo "<br />saving dynamic to log<br />";


		$sql = "SELECT * FROM warapi_dynamic ORDER BY regionId ASC";
			
		if(!$dynamic = $mysqli->query($sql))
		  {
			echo("Error description: " . mysqli_error($mysqli));
			echo $sql;
			echo "<br />";
		  }
		$records = [];
		while($row = mysqli_fetch_assoc($dynamic)){//collate all casualty for conquest total
			if($row['mapName'] != 'Conquest_Total'){
				$allCasualties[$row['regionId']] = $row['wardenRate'] + $row['colonialRate'];	
			}
			$records[] = $row;
		}
	
	
		$proto = [];/*
	//if($domain == 'https://foxholestats.com'){
		include 'warapiProto.php';
		$proto = [	'players' => ['collies' => $totalCollies, 'wardens' => $totalWardens],
					'queued' => ['collies' => $totalColliesQueued, 'wardens' => $totalWardensQueued],
					'warning' => ['collies' => intval($shard['bShowColonialQueueWarning']), 'wardens' => intval($shard['bShowWardenQueueWarning'])],
					'towns' => ['collies' => $totalTownsCollies, 'wardens' => $totalTownWardens, 'noot' => $totalTownsUnclaimed],
				];
	//}
	*/
//".$record['totalEnlistments']."
		foreach($records as $record){
		
			//reprocess captures for storage
			$captures = json_decode($record['captures'], true);
			$newCaptures = [];
			if(is_array($captures)){
				foreach($captures as $id => $icons){
					if(is_array($icons)){
						$w = $c = 0;
						foreach($icons as $icon){
							if(!in_array($id , [37, 59, 71]))continue;//rocket, storm, ground z
							
							if($id == 37){
								if(isset($icon['F']) && $icon['F'] == 16){//only count launched
									if($icon['T'] == 'W')$w++;
									if($icon['T'] == 'C')$c++;
								}						
							}else{					
								if($icon['T'] == 'W')$w++;
								if($icon['T'] == 'C')$c++;
							}
							
						}
						if($w || $c)$newCaptures[$id] = [$w, $c];
						
					}else{
						if($id == 'C' || $id == 'COLONIALS')$newCaptures['C'] = $icons;
						if($id == 'W' || $id == 'WARDENS')$newCaptures['W'] = $icons;
					}
					
				}
			}
			
		
			$sql = "INSERT INTO warapi_logs_latest VALUES (
					NULL,
					'".$record['regionId']."',
					'".$record['mapName']."',
					'".$record['scorchedVictoryTowns']."',
					'0',
					'".$record['wardenCasualties']."',
					'".$record['colonialCasualties']."',
					'".$record['wardenRate']."',
					'".$record['colonialRate']."',
					'".$record['totalPlayers']."',
					'".json_encode($newCaptures)."',
					'".$record['day']."',
					'".$timeStamp."',
					
					'".($record['mapName'] == 'Conquest_Total'? json_encode($proto):'')."'
					)";
					
					if(!$mysqli->query($sql))
					  {
						echo("Error description: " . mysqli_error($mysqli));
						echo $sql;
						echo "<br />";
					  }		
		}
		
		
		$sql = "REPLACE INTO warapi_state (id, value) VALUES ( 'Conquest_Total',".$timeStamp.")";

		if(!$mysqli->query($sql))
		  {
			echo("Error description: " . mysqli_error($mysqli));
			echo $sql;
			echo "<br />";
		  }		
		
		//archive control overlay	
		
		include 'warapiHistory.php';

?>
