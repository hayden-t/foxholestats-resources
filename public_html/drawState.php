<script>

function updateApiStatus(status){

			if(status == 1)$('#summary .apiStatus').html('Sync Online').attr('class','apiStatus online');
			else $('#summary .apiStatus').html('<a href="#" style="text-decoration:none;" onclick="location.reload()">Click to Refresh</a>').attr('class','apiStatus offline');
			
			if(Push.Permission.has())$('#notificationsStatus').html("<span style='color: #00f03c;'>Browser Notifications Enabled</span>");
			else $('#notificationsStatus').html("<span style='color: #ffef00;'>Browser Notifications Disabled</span>");
}

function formatNumber(num, shorten) {
	if(shorten){
		if(num > 1000)num = (num/1000).toFixed()+'k'
	}
  return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

function formatVictory(state){
	
	var captures = state.captures;	

	var victoryTownsTotal = wardenT1 =  wardenT2 = wardenT3 = collieT1 = collieT2 = collieT3 = wardenClaimed = collieClaimed = wardenSc = collieSc = wardenIntel = collieIntel = wardenRockets = collieRockets = wardenWeather = collieWeather = 0;
	
		if(56 in captures){
			for (var i = 0; i < captures[56].length; i++) {
				if(captures[56][i]['F'] & 0x01){
					if(captures[56][i].T == 'W'){
						wardenT1++;
						if(captures[56][i]['F'] & 0x20)wardenClaimed++;
					}
					if(captures[56][i].T == 'C'){
						collieT1++;			
						if(captures[56][i]['F'] & 0x20)collieClaimed++;
					}			
				}			
			}
		}
		if(57 in captures){
			for (var i = 0; i < captures[57].length; i++) {
				if(captures[57][i]['F'] & 0x01){
					if(captures[57][i].T == 'W'){
						wardenT2++;
						if(captures[57][i]['F'] & 0x20)wardenClaimed++;
					}
					if(captures[57][i].T == 'C'){
						collieT2++;
						if(captures[57][i]['F'] & 0x20)collieClaimed++;
					}
				}			
			}
		}
	
		if(58 in captures){//T3
			for (var i = 0; i < captures[58].length; i++) {
				if(captures[58][i]['F'] & 0x01){
					if(captures[58][i].T == 'W'){
						wardenT3++;
						if(captures[58][i]['F'] & 0x20)wardenClaimed++;
					}
					if(captures[58][i].T == 'C'){
						collieT3++;
						if(captures[58][i]['F'] & 0x20)collieClaimed++;
					}
				}			
			}
		}
		if(59 in captures){//SC
			for (var i = 0; i < captures[59].length; i++) {
					if(captures[59][i].T == 'W'){
						wardenSc++;
					}
					if(captures[59][i].T == 'C'){
						collieSc++;
					}	
			}
		}		
		if(60 in captures){//intel
			for (var i = 0; i < captures[60].length; i++) {
					if(captures[60][i].T == 'W'){
						wardenIntel++;
					}
					if(captures[60][i].T == 'C'){
						collieIntel++;
					}	
			}
		}
		if(83 in captures){//weather
			for (var i = 0; i < captures[83].length; i++) {
					if(captures[83][i].T == 'W'){
						wardenWeather++;
					}
					if(captures[83][i].T == 'C'){
						collieWeather++;
					}	
			}
		}
		var wardenTargets = collieTargets = '';
		var seconds = Math.floor(new Date() / 1000);
		
		if(70 in captures){//Target
			for (var i = 0; i < captures[70].length; i++) {
					if(captures[70][i].T == 'W'){
						wardenTargets += '<span class="warden target"></span> T- '+ (48 - Math.floor((seconds - captures[70][i].S) / 3600))+'h ';
					}
					if(captures[70][i].T == 'C'){
						collieTargets += '<span class="collie target"></span>';
					}	
			}
		}
		if(72 in captures){//with rocket
			for (var i = 0; i < captures[72].length; i++) {
					if(captures[72][i].T == 'W'){
						//wardenRockets += '<span class="warden rocket"></span>';
						wardenRockets++;
					}
					if(captures[72][i].T == 'C'){
						//collieRockets += '<span class="collie rocket"></span>';
						collieRockets++;
					}	
			}
		}	
		
		var victoryTownsTotal = <?php echo $warState['requiredVictoryTowns']; ?> - state.scorchedVictoryTowns;
			
		$('.victoryTotals.wardens.line1').html(collieTargets+
			//'<span class="warden t1"></span>'+wardenT1+
			//'<span class="warden t2"></span>'+wardenT2+
			'<span class="warden victory"></span> '+(wardenT1+wardenT2+wardenT3)+" / "+victoryTownsTotal
		//	'<span class="warden t4"></span>'+wardenClaimed+"/"+(wardenT1+wardenT2+wardenT3)+" ("+victoryTownsTotal+")"
		//	'<br /><span class="warden sc"></span> '+wardenSc+' <span class="warden intel"></span> '+wardenIntel
		);
		$('.victoryTotals.wardens.line2').html(
			(wardenSc ? '<span class="warden sc"></span> '+wardenSc : '')
			+(wardenIntel ? ' <span class="warden intel"></span> '+wardenIntel : '' ) 
			+(wardenWeather ? ' <span class="warden weather"></span> '+wardenWeather : '' )
			+(wardenRockets ? ' <span class="warden rocket"></span> '+wardenRockets : '')
		);
		
		$('.victoryTotals.colonials.line1').html(wardenTargets+
			//'<span class="collie t1"></span>'+//collieT1+
		//	'<span class="collie t2"></span>'+//collieT2+
			'<span class="collie victory"></span> '+(collieT1+collieT2+collieT3)+" / "+victoryTownsTotal
		//	'<span class="collie t4"></span>'+collieClaimed+"/"++" ("+victoryTownsTotal+")"
		//	'<br /><span class="collie sc"></span> '+collieSc+' <span class="collie intel"></span> '+collieIntel
		);
		$('.victoryTotals.colonials.line2').html(
			(collieSc ? '<span class="collie sc"></span> '+collieSc : '' ) 
			+ (collieIntel ? ' <span class="collie intel"></span> '+collieIntel : '' ) 
			+ (collieWeather ? ' <span class="collie weather"></span> '+collieWeather : '' ) 
			+(collieRockets ? ' <span class="collie rocket"></span> '+collieRockets : '')
		);
		
		$('#dh').html(captures['Z']);
}

function formatTotals(state){
					state.wardenCasualties = Number(state.wardenCasualties);
					state.colonialCasualties = Number(state.colonialCasualties);
					state.wardenRate = Number(state.wardenRate);
					state.colonialRate = Number(state.colonialRate);
					
					$('#summary .day').html('In Game Day: ' +state.day);
					$('#summary .players').html( (state.totalPlayers > 0 ? formatNumber(state.totalPlayers, 0): '?'));
					$('#summary .viewers').html(formatNumber(state.viewers, 0));
					
					//$('#summary .totalEnlistments').html(formatNumber(state.totalEnlistments, 0));
					$('#summary .scorchedVictoryTowns').html(state.scorchedVictoryTowns);
					
					wTime = moment.unix(state.time).utc();
					$('#worldClock').html(
						'[ Los Angeles '+ wTime.tz("America/Los_Angeles").format('ha')+' ]'
						+'[ New York '+ wTime.tz("America/New_York").format('ha')+' ]'
						+'[ London '+ wTime.tz("Europe/London").format('ha')+' ]'
						+'[ Paris/Berlin '+ wTime.tz("Europe/Paris").format('ha')+' ]'
						+'[ Moscow/Turkey '+ wTime.tz("Europe/Moscow").format('ha')+' ]'
						//+'[ Delhi '+ wTime.tz("Asia/Delhi").format('ha')+' ]'
						+'[ Shanghai '+ wTime.tz("Asia/Shanghai").format('ha')+' ]'
						+'[ Sydney  '+ wTime.tz("Australia/Sydney").format('ha')+' ]'
					);
					
					$('#summary .wardenCasualties').html(formatNumber(state.wardenCasualties, 0));
					$('#summary .colonialCasualties').html(formatNumber(state.colonialCasualties, 0));
					$('#summary .totalCasualties').html(formatNumber(state.wardenCasualties+state.colonialCasualties, 0));			
					if(state.colonialCasualties > state.wardenCasualties){
						var casualtyImbalance = Math.round(((state.colonialCasualties-state.wardenCasualties)/state.wardenCasualties)*100);
						casualtyImbalance = "+"+formatNumber(casualtyImbalance, 0)+'% <img src="/images/collie.png" />';
					}else{
						var casualtyImbalance = Math.round(((state.wardenCasualties-state.colonialCasualties)/state.colonialCasualties)*100);
						casualtyImbalance = "+"+formatNumber(casualtyImbalance, 0)+'% <img src="/images/warden.png" />';
					}
					$('#summary .casualtiesImbalance').html(casualtyImbalance);			
					$('#summary .wardenRate').html(formatNumber(state.wardenRate, 0)+'/hr');
					$('#summary .colonialRate').html(formatNumber(state.colonialRate, 0)+'/hr');
					$('#summary .totalRate').html(formatNumber(state.wardenRate+state.colonialRate, 0)+'/hr');
					if(state.colonialRate > state.wardenRate){
						var rateImbalance = Math.round(((state.colonialRate-state.wardenRate)/state.wardenRate)*100);
						rateImbalance = "+"+formatNumber(rateImbalance, 0)+'% <img src="/images/collie.png" />';
					}else{
						var rateImbalance = Math.round(((state.wardenRate-state.colonialRate)/state.colonialRate)*100);
						rateImbalance = "+"+formatNumber(rateImbalance, 0)+'% <img src="/images/warden.png" />';
					}			
					$('#summary .rateImbalance').html(rateImbalance);					
			
					formatVictory(state);
					
					$('#summary .update').show().fadeOut(20000);
					
					if(!document.hidden){
						var img = new Image();
						img.src = $('#controlLayer').data('bare')+state.time;
						$(img).on("load", function(){
								$('#controlLayer').css('background-image','url("'+$('#controlLayer').data('bare')+state.time+'")');
								
						});
					}



				
}


function formatMapGrid(state){
		
		$('.region_'+state.regionId+' .casualties .warden .text').html(formatNumber(state.wardenCasualties, 1)).attr('title','Warden Casualties: '+formatNumber(state.wardenCasualties));
		$('.region_'+state.regionId+' .casualties .colonial .text').html(formatNumber(state.colonialCasualties, 1)).attr('title','Collie Casualties: '+formatNumber(state.colonialCasualties));

		var color3W='';
		var color3C='';
		
		if(state.wardenRate >= 1600)color3W = '#FF0EF0';
		else if(state.wardenRate >= 1400)color3W = '#FF0040';		
		else if(state.wardenRate >= 1200)color3W = '#FF6e00';						
		else if(state.wardenRate >= 1000)color3W = '#FFbe00';
		else if(state.wardenRate >= 800)color3W = '#fdff00';
		else if(state.wardenRate >= 600)color3W = '#3eff00';
		else if(state.wardenRate >= 400)color3W = '#00fff4';
		else if(state.wardenRate >= 200)color3W = '#0022ff';
		
		if(state.colonialRate >= 1600)color3C = '#FF0EF0';
		else if(state.colonialRate >= 1400)color3C = '#FF0040';		
		else if(state.colonialRate >= 1200)color3C = '#FF6e00';						
		else if(state.colonialRate >= 1000)color3C = '#FFbe00';
		else if(state.colonialRate >= 800)color3C = '#fdff00';
		else if(state.colonialRate >= 600)color3C = '#3eff00';
		else if(state.colonialRate >= 400)color3C = '#00fff4';		
		else if(state.colonialRate >= 200)color3C = '#0022ff';	
			
		$('.region_'+state.regionId+' .casualtyRate .warden .rateDisc').css('background',color3W);
		
		if(color3W){
			var speed = (1/(state.wardenRate/60/60))*1000*.1;
			$('.region_'+state.regionId+' .casualtyRate .warden .rateDisc').css('border-color','white').pulsate("destroy").pulsate({reach:10,speed:speed,glow:false,color:'#dfdfdf'});
		}else{
			$('.region_'+state.regionId+' .casualtyRate .warden .rateDisc').css('border-color','transparent').pulsate("destroy");
		}
		
		$('.region_'+state.regionId+' .casualtyRate .warden .text').html(formatNumber(state.wardenRate, 0));
		
		$('.region_'+state.regionId+' .casualtyRate .colonial .rateDisc').css('background',color3C);
		
		if(color3C){
			var speed = (1/(state.colonialRate/60/60))*1000*.1;
			$('.region_'+state.regionId+' .casualtyRate .colonial .rateDisc').css('border-color','white').pulsate("destroy").pulsate({reach:10,speed:speed,glow:false,color:'#dfdfdf'});
		}else{
			$('.region_'+state.regionId+' .casualtyRate .colonial .rateDisc').css('border-color','transparent').pulsate("destroy");
		}
				
		$('.region_'+state.regionId+' .casualtyRate .colonial .text').html(formatNumber(state.colonialRate, 0));		
		
		var captures = state.captures;	
		
		if((!('C' in captures) && state.colonialRate > 200) || (!('W' in captures) && state.wardenRate > 200)){//only warden
			$('.region_'+state.regionId+' .qrfDisc').css('display','inline-block').pulsate("destroy").pulsate({reach:10,speed:500,glow:false,color:'#dfdfdf'});
		}else $('.region_'+state.regionId+' .qrfDisc').hide().pulsate("destroy");
		
			

		var victoryHTML = [];
		var significantsHTML = [];
		//made available by warapi.php line 397
		if(56 in captures){
			for (var i = 0; i < captures[56].length; i++) {
				if(captures[56][i].T == 'W'){
					if(captures[56][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier1Warden.png',flags:captures[56][i]['F'],team:'Warden'});			
				}
				if(captures[56][i].T == 'N'){
					var nuked = ( captures[56][i]['F'] & 0x10 ) ? 'Rocket' : '';
					if(captures[56][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier1'+nuked+'.png',flags:captures[56][i]['F'],team:'none'});			
				}					
				if(captures[56][i].T == 'C'){				
					if(captures[56][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier1Colonial.png',flags:captures[56][i]['F'],team:'Colonial'});
				}	
			}
		}
		if(57 in captures){
			for (var i = 0; i < captures[57].length; i++) {
				if(captures[57][i].T == 'W'){				
					if(captures[57][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier2Warden.png',flags:captures[57][i]['F'],team:'Warden'});			
				}
				if(captures[57][i].T == 'N'){
					var nuked = ( captures[57][i]['F'] & 0x10 ) ? 'Rocket' : '';		
					if(captures[57][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier2'+nuked+'.png',flags:captures[57][i]['F'],team:'none'});
				}					
				if(captures[57][i].T == 'C'){					
					if(captures[57][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier2Colonial.png',flags:captures[57][i]['F'],team:'Colonial'});
				}	
			}
		}
		if(58 in captures){
			for (var i = 0; i < captures[58].length; i++) {
				if(captures[58][i].T == 'W'){					
					if(captures[58][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier3Warden.png',flags:captures[58][i]['F'],team:'Warden'});	
				}
				if(captures[58][i].T == 'N'){
					var nuked = ( captures[58][i]['F'] & 0x10 ) ? 'Rocket' : '';
					if(captures[58][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier3'+nuked+'.png',flags:captures[58][i]['F'],team:'none'});
				}					
				if(captures[58][i].T == 'C'){				
					if(captures[58][i]['F'] & 0x01)victoryHTML.push({url:'images/MapIcons/cache/MapIconTownBaseTier3Colonial.png',flags:captures[58][i]['F'],team:'Colonial'});
				}
			}	
		}
		
		if(37 in captures){
			for (var i = 0; i < captures[37].length; i++) {
				if(captures[37][i].T == 'W'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketSiteWarden.png',flags:captures[37][i]['F'],team:'Warden'});	
				}
				else if(captures[37][i].T == 'N'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketSite.png',flags:captures[37][i]['F'],team:'none'});
				}					
				else if(captures[37][i].T == 'C'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketSiteColonial.png',flags:captures[37][i]['F'],team:'Colonial'});
				}
			}
		}
		
		if(72 in captures){
			for (var i = 0; i < captures[72].length; i++) {
				if(captures[72][i].T == 'W'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketSiteWithRocketWarden.png',flags:captures[72][i]['F'],team:'Warden'});	
				}
				else if(captures[72][i].T == 'N'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketSiteWithRocket.png',flags:captures[72][i]['F'],team:'none'});
				}					
				else if(captures[72][i].T == 'C'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketSiteWithRocketColonial.png',flags:captures[72][i]['F'],team:'Colonial'});
				}
			}
		}
		
		if(70 in captures){
			for (var i = 0; i < captures[70].length; i++) {
				if(captures[70][i].T == 'W'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketTargetWarden.png',flags:captures[70][i]['F'],team:'Warden'});	
				}
				else if(captures[70][i].T == 'N'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketTarget.png',flags:captures[70][i]['F'],team:'none'});
				}					
				else if(captures[70][i].T == 'C'){
					victoryHTML.push({url:'images/MapIcons/cache/MapIconRocketTargetColonial.png',flags:captures[70][i]['F'],team:'Colonial'});
				}
			}
		}
		
		if(59 in captures){
		var wardenCount = collieCount = 0;
			for (var i = 0; i < captures[59].length; i++) {	
				if(captures[59][i].T == 'W')wardenCount++;
				else if(captures[59][i].T == 'C')collieCount++
			}				
			if(wardenCount)significantsHTML.push({url:'images/MapIcons/cache/MapIconStormCannonWarden.png',flags:captures[59][0]['F'],team:'Warden'});
			if(collieCount)significantsHTML.push({url:'images/MapIcons/cache/MapIconStormCannonColonial.png',flags:captures[59][0]['F'],team:'Colonial'});
		}	
		
		if(60 in captures){
		var wardenCount = collieCount = 0;
			for (var i = 0; i < captures[60].length; i++) {	
				if(captures[60][i].T == 'W')wardenCount++;
				else if(captures[60][i].T == 'C')collieCount++
			}				
			if(wardenCount)significantsHTML.push({url:'images/MapIcons/cache/MapIconIntelCenterWarden.png',flags:captures[60][0]['F'],team:'Warden'});
			if(collieCount)significantsHTML.push({url:'images/MapIcons/cache/MapIconIntelCenterColonial.png',flags:captures[60][0]['F'],team:'Colonial'});
		}
		
		if(83 in captures){
		var wardenCount = collieCount = 0;
			for (var i = 0; i < captures[83].length; i++) {	
				if(captures[83][i].T == 'W')wardenCount++;
				else if(captures[83][i].T == 'C')collieCount++
			}				
			if(wardenCount)significantsHTML.push({url:'images/MapIcons/cache/MapIconWeatherStationWarden.png',flags:captures[83][0]['F'],team:'Warden'});
			if(collieCount)significantsHTML.push({url:'images/MapIcons/cache/MapIconWeatherStationColonial.png',flags:captures[83][0]['F'],team:'Colonial'});
		}
		
			
	
		function updateIcons(objects, block){
			var imgs = block.find('img');
			$.each(objects,function(index, object){
				var oClass = '';
				//if(object.flags & 0x20)oClass = 'Civic Upgrade (Win Condition)';
				if(index in imgs){imgs[index].src = object.url;$(imgs[index]).attr({'class':oClass,'title':oClass});}
				else block.append("<img class='"+oClass+"' title='"+oClass+"' src='"+object.url+"' />");
				//todo removal
			});		
		}				

		updateIcons(victoryHTML,$('.region_'+state.regionId+' .victoryTowns'));
		updateIcons(significantsHTML,$('.region_'+state.regionId+' .significants'));

			
		
/*		var tempHTML = '';
		
		if('W' in captures){
			tempHTML += '<span class="disc warden">'+captures.W+'</span>';
		}
		if('N' in captures){
			tempHTML += '<span class="disc neutral">'+captures.N+'</span>';
		}
		if('C' in captures){
			tempHTML += '<span class="disc collie">'+captures.C+'</span>' ;
		}
		
		$('.region_'+state.regionId+' .towns').html(tempHTML);*/

	
		//var tooltip = $('.region_'+state.regionId);/*.parent().find('.tooltip img');*/
		//tooltip.attr('href',tooltip.data('bare')+state.time);
		
}
		
/*function playHistory(){
	var myVideo = document.getElementById("player"); 
	myVideo.setAttribute('src', "/images/history/combined.mp4")
	$('.grid').css('height',$('.grid').css('height'));
	$('.victoryTowns, .casualtyRate, .casualties').hide();
	myVideo.play();
}*/

$( document ).ready(function() {

var syncTimeout = setTimeout(function(){updateApiStatus(0); }, 120 * 1000);

			var stateChannel = new window.EventSource('<?php echo $domain; ?>:<?php echo $eventPort; ?>/channel/state');

			stateChannel.addEventListener('totals', function (e) {
			
			clearTimeout(syncTimeout);
			syncTimeout = setTimeout(function(){updateApiStatus(0); }, 120 * 1000);
			updateApiStatus(1);
			
			var data = JSON.parse(e.data);
			var dynamic = data[0];
			var viewers = data[1];
			//console.log(apiStatus);
			//updateApiStatus(apiStatus);
			
			for (var i = 0; i < dynamic.length; i++) {			
						
					var state = dynamic[i];
					state.captures = JSON.parse(state.captures);
					//console.log(state);		
					
					if(state.regionId == 0){
						state.viewers = viewers;
						formatTotals(state);						
					}
					else formatMapGrid(state);					
			}
				
		});	
		
		<?php if(isset($_GET['lat']) && isset($_GET['lng'])){ ?>	
			
			$('.DeadLandsHex').click();
			
		<?php } ?>
	

});



</script>
 <?php 

		
		function drawGrid($map){

			global $dynamicStates;
			global $localIndex;
			
			$state = (isset($dynamicStates[$map])? $dynamicStates[$map]: false);
			if(!$state){
				echo '<span><a class="inactive grid"><div class="victoryTowns"></div><div class="title">'.$localIndex[$map]['name'].'</div></a></span>';
			return;
			}	
				echo '<span><a class="grid '.$state['mapName'].' region_'.$state['regionId'].'" href="#" data-featherlight-type="ajax" data-featherlight="drawLeaflet.php?map='.$state['mapName'].(isset($_GET['lat']) && isset($_GET['lng']) ? '&lat='.$_GET['lat'].'&lng='.$_GET['lng'] : '').'" data-featherlight-close-on-click="false">'
								//echo '<span><a class="grid '.$state['mapName'].'" href="#" data-featherlight="/images/cache/'.strtolower($state['mapName']).'.png">'
				
						.'<div class="victoryTowns"></div>'
						.'<div class="title">'.$state['displayName'].'</div>';
			
				
				$significants =  '<div class="significants"><span class="qrfDisc" title="Invasion Alert"></span></div>';			
																
				$rateHTML = '<div class="casualtyRate" title="Casualty rate, 30 min AVG">'		
									.'<span class="warden"><img src="/images/wardenCross.png"/> <span class="text"></span>/hr <span class="rateDisc"></span></span>'
									.'<span class="colonial"><img src="/images/collieCross.png"/> <span class="text"></span>/hr <span class="rateDisc"></span></span>'
								.'</div>';
	
					$countHTML = '<div class="casualties">'
							.'<span class="warden"><span class="text"></span></span>&nbsp;&nbsp;<span class="colonial"><span class="text"></span></span>'
							.'</div>';
				
				echo $rateHTML.$significants.$countHTML;				


							

								
				echo 			'</a></span>';
							

				

		
				echo "
				<script>
					formatMapGrid(JSON.parse('". json_encode($state)."'));
				</script>	
				";		
		}	
		
?> 
  
  	 
  	  <div>  	  
  	 <!--<img style="vertical-align:middle;margin-right:5px;width:40px;" src="images/MapIcons/full/MapIconFortCursed.png" /><span style="color:#ce2723;font-weight:bold;font-size: 21px;">DEAD HARVEST: <span id="dh"></span>/50</span>-->
	
		 <table id='summary'>
			<tr style="font-weight:bold;">
				<td style="font-weight:bold;color:#ff6820">Players (steam):</td>
				<td class='players' style="font-weight:bold;color:#ff6820"></td>
				<td colspan='0'>Website Users Online:</td>
				<td class='viewers'></td>
				<td class='day'></td>					
			</tr>		 
		 
			<tr>
				<th style="padding:0;"><!--<div class='update' style="padding:5px;color:#ff7e40;font-weight:normal;background:white;">Stats now live!</div>--></th>
				<th><img src="/images/warden.png" /> Wardens</th>
				<th><img src="/images/collie.png" /> Colonials</th>
				<th>Total</th>
				<th class='apiStatus'></th>
			</tr>	
			<tr>
				<td>Casualties</td>
				<td class='wardenCasualties'></td>
				<td class='colonialCasualties'></td>
				<td class='totalCasualties'></td>
				<td class='casualtiesImbalance'  style="text-align:right;"></td>			
			</tr>

		<tr style="border-bottom: thin solid #e3d7c5;">	
			<td>Casualty Rate</td>
				<td class='wardenRate'></td>
				<td class='colonialRate'></td>
				<td class='totalRate'></td>
				<td class='rateImbalance' style="text-align:right;"></td>			
			</tr>			

		<tr style="display:none;">
				<td>Enlistments</td>
				<td class='totalEnlistments'></td>
				<td colspan='2'><b>Scorched Victory Towns</b></td>
				<td class='scorchedVictoryTowns'></td>		
	
			</tr>			
							
		</table>		
<div style="padding-top:5px;color:#ff7e40;">
<span style="color:#ffe248">Donations keep this community built site online and ad free:</span><br >

<a  style="color:#ff7e40;" target='_blank' href="https://www.patreon.com/foxholestats"><img style="vertical-align:middle;padding:5px;" src="/images/patreon.png" />Patreon</a>
 <a style="color:#ff7e40;" target='_blank' href="https://ko-fi.com/foxholestats"><img style="vertical-align:middle;padding:5px;" src="/images/kofi.png" />Ko-fi</a>
  <a style="color:#ff7e40;" target='_blank' href="https://www.paypal.com/donate/?hosted_button_id=NVNCWEDH8EKQU"><img style="vertical-align:middle;padding:5px;" src="/images/paypal.png" />PayPal</a>
</div>

	</div>	  
<div >  	  

		<div style=""><?php if ($domain == 'https://foxholestats.com'){ ?>
		<a style="" href="#" data-featherlight="#mylightbox">&#9658; Watch a replay of the war so far</a> 		
		 ( <a target="_blank" href="https://github.com/hayden-t/foxholestats-resources/tree/main/videos">archives</a> )<br />
	<?php } ?>
		  <a style="" href="https://discord.gg/dnegnws" target="_blank">Join FHS Discord</a>
		and 
		<a style="" href="https://www.reddit.com/r/foxholegame" target="_blank">the Reddit Sub</a><br />
		<span id="notificationsStatus" style="color:#ff7e40;"></span>
		
			<div id="mylightbox"> 
				<video controls>
				  <source src="https://github.com/hayden-t/foxholestats-resources/raw/main/videos/WC<?php echo $warState['id']; ?>.mp4" type="video/mp4">
				</video>
			</div>		
		</div>
 <div style="overflow-x: auto">		
			<table style="margin:0 auto;">
			<tr><td>
				<!-- dont forget mega wall !!! 
<div style="text-align:center;">Please note this map is a work in progress as i try to decide and find time to optimise it for the latest changes and particularly mobile usage.</div>-->
			<div style="background:url('/images/worldmap_warapi.webp?v=5')no-repeat center;background-size:100% 100%;">
			<div id="controlLayer" data-bare="images/WorldMapControl.webp?t=" style="background:url('images/WorldMapControl.webp?t=<?php echo $dynamicStates['Conquest_Total']['time']?>')no-repeat center;background-size:100% 100%;">		

				<table class="mapGrid" style="color:white;">
				<tr>
				<td>
					<?php drawGrid('OarbreakerHex'); ?>
					<?php drawGrid('FishermansRowHex'); ?>
					<?php drawGrid('StemaLandingHex'); ?>
				</td>
				<td>
					<?php drawGrid('NevishLineHex'); ?>
					<?php drawGrid('FarranacCoastHex'); ?>
					<?php drawGrid('WestgateHex'); ?>
					<?php drawGrid('OriginHex'); ?>
				</td>			
				<td>
					<?php drawGrid('CallumsCapeHex'); ?>
					<?php drawGrid('StonecradleHex'); ?>
					<?php drawGrid('KingsCageHex'); ?>
					<?php drawGrid('SableportHex'); ?>
					<?php drawGrid('AshFieldsHex'); ?>
				</td>
				<td>
					<div class='victoryTotals colonials line2'></div>		
					<?php drawGrid('SpeakingWoodsHex'); ?>
					<?php drawGrid('MooringCountyHex'); ?>
					<?php drawGrid('LinnMercyHex'); ?>
					<?php drawGrid('LochMorHex'); ?>
					<?php drawGrid('HeartlandsHex'); ?>
					<?php drawGrid('RedRiverHex'); ?>
				</td>
				<td>
					<div class='victoryTotals colonials line1'></div>
					<div class='victoryTotals wardens line1'></div>							
					<?php drawGrid('BasinSionnachHex'); ?>
					<?php drawGrid('ReachingTrailHex'); ?>
					<?php drawGrid('CallahansPassageHex'); ?>
					<?php drawGrid('DeadLandsHex'); ?>
					<?php drawGrid('UmbralWildwoodHex'); ?>
					<?php drawGrid('GreatMarchHex'); ?>
					<?php drawGrid('KalokaiHex'); ?>
				</td>
				<td>					
					<div class='victoryTotals wardens line2'></div>
					<?php drawGrid('HowlCountyHex'); ?>
					<?php drawGrid('ViperPitHex'); ?>
					<?php drawGrid('MarbanHollow'); ?>
					<?php drawGrid('DrownedValeHex'); ?>
					<?php drawGrid('ShackledChasmHex'); ?>
					<?php drawGrid('AcrithiaHex'); ?>
				</td>
				<td>					
					<?php drawGrid('ClansheadValleyHex'); ?>
					<?php drawGrid('WeatheredExpanseHex'); ?>
					<?php drawGrid('ClahstraHex'); ?>
					<?php drawGrid('AllodsBightHex'); ?>
					<?php drawGrid('TerminusHex'); ?>	
				</td>
				<td>
					<?php drawGrid('MorgensCrossingHex'); ?>
					<?php drawGrid('StlicanShelfHex'); ?>
					<?php drawGrid('EndlessShoreHex'); ?>	
					<?php drawGrid('ReaversPassHex'); ?>	
				</td>	
				<td>
					<?php drawGrid('GodcroftsHex'); ?>
					<?php drawGrid('TempestIslandHex'); ?>
					<?php drawGrid('TheFingersHex'); ?>				
				</td>												
				</tr>
				</table>
			</div></div>	
		</td>
		</tr>	
		</table>	
</div>
		</div>		Darker map colors = more recent change, going back 5 days		
<div id="worldClock" style="color:#ff7e40">	 		</div>	

<script>
<?php $dynamicStates['Conquest_Total']['viewers'] = $viewers; ?>
	formatTotals(JSON.parse('<?php echo json_encode($dynamicStates['Conquest_Total']); ?>'));
	updateApiStatus(1);	
</script>		



