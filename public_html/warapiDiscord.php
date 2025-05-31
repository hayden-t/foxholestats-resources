<?php

include "_settings.php";
foreach(glob($path.'/languages/*.php') as $file) {
     include $file;
}

require $path."/vendor/auroraari/discord-webhooks/src/Client.php";	
 use \DiscordWebhooks\Client as DiscordWebhook;    
 // ```\:warden:```
	
if(file_exists($path."/warapiDiscordServers.php") && file_exists($path."/warapiDiscordEvents.json")){
require($path."/warapiDiscordServers.php");

$discordMessages = json_decode(file_get_contents($path."/warapiDiscordEvents.json"), true);
unlink($path."/warapiDiscordEvents.json");
//discord messages			
	echo "<br /><br />Discord Messages:<br /><br />";
		foreach($discordMessages as $key => $message){
			extract($message);
/*							$discordMessages[] = [
								'region' => $region,
								'' => $regionName,
								'team' => $team,
								'townName' => $townName,
								'action' => $action,
								'timeStamp' => $timeStamp,
								'itemCurrent' => $itemCurrent,
								'day' => $day,
								'players' => $players,
							];			*/
				  if($team == 'SOMEONE' || $team == 'NONE'){$color = 16777215;$auth = $domain.'/images/None_Big.png';}
				  else if($team =='WARDENS'){$color = 6462189;$auth = $domain.'/images/Warden_Big.png';}
				  else if($team =='COLONIALS'){$color = 5555517;$auth = $domain.'/images/Colonial_Big.png';}							
				  $time = date('Y-m-d\TH:i:s.000\Z',$timeStamp);
				  
					$iconName = $icons[$itemCurrent["iconType"]]['name'];
					$iconPath = $path.'/images/MapIcons/';
					$teamVar = '';
					if($itemCurrent["teamId"] == 'WARDENS')$teamVar = 'Warden';
					else if($itemCurrent["teamId"] == 'COLONIALS')$teamVar = 'Colonial';

						
					$icon = $domain.'/images/MapIcons/cache/'.$iconName.$teamVar.".png";
					
					
					//lets add a ring to the map
					/* 
					 $img = imagecreatefromjpeg($path.'/images/cache/'.strtolower($region).".jpg");			
					 $mapWidth = imagesx($img);
					 $mapHeight = imagesy($img);		
					 $iconX = map($itemCurrent["x"], 0, 1, 0, $mapWidth);
					 $iconY = map($itemCurrent["y"], 0, 1, 0, $mapHeight);
					 
					 $circleColor = imagecolorallocate($img, 217, 85, 27);
					 $black = imagecolorallocate($img, 0, 0, 0);
					 $circleThickness = 30;
					 $circlewidth = 140;

					 for ($i=0; $i<$circleThickness; $i++) {
						imageellipse($img, $iconX, $iconY, $circlewidth-$i, $circlewidth-$i, $circleColor);
					 }					 
					 imageellipse($img, $iconX, $iconY, $circlewidth, $circlewidth, $black);
					 imageellipse($img, $iconX, $iconY, $circlewidth-$circleThickness, $circlewidth-$circleThickness, $black);
					 
					 imagejpeg($img, $path.'/images/discord/'.strtolower($region).".jpg");
					 imagedestroy($img);
			*/		 
					 $thumb = $icon;//$domain.'/images/discord/'.strtolower($region).".jpg?time=".$timeStamp.$key;
			
					foreach($discordServers as $server){
					
								if(isset($server[2]) && !in_array($regionName, $server[2]))continue;
								  
								$lang = $server[1]."_";
										
								$actionParts = explode(" ", $action);
								$actionTranslated = constant($lang.$actionParts[0]).(isset($actionParts[1]) ? " ".$actionParts[1] : "" );			
								
								$teamTranslated = constant($lang.$team);
							
								$message = $regionName." - ".$townName. " ".constant($lang."WAS")." ".$actionTranslated." ".constant($lang."BY")." ".$teamTranslated;
								
								$title = $message;
								$desc = '';(isset($server[2])? $server[2]:"");
								
								$fields = array(								
										0 => array('name'=>constant($lang."DAY"),'value'=> strval($day), 'inline'=>true),
										1 => array('name'=>constant($lang."TOTALPLAYERS"),'value'=> strval($players), 'inline'=>true),
									//	2 => array('name'=>'Warden Casualty Rate','value'=>$players, 'inline'=>true),
									//	3 => array('name'=>'Colonial Casualty Rate','value'=>$players, 'inline'=>true),
								);
								
								$auth = ['name' => ''];//array('name'=>$regionName,'icon_url'=>$icon);
								
								echo $lang." " .$regionName." ".$message ."<br />";
								//file_put_contents($path."/log.txt", constant($lang."LOCALE")." ".$message."\r\n",FILE_APPEND);
								
								try{
									$discord = new DiscordWebhook($server[0]);
									$discord->send($title, $desc, $color, $time, $thumb, $fields, $auth);
								} catch (Exception $e) {
									 echo 'Caught exception: ',  $e->getMessage(), "\n";
								}
								//break;
							}
							
				}
echo "<br>finished discord<br>";

}//else echo "discord servers not configured<br>";
?>
