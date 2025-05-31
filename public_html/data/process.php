<?php

include '../_settings.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli($servername, $username, $password, $dbname);

$sql = "SELECT * FROM warapi_logs_latest WHERE id <= 5318841 ORDER BY id ASC ";
$result = $mysqli->query($sql);
//id BETWEEN 3238598 AND 5260885 AND 

while($row = mysqli_fetch_assoc($result)){
	
	
	$captures = json_decode($row['captures'], true);
	//echo $row['captures'].PHP_EOL;
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
			//if(isset($newCaptures[71]))echo json_encode($newCaptures).PHP_EOL;
		}
		
		$sql = "UPDATE warapi_logs_latest SET captures = '" .json_encode($newCaptures). "' WHERE id = ".$row['id'];
		//echo $sql;
		//$mysqli->query($sql);
		//if($newCaptures)echo json_encode($newCaptures).PHP_EOL;

		echo $row['id'] . ' ';
		//break;
	}

}




?>