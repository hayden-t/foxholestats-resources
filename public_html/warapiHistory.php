<?php	
include_once '_settings.php';

if(isset($_GET['WC']))$warState['warNumber'] = $_GET['WC'];
else{
	if(isset($warState) && isset($warState['code']) && $warState['code'] == 504)return;
	if(isset($warState) && $warState['resistanceStartTime']){echo "resistance";return;}	
}
	if(!file_exists($path.'/images/WorldMapControl.webp')){echo "python map generator no running<br>";return;}
	
		$background = imagecreatetruecolor($outputImageSize['x'], $outputImageSize['y']);
		
		$white = imagecolorallocate($background, 255, 255, 255);		
		$beige = imagecolorallocate($background, 47, 47, 47);	
		
		imagefill($background, 0, 0, $beige);

		$layer0 = imagecreatefromwebp($path.'/images/worldmap_warapi.webp');
		$layer1 = imagecreatefromwebp($path.'/images/WorldMapControl.webp');
		$layer2 = imagecreatefromwebp($path.'/images/WorldMapControl_names.webp');
	
		imagecopy($background, $layer0,  0, 0, 0, 0, $outputImageSize['x'], $outputImageSize['y']);	
		imagecopy($background, $layer1,  0, 0, 0, 0, $outputImageSize['x'], $outputImageSize['y']);
		imagecopy($background, $layer2,  0, 0, 0, 0, $outputImageSize['x'], $outputImageSize['y']);	
		
		$day = (isset($day) ? $day:'');
		$timeStamp = (isset($timeStamp) ? $timeStamp: date('U'));
		
		$length = ceil(($timeStamp - ($warState['conquestStartTime']/1000))/86400);//if warstate

		imagefttext($background, 20, 0, 5, 30, $white, 'LinLibertine_R', date(DATE_RFC2822, $timeStamp).' WC'.($warState['warNumber']));
		if($warState['conquestStartTime'])imagefttext($background, 20, 0, 5, 60, $white, 'LinLibertine_R', 'IRL Day: '.$length .' - Game Day: '.$day);
		//imagefttext($background, 20, 0, 5, 90, $white, 'LinLibertine_R', $timeStamp .' '.$warState['conquestStartTime']);
		imagefttext($background, 20, 0, 5, 90, $white, 'LinLibertine_R', $domain);		
		
		
		if(!file_exists($path.'/../history/'))mkdir($path.'/../history/', 0755);
		if(!file_exists($path.'/../history/videos'))mkdir($path.'/../history/videos', 0755);
		
		$warPath = $path.'/../history/'.$warState['warNumber'];
		if(!file_exists($warPath))mkdir($warPath, 0755);		
		
		if(!isset($_GET['WC']))imagejpeg($background, $warPath.'/WorldMapControl_'.$timeStamp.'.jpg');
		
		$videoPath = $path."/../history/videos/WC".$warState['warNumber'].".mp4";		
		
		if($domain == 'https://foxholestats.com'){//primary shard only
			exec ("/usr/bin/nice /usr/bin/ffmpeg -y -framerate 8 -threads 1 -pattern_type glob -i ".$warPath."'/*.jpg' -vf 'eq=contrast=1:brightness=-0.05:saturation=1,scale=".$outputImageSize['x']."x".$outputImageSize['y']."'  -pix_fmt yuv420p ".$videoPath);
			exec ("/usr/bin/git -C /home/foxholestats/history/ add .");
			exec ("/usr/bin/git -C /home/foxholestats/history/ commit -m video");
			exec ("/usr/bin/git -C /home/foxholestats/history/ push");
		}
		echo "done history<br />";
?>
<!--<img src="<?php echo '/images/history/WorldMapControl_'.$timeStamp.'.jpg' ?>" />-->
