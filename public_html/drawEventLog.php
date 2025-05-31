<?php
//get events
		error_reporting(1);		
	
		if($latestTables){
			$selectTable = 'warapi_events_latest';
			$where = " time BETWEEN ". $timeFrom ." AND ". $timeTo;
		}else{
			$selectTable = 'warapi_events';
			$where = " id BETWEEN ". $eventsFrom ." AND ". $eventsTo;
		}

		$sql = "SELECT * FROM ".$selectTable." WHERE ".($serverState['regionId'] ? "regionId = '".$serverState['regionId']."' AND " : ""). $where ." ORDER BY id DESC";
		
		
	
		$events = $mysqli->query($sql);
		
		$flags = [
			['name'=>'English', 'code'=>'EN'],
			['name'=>'Spanish', 'code'=>'ES'],
			['name'=>'Portugese', 'code'=>'PT'],
			['name'=>'Russian', 'code'=>'RU'],
			['name'=>'French', 'code'=>'FR'],
			['name'=>'Turkish', 'code'=>'TR'],
			['name'=>'German', 'code'=>'DE'],
			['name'=>'Netherlands', 'code'=>'NL'],
			['name'=>'Italian', 'code'=>'IT'],
			['name'=>'Greek', 'code'=>'EL'],
			['name'=>'Icelandic', 'code'=>'IS'],
			['name'=>'Latvian', 'code'=>'LV'],
			['name'=>'Hungarian', 'code'=>'HU'],
			['name'=>'Korean', 'code'=>'KO'],
			['name'=>'Chinese', 'code'=>'CN'],
			['name'=>'Polish', 'code'=>'PL'],
			['name'=>'Danish', 'code'=>'DA'],
			['name'=>'Arabic', 'code'=>'AR'],
        	['name'=>'Slovak', 'code'=>'SK'],
        	['name'=>'Ukraine', 'code'=>'UK'],
		];
		
		
		shuffle($flags);
		
if(isset($_GET['stream'])){
	echo "<style>";
		echo ".eventLog{";
			if(isset($_GET['font']))echo "font-family:'".$_GET['font']."';";
			if(isset($_GET['color']))echo "color:".$_GET['color'].";";
		echo "}";
echo "</style>";
} ?>
	


	<div class="eventLog" style="">
		<a name="events"></a>
		
		<div class='eventHeader'>
			<span style="font-size:16px;">Live Event Log - UTC<span class="offset"></span></span>
			
			<br />
			
		<?php foreach($flags as $flag){ ?>	
			<span class="flag"><a title="<?php echo $flag['name']; ?>" href="?lang=<?php echo $flag['code']; ?>#events"><img title="<?php echo $flag['name']; ?>" alt="<?php echo $flag['name']; ?>" src="languages/icons/<?php echo $flag['code']; ?>.png" /></a></span>
		<?php } ?>
		</div>
		
		<ul>
			
		<?php
		
			foreach(glob($path.'/languages/*.php') as $file) {
				 require $file;
			}
			$lang = $language."_";
			

		while ($event = $events->fetch_assoc()) {
		
			$latestEventId = (!isset($latestEventId) ? $event['id'] : $latestEventId);
if(isset($_GET['stream']))continue;		
		
			$time =  "<span style='font-weight:normal;' class='time'>". $event['time'] ."</span> ";
			
		//	$matches = preg_replace('/(?<!^)([A-Z])/', ' \\1', $event['mapName']);
		//temp
		//	$matches = implode(" ", $matches);
			
			
			$region = "<b>".$event['mapName']."</b> - ";			
			
			switch($event['action']) {
			  case 'NUKED':
					$class = 'rocketEvent';
				break;
			  case 'LAUNCHED':
					$class = 'rocketEvent';
				break;
			  case 'BUILT':
					if($event['iconType'] == 70)$class = 'rocketEvent';
				break;
			  case 'RESET':
			//  case 'TAKEN':
					$class = 'resetEvent';
				break;						
			  default:
					$class = '';
			} 

			
			$day = ($event['day'] ? ' Game '. constant($lang."DAY") ." " . $event['day'].", " : '' );									
			
			$actionParts = explode(" ", $event['action']);
			$actionTranslated = constant($lang.$actionParts[0]).(isset($actionParts[1]) ? " ".$actionParts[1] : "" );			
			
			$teamTranslated = constant($lang.$event['team']);
			
			$teamVar = '';
			if(in_array($event['action'],['TAKEN','UPGRADED T1','UPGRADED T2','UPGRADED T3','CONSTRUCTION', 'BUILT'])){
					if($event["team"] == 'WARDENS')$teamVar = 'Warden';
					else if($event["team"] == 'COLONIALS')$teamVar = 'Colonial';			
			}
			$icon = ($event['iconType'] ? "<img src='images/MapIcons/cache/".$icons[$event['iconType']]['name'].$teamVar.".png' />" :"");
			
		
			echo "<li data-iconType='".$event['iconType']."' class='".$class."' title='[".$event['id']."]'>"
					.$icon.$region." <b>".$event['town']."</b>"." "
					.constant($lang."WAS")." "
					."<b>" .$actionTranslated."</b> "
					.constant($lang."BY")
					." <b class='".$event['team']."'>".$teamTranslated. "</b> -"
					.$day
					." ".$time
				."</li>";
			
			
		}

	?>
		</ul>
		<span class="eventFooter" style="color:#ff7e40;">You can get this event log live in your discord server or even obs/twitch stream translated into any language !! (join Discord via link top of page)</span>

	</div>


	<script>
		var localTime = new Date();
		var tzOffset = localTime.getTimezoneOffset();
		
		$('.eventLog .offset').html((tzOffset < 0 ? "+":"")+ (-tzOffset/60));
		
		
		function formatTime(time){//time in seconds
			var time = new Date(time*1000 );
			var options1 = {hour: 'numeric',minute: 'numeric',hour12: true };
			var options2 = {month: 'long', day: 'numeric', timeZoneName: 'short' };		
			return time.toLocaleTimeString('<?php echo constant($lang."LOCALE_JS"); ?>', options1) +", "+ time.toLocaleDateString('<?php echo constant($lang."LOCALE_JS"); ?>', options2)
		}
		function strip(html)
		{
		   var tmp = document.createElement("DIV");
		   tmp.innerHTML = html;
		   return tmp.textContent || tmp.innerText || "";
		}
		
		$('.eventLog .time').each(function(){					
			$(this).html(formatTime($(this).html()));
		});				
			
			
	//live SSE event log
	
			var jsIcons = JSON.parse('<?php echo json_encode($icons); ?>');
			
			var jsLangStrings = JSON.parse('<?php echo json_encode(get_defined_constants(true)['user']); ?>');
			$.i18n.load(jsLangStrings);
			
		<?php if($latestTables && $latestEventId){
			if(isset($_GET['stream']))$latestEventId=$latestEventId-5;								
		 ?>		
			
			var eventChannel = new window.EventSource('<?php echo $domain; ?>:<?php echo $eventPort; ?>/channel/eventlog?lastEventId=<?php echo $latestEventId; ?>');	

			eventChannel.addEventListener('event', function (e) {

				//console.log(e.data);
				
				var event = JSON.parse(e.data);
				
				<?php if($serverState['regionId']){?>
					if(event.regionId !=  <?php echo $serverState['regionId']; ?>)return;
				<?php } ?>

					
					switch(event.action) {
					  case 'NUKED':
							var eClass = 'rocketEvent';
						break;
					  case 'LAUNCHED':
							var eClass = 'rocketEvent';
						break;
					  case 'BUILT':
							if(event.iconType == 70)var eClass = 'rocketEvent';
						break;
					  case 'RESET':
					 // case 'TAKEN':  
							var eClass = 'resetEvent';
						break;											
					  default:
							var eClass = '';
					} 
					
					
					if(event.action == 'LAUNCHED' || (event.action == 'BUILT' && event.iconType == 70)){
						var audio = new Audio('/Starcraft.mp3');
						audio.play();
					}
					
					var teamVar = '';
					if(event.action == 'TAKEN' || event.action == 'UPGRADED T1' || event.action == 'UPGRADED T2' || event.action == 'UPGRADED T3' || event.action == 'CONSTRUCTION' || event.action == 'BUILT'){
						if(event.team == 'WARDENS')teamVar = 'Warden';
						else if(event.team == 'COLONIALS')teamVar = 'Colonial';
					}
					var actionTrans = '';//not pretty but hey
					if(event.action == 'UPGRADED T1')actionTrans =  $.i18n._('<?php echo $lang; ?>'+'UPGRADED')+ ' T1';
					else if(event.action == 'UPGRADED T2')actionTrans =  $.i18n._('<?php echo $lang; ?>'+'UPGRADED')+ ' T2';
					else if(event.action == 'UPGRADED T3')actionTrans =  $.i18n._('<?php echo $lang; ?>'+'UPGRADED')+ ' T3';
					else if(event.action == 'UPGRADED Civic')actionTrans =  $.i18n._('<?php echo $lang; ?>'+'UPGRADED')+ ' Civic';
					else actionTrans =  $.i18n._('<?php echo $lang; ?>'+event.action);
				
					var html = "<li data-iconType='"+event.iconType+"' title='"+event.id+"' class='"+eClass+"'>"
							+"<img src='images/MapIcons/cache/"+jsIcons[event.iconType].name+teamVar+".png' />"
							+ "<b>"+event.mapName+ " - "+event.town+"</b> "
							+ $.i18n._('<?php echo $lang; ?>'+'WAS')
							+" <b>"	+ actionTrans + "</b> "
							+ $.i18n._('<?php echo $lang; ?>'+'BY')
							+" <b class='"+event.team+"'>"+ $.i18n._('<?php echo $lang; ?>'+event.team) + "</b> - "
							+ $.i18n._('<?php echo $lang; ?>'+'DAY')+ " " + event.day 
							+ " " +formatTime(event.time)+ "</li>";
		<?php if(!isset($_GET['stream'])) { ?>					
					$(html).prependTo('.eventLog ul').css('background','#5f5f5f').animate({backgroundColor: "transparent"},60000*5);					
			//browser notification		
					Push.create(strip(html), {
						body: strip(html),
						icon: "images/MapIcons/cache/"+jsIcons[event.iconType].name+teamVar+".png",
						timeout: 30000,
						silent: true,				
						onClick: function () {
							window.focus();
							this.close();
						}
					});		
			<?php }else{
			
				$fadeOut = (isset($_GET['fade']) ? intval($_GET['fade']) : 0);
			
				if($fadeOut)echo "$(html).prependTo('.eventLog ul').fadeOut(". $fadeOut*1000 .")";
				else echo "$(html).prependTo('.eventLog ul')";			
			
			 } ?>

			  });

		<?php } ?>	

	</script>
	
