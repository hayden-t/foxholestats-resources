<?php
$ip = $_SERVER['REMOTE_ADDR'];

			
$error = '';
$success = '';

if(isset($_GET['a']) && is_numeric($_GET['a'])){//affilitae visit

	$context = stream_context_create(array(
	    'http' => array(
	        'timeout' => 5   // Timeout in seconds
	    )
	));
	$org = '';//file_get_contents('https://ipapi.co/'.$ip.'/org/', 0, $context);

	$affiliateId = $_GET['a'];
	
	$sql = "SELECT * FROM affiliateHits WHERE ip = '".$ip."'" ;		
	$result = $mysqli->query($sql);
	if($result->num_rows){}//$error .= "Affiliate hit already counted";
	else{
	
		$sql = "SELECT * FROM affiliateLinks WHERE id = ".$affiliateId." AND ip !='".$ip."'" ;
		$result = $mysqli->query($sql);
		if($result->num_rows){
				$sql = "INSERT INTO affiliateHits VALUES (NULL, ".$affiliateId.",'".$ip."','".$org."')" ;						
				if($result = $mysqli->query($sql)){				
					//$success .= "Affiliate hit counted";
				}
		
		}else{}//$error .= "Affiliate id does not exist or is affilate";
	}

} 
if(isset($_POST['name'])){//generate link request

    $affiliateName = $_POST['name'];
	$affiliateName = substr($affiliateName,0,15);
	$affiliateName = htmlentities($affiliateName);
	$affiliateName = $mysqli->real_escape_string($affiliateName);
	
	
	$sql = "SELECT * FROM affiliateLinks WHERE ip = '".$ip."'" ;		
	$result = $mysqli->query($sql);
	if($result->num_rows)$error .= "Affiliate Link already registered";
	else{
			$sql = "SELECT * FROM affiliateLinks WHERE name = '".$affiliateName."'" ;		
			$result = $mysqli->query($sql);
			if($result->num_rows)$error .= "Affiliate name already registered";
			else{
				$sql = "INSERT INTO affiliateLinks VALUES (NULL, '".$affiliateName."','".$ip."')" ;						
				if($result = $mysqli->query($sql)){
				
					$success .= "Affiliate link generated. Save and share this link to recieved FHS commander rank:<span style='padding: 20px;display: block;background: black;color: white;font-size: 30px;'>".$domain."/?a=".$mysqli->insert_id."</span>";
				}
			}
		
	}
}

if($error ||  $success){
	echo "<h3 style='padding:20px;'>";
		echo $error;
		echo $success;
	echo "</h3>";
}
?>
