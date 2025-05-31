<?php
//used by discord
	putenv('GDFONTPATH=' . realpath($path.'/fonts/'));

	
		$mapPath = $path.'/images/Maps/'.'Map'.$region.'.png';
		if(!file_exists($mapPath)){
			echo "no map	  ";
			$mapPath = $path.'/images/Maps/'.'MapBlank.png';
		}


			$map = imagecreatetruecolor($mapSize['x'], $mapSize['y']);
			imagealphablending($map, true);
			$transparent = imagecolorallocatealpha($map, 0, 0, 0, 127);
			imagefill($map, 0, 0, $transparent);
			imagesavealpha($map, true);
			
			// Create image instances
			//$map = imagecreatefromjpeg($mapPath);			
			$tile = imagecreatefrompng($mapPath);			
			//zoom map
			//$tile = imagescale($tile, imagesx($tile)*3);
			imagecopyresampled($map, $tile, 0, 0, 0, 0, imagesx($map), imagesy($map), imagesx($tile), imagesy($tile));	
			
					
			//$result = imagecopy($map, $tile,0, 0, 0, 0, imagesx($tile), imagesy($tile));		
			
			$white = imagecolorallocate($map, 0xFF, 0xFF, 0xFF);
			$black = imagecolorallocate($map, 0, 0, 0);
			$grey = imagecolorallocate($map, 0xd0, 0xd0, 0xd0);
			
			$mapWidth = imagesx($map);
			$mapHeight = imagesy($map);
			
/*			//test data
			$dynamic = ["mapItems" =>[[
										"teamId" => "NONE",
										"iconType" => 5,
										"x" => 0.21965122,
										"y" => 0.6231655,
										"flags" => 0
									  ]],
							"mapTextItems" => [[
										"text" => "Abandoned Ward",
										"x" => 0.410076,
										"y" => 0.4957782
									  ]],		  
								  "worldExtentsMinX" => -61645.926,
								  "worldExtentsMinY" => -84705.41,
								  "worldExtentsMaxX" => 78413.08,
								  "worldExtentsMaxY" => 82011.05,	   
								];*/
				
			//icons

			
/*		echo "All:<pre>";
		var_dump($mapItems);
		echo "</pre>";		*/
			
			$mapItems = array_merge($dynamic["mapItems"],$static["mapItems"]);
			
			$timerArray = [];
			$iconTypesPresent = [];
			
			foreach($mapItems as $item){
if(!isset($item['name']) && $item['iconType'] == 37)continue;//cheap hack for rockets in static
				if(!isset($icons[$item["iconType"]]))continue;//no icon file
				if(!in_array($item['iconType'], $iconTypesPresent))$iconTypesPresent[] = $item['iconType'];


				//team suffix
				$teamVar = '';
				if($item["teamId"] == 'WARDENS')$teamVar = 'Warden';
				else if($item["teamId"] == 'COLONIALS')$teamVar = 'Colonial';	
				
				if(in_array($item['iconType'],[37]))$teamVar = '';//:(
			
				//homebase icon override
				$iconName = $icons[$item["iconType"]]['name'];
				$iconColor = (isset($icons[$item["iconType"]]['color']) ? $icons[$item["iconType"]]['color']: '');
		
				
				//if(($item["flags"] >> 1) & 0b1)$iconName = $icons[99]['name'];
				
				//build
				//$opacity = ((($item["flags"] >> 2) & 0b1) ? 50 : 100);							
			
				$iconX = map($item["x"], 0, 1, 0, $mapWidth);
				$iconY = map($item["y"], 0, 1, 0, $mapHeight);
				
				//victory town underlay icon
				if($item["flags"] & 0x01){
					
					$icon = imagecreatefrompng($iconPath.'cache/'.$icons[98]['name']./*$teamVar.*/".png");		
					$icon = imagescale($icon, round(imagesx($icon)/2));		
					$iconXc = $iconX - (imagesx($icon) / 2);
					$iconYc = $iconY - (imagesy($icon) / 2);
					
					if($item["flags"] & 0x04)imagefilter($icon, IMG_FILTER_BRIGHTNESS, 50);//build
					$result = imagecopy($map, $icon, $iconXc, $iconYc+4, 0, 0, imagesx($icon), imagesy($icon)/*, $opacity*/);			

				}//victory town underlay icon
				
				//draw icon
				if(file_exists($iconPath.'cache/'.$iconName.$teamVar.".png")){
					$icon = imagecreatefrompng($iconPath.'cache/'.$iconName.$teamVar.".png");	
					$icon = imagescale($icon, round(imagesx($icon)/2));
				}else if(file_exists($iconPath.'cache/'.$iconName.".png")){//lets shade
						$icon = imagecreatefrompng($iconPath.'cache/'.$iconName.".png");
						$icon = imagescale($icon, round(imagesx($icon)/2));
						imagefilter($icon, IMG_FILTER_NEGATE); 
						imagefilter($icon, IMG_FILTER_COLORIZE, 255-$colors[$teamVar][0], 255-$colors[$teamVar][1], 255-$colors[$teamVar][2], 0);
						imagefilter($icon, IMG_FILTER_NEGATE);	
					
				}else echo "Icon Missing: ".$iconName."<br />";
				
				$iconXc = $iconX - (imagesx($icon) / 2);
				$iconYc = $iconY - (imagesy($icon) / 2);							

				if($item["flags"] & 0x04)imagefilter($icon, IMG_FILTER_BRIGHTNESS, 50);//build
				if($item["flags"] & 0x10){//nuke
						imagefilter($icon, IMG_FILTER_NEGATE); 
						imagefilter($icon, IMG_FILTER_COLORIZE, 255-200, 255-50, 255-50, 0);
						imagefilter($icon, IMG_FILTER_NEGATE);						
				}else if($iconColor){
						imagefilter($icon, IMG_FILTER_NEGATE); 
						imagefilter($icon, IMG_FILTER_COLORIZE, 255-$iconColor[0], 255-$iconColor[1], 255-$iconColor[2], 20);
						imagefilter($icon, IMG_FILTER_NEGATE);				
				}
				
				$result = imagecopy($map, $icon, $iconXc, $iconYc, 0, 0, imagesx($icon), imagesy($icon)/*, $opacity*/);
					
				if(!$result)echo "<br />Image Icon Failed: ".$iconName;

				//draw icon
				
				//home base indicator
				//if($item["flags"] & 0x02){
					
				//	$icon = imagecreatefrompng($iconPath.'cache/'.$icons[99]['name'].$teamVar.".png");//no noot
				//	$iconXc = $iconX - (imagesx($icon) / 2);
				//	$iconYc = $iconY - (imagesy($icon) / 2);
					
				//	if($item["flags"] & 0x04 || 1)imagefilter($icon, IMG_FILTER_BRIGHTNESS, 75);//build - forced
					//if(($item["flags"] >> 2) & 0b1 || 1)imagefilter($icon, IMG_FILTER_CONTRAST, -50);//build - forced
				//	$result = imagecopy($map, $icon, $iconXc, $iconYc+6, 0, 0, imagesx($icon), imagesy($icon)/*, $opacity*/);			
					
				//}
				
				//if($item["flags"] & 0x20){//civic center icon
					
				//	$icon = imagecreatefrompng($iconPath.'cache/'.$icons[97]['name'].$teamVar.".png");				
				//	$iconXc = $iconX - (imagesx($icon) / 2);
				//	$iconYc = $iconY - (imagesy($icon) / 2);
					
				//	if($item["flags"] & 0x04 || 1)imagefilter($icon, IMG_FILTER_BRIGHTNESS, 75);//build - forced
					//if(($item["flags"] >> 2) & 0b1 || 1)imagefilter($icon, IMG_FILTER_CONTRAST, -50);//build - forced
				//	$result = imagecopy($map, $icon, $iconXc, $iconYc+6, 0, 0, imagesx($icon), imagesy($icon)/*, $opacity*/);			
					
				//}
				
				
				//held timer text
		//		if(isset($item['timer']) && $item['timer']>0 && in_array($item['iconType'],$capturable)){
			//		$timeHeld = round(($timeStamp - $item['timer'])/3600,0,PHP_ROUND_HALF_DOWN);//hours
			//		$timerText = ($timeHeld <= 24 ? $timeHeld .'h' : round($timeHeld/24,0,PHP_ROUND_HALF_DOWN).'d');
					
		//			$textBox = imagettfbbox(12, 0, 'TypoSlab_demo', $timerText);
		//			$centerX = ceil($iconX - ($textBox[2] / 2));
		//			$centerY = ceil($iconY - ($textBox[3] / 2));					
		//			$timerArray[] = ['timerText' => $timerText, 'centerX' => $centerX, 'centerY' => $centerY, 'iconType' => $item['iconType']];
		//		}
		
				if($icon)imagedestroy($icon);
			}
			
			//print on top
		//	foreach($timerArray as $text){			
				
		//		if($text['iconType'] < 11)$result = imagettfstroketext($map, 14, 0, $text['centerX'], $text['centerY'] + 12, $grey, $black, 'TypoSlab_demo', $text['timerText'], 1);
		//		else $result = imagettfstroketext($map, 12, 0, $text['centerX'], $text['centerY'] + 10, $grey, $black, 'TypoSlab_demo', $text['timerText'], 1);
		//	}
			
			//text
			
/*		echo "Static:<pre>";
		var_dump($static);
		echo "</pre>";		*/			
			foreach($static["mapTextItems"] as $text){//other text
	
				if(isset($text['town']))continue;
				
				$textX = map($text["x"], 0, 1, 0, $mapWidth);
				$textY = map($text["y"], 0, 1, 0, $mapHeight);			
			
				$textBox = imagettfbbox(8, 0, 'TypoSlab_demo', $text['text']);
				$centerX = ceil($textX - ($textBox[2] / 2));
				$centerY = ceil($textY - ($textBox[3] / 2));
			
				$result = imagettfstroketext($map, 8, 0, $centerX, $centerY, $grey, $black, 'TypoSlab_demo', $text['text'], 1);
				
			}			
			foreach($static["mapTextItems"] as $text){//towns text on top

				if(!isset($text['town']))continue;

				//$textX = map($text["x"], 0, 1, 0, $mapWidth);
				//$textY = map($text["y"], 0, 1, 0, $mapHeight);	
				$textX = map($text["townX"], 0, 1, 0, $mapWidth);//write town name uniform under icon
				$textY = map($text["townY"], 0, 1, 0, $mapHeight);							
			
				$textBox = imagettfbbox(8, 0, 'TypoSlab_bold_demo', $text['text']);
				$centerX = ceil($textX - ($textBox[2] / 2));
				$centerY = ceil($textY - ($textBox[3] / 2))+25;				
						
				$result = imagettfstroketext($map, 8, 0, $centerX, $centerY, $white, $black, 'TypoSlab_bold_demo', $text['text'], 1);
				
			}
	
		
			//image title
		//	$result = imagefttext($map, 13, 0, 2, 14, $white, 'LinLibertine_R', $regionName. " ". date(DATE_RFC2822)." foxholestats.com");
			
			
			$result = imagepng($map, $path.'/images/cache/'.strtolower($region).".png", 9, PNG_ALL_FILTERS);
			//save image to disk
			//$result = imagejpeg($map, $path.'/images/cache/'.strtolower($region).".jpg", 90);
		
			//$result = imagejpeg(imagescale($map, 800), $path.'/images/cache/medium/'.strtolower($region).".jpg", 90);	
			
			//$result = imagejpeg(imagescale($map, 550), $path.'/images/cache/thumbs/'.strtolower($region).".jpg", 80);	
		
			//echo "<br />";
			//if($result)echo "Image Generated";
			//else echo "Image Failed";

			imagedestroy($map);
			
			//show image  
			  
			echo "<img style='width:300px;' src='/images/cache/".strtolower($region).".png' />";


	?>