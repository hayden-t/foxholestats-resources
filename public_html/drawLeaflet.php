<div style="display:flex;flex-direction:column;height:100%;">

<?php 
//todo build state

include_once "_settings.php";

//$wells = file_get_contents(__DIR__."/wells.json");
//$wells = json_decode($wells);
//$mortars = file_get_contents(__DIR__."/mortars.json");
//$mortars = json_decode($mortars);

$zoomTo = '';
$lat = '';
$lng = '';
if(isset($_GET['lat']) && isset($_GET['lng'])){

	$lat = $_GET['lat'];
	$lng = $_GET['lng'];

}else if(isset($_GET['map']) && isset($_GET['map'], $localIndex)){
	$zoomTo = $_GET['map'];
}

if(isset($_GET['full'])){//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css
	echo '
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">	
	';
}


$now = date('U');//utc
$mysqli = new mysqli($servername, $username, $password, $dbname);
$regions = [];
		$sql = "SELECT `warapi_dynamic`.`mapName`,`warapi_dynamic`.`dynamic`,`warapi_static`.`static` FROM `warapi_dynamic` JOIN `warapi_static` ON `warapi_dynamic`.`regionId` = `warapi_static`.`regionId`";
		//  WHERE `warapi_dynamic`.`mapName` = '".$mapName."'
		$result2 = $mysqli->query($sql);
		while($row =  mysqli_fetch_assoc($result2)){
		
			$regions[$row['mapName']] = ['static' => json_decode($row['static'], true), 'dynamic' => json_decode($row['dynamic'], true)];
		}

?>


    <script src="/lib/leaflet/leaflet.js?v=3" crossorigin=""></script>    
    <link rel="stylesheet" href="/lib/leaflet/leaflet.css" crossorigin=""/>
    
    <script src="/lib/leaflet.measure.js?v=3" crossorigin=""></script>
    <link rel="stylesheet" href="/lib/leaflet.measure.css?v=3" crossorigin=""/>    
    
  <script src="/lib/leaflet.rangefinder.js?v=3" crossorigin=""></script>
 <link rel="stylesheet" href="/lib/leaflet.rangefinder.css?v=3" crossorigin=""/>   
 
  <script src="/lib/leaflet.locationLink.js?v=3" crossorigin=""></script>
 <link rel="stylesheet" href="/lib/leaflet.locationLink.css?v=3" crossorigin=""/>   
    
    <script src='/lib/leaflet.fullscreen.min.js'></script>
<link href='/lib/leaflet.fullscreen.css' rel='stylesheet' />

    <script src='/lib/Javascript-Voronoi-master/rhill-voronoi-core.min.js'></script>
     <script src='/lib/turf.min.js'></script>

	<style>
		html, body {
			height: 100%;
			margin: 0;
		}
		#map {
			width: 100%;
			flex-grow:1;
			background:black;
			border:none;
		}
		.timers, .regionLabel, .Minor, .Major{
		  white-space:nowrap;
		  text-shadow: 0 0 0.1em black, 0 0 0.1em black,
				0 0 0.1em black,0 0 0.1em black,0 0 0.1em;
		  color: white;
		  font-size:16px;
			text-align:center;
		
		}
		.zoomOptional{
			display:none;
		}
		
		.timers, .Major, .Minor, .notCapturable, .resource, .range, .regions{
				display:none;
		}
		.ui-resizable-se{
			background: url("/images/resize.png") center / contain !important;
			height: 40px;
			z-index: 999999 !important;
			width: 40px;
		}
		.featherlight-content{
			border: thin solid #b3b3b3;		
			width: 100%;
			max-height:none!important;
			margin: 0!important;
		}
		.featherlight .featherlight-close-icon{
			rgba(255, 255, 255, 1);
			font-size: 22px;
			line-height: 40px;
			width: 40px;
		}
		.leaflet-interactive {
			/*cursor: unset;*/
		}
		.ui-autocomplete{
			z-index:9999;
			text-align:left;
		}
		#leafletSearch{
			font-size: 16px;
			text-align: left;		
			padding: 1px 10px;	
		}
/*Pasqualle */	
		.top-nav {
			display: flex;
			flex-direction: row;
			justify-content: center;
			background-color: #000;
			color: #FFF;
			padding: 0.5em;
			z-index: 1500;
			width: 100%;
		}
		
		.top-nav label, .top-nav input{
			cursor: pointer;
		}

		.top-nav .title {
			margin: 0.4rem 0.5rem;
			line-height:22px;
		}

		.menu {
		  display: flex;
		  flex-direction: row;
		  list-style-type: none;
		  margin: 0;
		  padding: 0;
		  flex-wrap: wrap;
		}

		.menu > li {
		  margin: 0.4rem 0.1rem;
		  overflow: hidden;
		  user-select: none;
		  cursor:pointer;
		  line-height:22px;
		}


		.menu-button-container {
		  display: none;
		  height: 100%;
		  width: 30px;
		  cursor: pointer;
		  flex-direction: column;
		  justify-content: center;
		  align-items: center;
		}

		#menu-toggle {
		  display: none;
		}

		.menu-button,
		.menu-button::before,
		.menu-button::after {
		  display: block;
		  background-color: #fff;
		  position: absolute;
		  height: 4px;
		  width: 30px;
		  transition: transform 400ms cubic-bezier(0.23, 1, 0.32, 1);
		  border-radius: 2px;
		}

		.menu-button::before {
		  content: '';
		  margin-top: -8px;
		}

		.menu-button::after {
		  content: '';
		  margin-top: 8px;
		}

		#menu-toggle:checked + .menu-button-container .menu-button::before {
		  margin-top: 0px;
		  transform: rotate(405deg);
		}

		#menu-toggle:checked + .menu-button-container .menu-button {
		  background: rgba(255, 255, 255, 0);
		}

		#menu-toggle:checked + .menu-button-container .menu-button::after {
		  margin-top: 0px;
		  transform: rotate(-405deg);
		}

@media (max-width: 700px) {
		  .menu-button-container {
			display: flex;
		  }
			#leafletSearch{
				text-align: left;			
			}
		  .menu {
			position: absolute;
			top: 0;
			margin-top: 50px;
			left: 0;
			flex-direction: column;
			width: 100%;
			justify-content: center;
			align-items: center;
		  }
		  #menu-toggle ~ .menu li {
			height: 0;
			margin: 0;
			padding: 0;
			border: 0;
			transition: height 400ms cubic-bezier(0.23, 1, 0.32, 1);
		  }
		  #menu-toggle:checked ~ .menu li {
			border: 1px solid #333;
			height: 2.5em;
			padding: 0.5em;
			transition: height 400ms cubic-bezier(0.23, 1, 0.32, 1);
		  }
		  #menu-toggle:checked ~ .menu li label {
			width: 100%;
		  }
		  #menu-toggle:checked ~ .menu li:first-child {
			height: 3em;
		  }
		  .menu > li {
			display: flex;
			
			margin: 0;
			padding: 0.5em 0;
			width: 100%;
			color: white;
			background-color: #222;
		  }
		  .menu > li:not(:last-child) {
			border-bottom: 1px solid #444;
		  }
		  
		  #menu-toggle:checked ~ .menu #leafletSearch {
			width: 80%;
		  }
		  
		  .top-nav .title {
			position: relative;
			right: -10px;
			top: 0;
		  }
		  .top-nav {
		   justify-content: left;
		  }
}

/*Pasqualle */	
	</style>
<!--
<div id="filter" class="ui-front" style="text-align:center;background:black;color:white;padding-right: 40px;z-index:999">foxholestats.com - filter: 
<input class="filter" id="12" value="regions" type="checkbox"/><label for="12">regions</label>
	<input class="filter" id="1" value="capturable" type="checkbox" checked="checked"/><label for="1">core </label>
	<input class="filter" id="2" class="filter" value="notCapturable" type="checkbox" /><label for="2">other </label>
	<input class="filter" id="3" value="resource" type="checkbox" /><label for="3">resources </label>
	<input class="filter" id="4" value="Major" type="checkbox" /><label for="4">major labels </label>
	<input class="filter" id="5" value="Minor" type="checkbox" /><label for="5">minor labels </label>
	<input class="filter" id="6" value="timers" type="checkbox" /><label for="6">timers </label>
	<!--<input class="filter" id="7" value="range" type="checkbox" /><label for="7">ranges </label>-
	<input class="toggleImm" id="8" type="checkbox" checked="checked"/><label for="8">imm </label>
	<!--<input class="toggleTopo" id="9" type="checkbox"  /><label for="9">topo </label>
	<input class="toggleRoads" id="10" type="checkbox"  /><label for="10">roads </label>
	<input class="toggleDecay" id="11" type="checkbox" /><label for="11">decay </label>
	<input id="leafletSearch" placeholder="search everything" style=""/>
</div>
-->
  <section class="top-nav">
    <input id="menu-toggle" type="checkbox" />
	<label class="menu-button-container" for="menu-toggle">
      <div class="menu-button"></div>
    </label>

	<div class="title">
      foxholestats.com
    </div>
	<ul id="filter" class="menu ui-front">
      <li>
	    <input id="leafletSearch" placeholder="search everything">
	  </li>
	  <li>
	    <label><input class="filter" id="1" value="capturable" type="checkbox" checked="checked">core</label>
	  </li>
      <li>
	    <label><input class="filter" id="2" value="notCapturable" type="checkbox">other</label>
	  </li>
      <li>
	    <label><input class="filter" id="3" value="resource" type="checkbox">resources</label>
	  </li>
	  <li>
	    <label><input class="filter" id="4" value="Major" type="checkbox">major labels</label>
	  </li>
	  <li>
	    <label><input class="filter" id="5" value="Minor" type="checkbox">minor labels</label>
	  </li>
	  <li>
	    <label><input class="filter" id="6" value="timers" type="checkbox">timers</label>
	  </li>
	  <li>
	    <label><input class="toggleImm" id="8" type="checkbox" checked="checked">imm</label>
	  </li>
      <li>
	    <label><input class="filter" id="12" value="regions" type="checkbox">subregions</label>
	  </li>
	  <li>
	    <label><input class="toggleDecay" id="11" type="checkbox">decay</label>
	  </li>

    </ul>
	

  </section>
<div id='map' class="ui-widget-content"></div>

<script>

	var distanceFactor = (1890/36.4)*1.15;
	var teamColor = {'WARDENS': '#1572B9', 'COLONIALS':'#3B792F'};
	
	var ranges = {//range circle distance per icon id
		28:[500],
		35:[100],
		45:[150],
		46:[150],
		47:[150],
		53: [150,200],
		56:[150],
		57:[150],
		58:[150],
		59: [1000,1300],
		60: [2000],
		95: [100],
		70: [80],
		71: [80],
		83: [2000],
		84: [100],
		37: [2000],
		72: [2000],
	};

	
	$('.filter').change(function(){
		 if($(this).is(':checked'))$("."+ $(this).val()).show();
		else $("."+ $(this).val()).hide();	
	});
	
	$('.toggleImm').change(function(){
		 if($(this).is(':checked')){
			map.addLayer(imm);
			map.removeLayer(warapi);
		}else{
			map.addLayer(warapi);
			map.removeLayer(imm);
		}
	});
	
	$('.toggleTopo').change(function(){
		 if($(this).is(':checked'))map.addLayer(topo);
		else map.removeLayer(topo);
	});
	
	$('.toggleRoads').change(function(){
		 if($(this).is(':checked'))map.addLayer(roads);
		else map.removeLayer(roads);
	});

	$('.toggleDecay').change(function(){
		 if($(this).is(':checked'))map.addLayer(decay);
		else map.removeLayer(decay);
	});

//	$('.leafletHeader .time').html(formatTime($('.leafletHeader .time').html()));		
	var localIndex = JSON.parse('<?php echo json_encode($localIndex); ?>');		
		
	var jsIcons = JSON.parse('<?php echo json_encode($icons); ?>');

	var capturable = JSON.parse('<?php echo json_encode($capturable); ?>');


	var jsRegions = JSON.parse('<?php echo addslashes(json_encode($regions)); ?>');

	//var wells = JSON.parse('<?php //echo json_encode($wells); ?>');
	//var mortars = JSON.parse('<?php //echo json_encode($mortars); ?>');
	
	var allPlaces = [];


	var zoomTo = '<?php echo $zoomTo;?>';
	var lat = '<?php echo $lat;?>';
	var lng = '<?php echo $lng;?>';

	map = L.map('map',{
		crs: L.CRS.Simple,	
		minZoom: 1,
		maxZoom: 9,
		doubleClickZoom: false,
		touchZoom: false
	}).setView( [-128, 128], 1);
	
	var temp = L.control.measure({
	  position: 'topleft',
	   lineColor: '#c41818',
	   lineDashArray: '6, 6',
	  formatDistance: function (val) {
			//console.log(val);
			return Math.round(distanceFactor * val) + 'm';
		}
	}).addTo(map)
	
	L.control.rangefinder({
      position: 'topleft',
      distanceFactor: distanceFactor,
    }).addTo(map)
    
	var locationLink = L.control.locationLink({
      position: 'topleft',
      distanceFactor: distanceFactor,
    }).addTo(map)
	
				  
	  $('body').on('click','.shareLocation',function(){

		navigator.clipboard.writeText($(this).attr('href'));
		$(this).find('img').attr('src','/lib/check.png');

		return false;
	  });
	
	//var temp = L.control.rangefinder({
	//  position: 'topleft',
	//}).addTo(map)	
	
	
	//$("#map" ).resizable({
	//  resize: function( event, ui ) {map.invalidateSize()}
	//});	

	var warapi = L.tileLayer('/tiles/worldmap_warapi.jpg-tiles/{z}_{x}_{y}.jpg?v=12', {
		bounds: [[0,0], [-256,256]],
		maxNativeZoom:5,
	})//.addTo(map)	

	var imm = L.tileLayer('/tiles/worldmap_imm.jpg-tiles/{z}_{x}_{y}.jpg?v=12', {
		bounds: [[0,0], [-256,256]],
		maxNativeZoom:6,
	}).addTo(map)
	
	var decay = L.tileLayer('/tiles/worldmap_rdz.png-tiles/{z}_{x}_{y}.png?v=11', {
		bounds: [[0,0], [-256,256]],
		maxNativeZoom:6,
		//opacity: 0.5,
	})//.addTo(map)	
/*	*/
	map.addControl(new L.Control.Fullscreen());

	var yx = L.latLng;
	
	var mapWidth = <?php echo $mapSize['x']; ?>;	
	var mapHeight = <?php echo $mapSize['y']; ?>;	

	var pixelWorldWidth =  <?php echo $worldSize['x']; ?>;	
	var pixelWorldHeight =  <?php echo $worldSize['y']; ?>;	

	var gridIntervalWidth =  <?php echo $gridInterval['x']; ?>;	
	var gridIntervalHeight =  <?php echo $gridInterval['y']; ?>;	

//leaflet and warapi top left is 0,0
	function getWorldTopLeft(gridX, gridY){

		var x = gridIntervalWidth*gridX;
		var y = gridIntervalHeight*gridY;
		
		return {'x': x, 'y': y};
	}

	function getWorldCenter(gridX, gridY){

		var topLeft = getWorldTopLeft(gridX, gridY);
		
		var x = topLeft['x'] + (mapWidth/2);
		var y = topLeft['y'] + (mapHeight/2);

		return {'x': x, 'y': y};
	}
	
	function convertApiToPixel(regionName, x, y){//game cords
	//	"""convert x,y coords from Hextiles to Worldmap coords"""
	
		x = Math.max(Math.min(x, 1), 0);
		y = Math.max(Math.min(y, 1), 0);
		
		var topLeft = getWorldTopLeft(localIndex[regionName]['grid']['x'], localIndex[regionName]['grid']['y'])
		
		var xcoord = topLeft['x'] + (mapWidth * x);
		var ycoord = topLeft['y'] + (mapHeight * y);

		return convertPixelToLeaflet(xcoord, ycoord);
	}
	
	function convertPixelToLeaflet(xcoord, ycoord){	
	
		var leafletUnitsPerPixel = 256 / Math.max(pixelWorldWidth, pixelWorldHeight);
		
		var pixelOffset = Math.abs(pixelWorldWidth-pixelWorldHeight)/2;
		//offset to account for non square world tileset
		if(pixelWorldWidth > pixelWorldHeight)ycoord += pixelOffset;
		else xcoord += pixelOffset;
		
		var xcoord = xcoord * leafletUnitsPerPixel;
		var ycoord =  ycoord * leafletUnitsPerPixel * -1;
		//convert to leaflet coords
		//console.log(xcoord);
		return  yx(ycoord, xcoord)
	
	}

	var bounds = [[0,0], [mapHeight,mapWidth]];
	//var image = L.imageOverlay('images/Maps/Map'+<?php //echo "'".$mapName."'"; ?>+'.jpg', bounds).addTo(map);
	//var image = L.imageOverlay('images/cache/stonecradlehex.jpg', bounds).addTo(map);

	for (var key in jsIcons) {

		// if(capturable.indexOf(Number(key)) != -1 || key == 98/*|| key == 99|| key == 97*/)var size = [48, 48];
		/* else*/ var size =  [34, 34];
		
		if(capturable.indexOf(Number(key)) != -1 || key == 97|| key == 98|| key == 59|| key == 60 || key == 53)var className = 'capturable';
		else var className = 'notCapturable';
		
		if(jsIcons[key]['type']=='resource')var className = 'resource';
		if(key==17)var className = className+' resource';//refinery
		
		 jsIcons[key]['WARDENS'] = L.icon({
			 iconUrl: '/images/MapIcons/cache/'+jsIcons[key]['name']+'Warden.png',
			 iconSize: size,
			 className: className,	 
		});
		jsIcons[key]['COLONIALS'] = L.icon({
			 iconUrl: '/images/MapIcons/cache/'+jsIcons[key]['name']+'Colonial.png',
			 iconSize: size,
			 className: className,
		});	
		jsIcons[key]['NONE'] = L.icon({
			 iconUrl: '/images/MapIcons/cache/'+jsIcons[key]['name']+'.png',
			 iconSize: size,
			 className: className,
		});
		jsIcons[key]['ROCKET'] = L.icon({
			 iconUrl: '/images/MapIcons/cache/'+jsIcons[key]['name']+'Rocket.png',
			 iconSize: size,
			 className: className,
		});
		jsIcons[key]['COLOR'] = L.icon({
			 iconUrl: '/images/MapIcons/cache/'+jsIcons[key]['name']+'Color.png',
			 iconSize: size,		
			 className: className,	
		});				
	}

	var createLabelIcon = function(className,labelText,verticalOffset){
		return L.divIcon({
			className: className,
			html: labelText,
			iconSize: [300,26],
			iconAnchor:[150,verticalOffset]
		})
	}
	var uniqueId = 0;

	$.each(jsRegions, function(i, e){
	
		

		var jsDynamic = e.dynamic
		var jsStatic = e.static
		var region = i
		/*
		if(wells[region]){
			for (i = 0; i < wells[region].length; i++) {

				jsDynamic.mapItems.push({
				'x': wells[region][i][0]/2048,
				'y': wells[region][i][1]/1776,
				'iconType': 96,
				'flags': 0,
				'name': localIndex[region].name +' Well',
				'teamId': 'NONE',
				'timer': 0,
				});
				
			}
		}else console.log('well region not found: '+region);
		
		if(mortars[region]){
			for (i = 0; i < mortars[region].length; i++) {

				jsDynamic.mapItems.push({
				'x': mortars[region][i][0]/2048,
				'y': mortars[region][i][1]/1776,
				'iconType': 95,
				'flags': 0,
				'name': localIndex[region].name +' Mortar House',
				'teamId': 'NONE',
				'timer': 0,
				});
				
			}
		}else console.log('mortar region not found: '+region);
		*/

		var regionDatas = [];
		for (i = 0; i < jsDynamic.mapItems.length; i++) {
		
			uniqueId++;	
			var item = jsDynamic.mapItems[i];
			
			if(!item.name)item.name = 'unknown, awaiting update';
						
			if(!(item.iconType in jsIcons))continue;//unknown icon id
			
			if(item.flags & 0x10)item.name = item.name + ' - Nuked';
			
			var tempName = item.name;
			if(item.flags & 0x01){
				L.marker(convertApiToPixel(region, item.x, item.y) ,{icon: jsIcons[98]['NONE']}).addTo(map);
				tempName = tempName + ' - Victory';
			}//victory
			
			var location = convertApiToPixel(region, item.x, item.y);
			
			
			if(ranges[item.iconType]){
			
				for (r = 0; r < ranges[item.iconType].length; r++) {
						L.circle(location, {
							radius: ranges[item.iconType][r] / distanceFactor,
							color: teamColor[item.teamId],
							opacity: 0.6,
							fillOpacity: 0.4,
							dashArray: (r > 0 ? '10, 7':''),
							weight: '2',
							className: 'range r'+uniqueId,
						}).addTo(map);
				}
			
			}
				
			if('color' in jsIcons[item.iconType])item.teamId = 'COLOR';
			if(item.flags & 0x10)item.teamId = 'ROCKET';			


			if ('timer' in item && item.timer > 0 && capturable.indexOf(item.iconType) != -1){
				var timeStamp = <?php echo $now; ?>//new Date() / 1000;
				var timeHeld = Math.floor((timeStamp - item['timer'])/3600);//hours
				var timerText = (timeHeld <= 24 ? timeHeld +'h' : Math.floor(timeHeld/24)+'d');
				tempName = tempName + ' - '+timerText;
				
				L.marker( convertApiToPixel(region, item.x, item.y), {icon:createLabelIcon("timers", timerText,5),zIndexOffset:1000,interactive:false}).addTo(map);
			}
			
			if(ranges[item.iconType]){
				tempName = tempName + ' (click for range)';
			}
			
			var marker = L.marker( location ,{icon: jsIcons[item.iconType][item.teamId], uniqueId: uniqueId}).bindTooltip(tempName,{direction: 'top', offset: L.point(0, -15)}).addTo(map);//the main icon
			
			if(ranges[item.iconType]){
			
				marker.on('click', function(e){					
					//console.log(this.options.uniqueId);
					$('.r'+this.options.uniqueId).toggle();
				});
				
			}
				
			allPlaces.push({'label': item.name, 'marker': marker});
			
			//if(item.flags & 0x02)L.marker(convertApiToPixel(region, item.x-0.01, item.y+0.01) ,{icon: jsIcons[99][item.teamId]}).bindTooltip(item.name+' - Spawn').addTo(map);//home base
			//if(item.flags & 0x20)L.marker(convertApiToPixel(region, item.x+0.01, item.y+0.01) ,{icon: jsIcons[97][item.teamId]}).bindTooltip(item.name+' - Civic').addTo(map);//civic
			
			if(item['regionData']){
				item['regionData']['teamId'] = item['teamId'];
				regionDatas.push(item['regionData']);
			}
				

		}
		


		for (i = 0; i < jsStatic.mapTextItems.length; i++) {
			var item = jsStatic.mapTextItems[i];
			//var markerClass = 'labelClass'
			//markerClass = markerClass + ' '+item.mapMarkerType
			var marker = L.marker( convertApiToPixel(region, item.x, item.y), {icon:createLabelIcon(item.mapMarkerType, item.text, -15),zIndexOffset:1000,interactive:false}).addTo(map);
			allPlaces.push({'label': item.text, 'marker': marker});
		}
		
		
		
		var regionCenter = getWorldCenter(localIndex[region]['grid']['x'], localIndex[region]['grid']['y']);

		var regionBorder = [
			convertPixelToLeaflet(regionCenter['x']-mapWidth/2, regionCenter['y']),
			convertPixelToLeaflet(regionCenter['x']-mapWidth/4,regionCenter['y']+mapHeight/2  ),
			convertPixelToLeaflet(regionCenter['x']+mapWidth/4, regionCenter['y']+mapHeight/2 ),
			convertPixelToLeaflet(regionCenter['x']+mapWidth/2,regionCenter['y']),
			convertPixelToLeaflet(regionCenter['x']+mapWidth/4,regionCenter['y']-mapHeight/2 ),
			convertPixelToLeaflet(regionCenter['x']-mapWidth/4,regionCenter['y']-mapHeight/2 ),
			];

		var borderPoly = L.polygon(regionBorder, {
				color: "black",
				opacity: 0.3,
				weight: 3,
				fillOpacity: 0,
				//pane:'regionLabelsPane'
				})
		
		var marker = L.marker( convertPixelToLeaflet( regionCenter['x'], regionCenter['y']), {icon:createLabelIcon('regionLabel', localIndex[region].name, 20),zIndexOffset:1000,interactive:false}).addTo(map);	

		allPlaces.push({'label': localIndex[region].name, 'marker': marker});
		
		var voronoi = new Voronoi();
		var bbox = {xl: 0, xr: 1, yt: 0, yb: 1};
		
		//console.log(region);//OarbreakerHex
		//if(region != 'FishermansRowHex')continue;
		
			//console.log(regionDatas);
			
			var diagram = voronoi.compute(regionDatas, bbox);
			
			//console.log(diagram);
								
			var voro = [];			

			for (i = 0; i < diagram.cells.length; i++) {//for each voro segment
					
				//if(i != 1)continue;//just draw one segment
					//console.log(diagram.cells[i]);	
					var cell = diagram.cells[i];
					
					if(0){//mark the voro segment center point
						L.circle(convertApiToPixel(region, cell.site['x'], cell.site['y']), {
							color: 'red',
							fillColor: '#f03',
							fillOpacity: 0.5,
							radius: 0.5
						}).addTo(map);
					}
					
					var poly = [];
					//console.log(cell.halfedges);
					for (h = 0; h < cell.halfedges.length; h++) {							

							var start = cell.halfedges[h].getStartpoint();
							//console.log(start);

							var point1 = convertApiToPixel(region, start['x'], start['y']);							
							
							poly.push(point1);				

					}

					var cellPoly = L.polygon(poly)//.addTo(map);	//add to map for debugging without intersection			
					
					//console.log(cellPoly);
					//console.log(cellPoly.toGeoJSON());
					//bug  with toGeoJSON and exponent (very small) values producing nan
					//https://github.com/Leaflet/Leaflet/issues/9172 
					
					if(1){//calc intersection (crop by hex)
						var intersection = turf.intersect(cellPoly.toGeoJSON(3),borderPoly.toGeoJSON());					

						var L_Intersection = L.GeoJSON.coordsToLatLngs(intersection.geometry.coordinates[0]);
						
						//console.log(cell.site);
						var color;
						if(cell.site['teamId'] == 'WARDENS')color = 'blue';
						if(cell.site['teamId'] == 'COLONIALS')color = 'green';
						if(cell.site['teamId'] == 'NONE')color = 'white';
						
						L.polygon( L_Intersection , {
							color: '#404040',
							fillColor: color,
							fillOpacity: 0.15,
							weight: 1,
							className: 'regions',
						}).addTo(map);	
					}						
			}
			
			borderPoly.addTo(map);

		
		
	});
	
	map.on('zoom', function() {

	
		//if(map.getZoom() >= 4)$('.Major, .notCapturable').fadeIn();
	 //   else $('.Minor, .Major, .notCapturable').fadeOut();	

	 //   if(map.getZoom() >= 5)$('.Minor').fadeIn();
	  //  else $('.Minor').fadeOut();
	    

	});
	
	if(lat && lng){
	
		var point = L.latLng(lat, lng);
		locationLink._createCircle(point);
		map.setView(point, 5);		
	
	}else if(zoomTo){
	
			map.fitBounds([
				convertApiToPixel(zoomTo, 0, 0),
				convertApiToPixel(zoomTo, 1, 1)
			]);

	}//else map.fitBounds(bounds);
	
	$('#leafletSearch').click(function(){$(this).val('')});
	
	allPlaces.sort((a, b) => {
		  const nameA = a.label.toUpperCase(); // ignore upper and lowercase
		  const nameB = b.label.toUpperCase(); // ignore upper and lowercase
		  if (nameA < nameB) {
			return -1;
		  }
		  if (nameA > nameB) {
			return 1;
		  }

		  // names must be equal
		  return 0;
		});
	
	$('#leafletSearch').autocomplete({
		  source: allPlaces,
		  minLength: 3,
		  select: function( event, ui ) {
		  		  
			$('#leafletSearch').val(ui.item.label);
			$('#menu-toggle').click();
			map.flyTo(ui.item.marker.getLatLng(), 7);
			$('#filter input[value='+ ui.item.marker.options.icon.options.className.split(" ").pop()+']').prop('checked',true).change();
			ui.item.marker.openTooltip();
			//console.log(ui);
			return false;
		  }
	});

<?php if(isset($_GET['lat']) && isset($_GET['lng'])){?>


<?php }?>
<?php if(isset($_GET['link'])){ ?>
window.linkButton.click();

<?php }?>
</script>
<div style="text-align:center;background:black;color:white;padding: 10px;">This map only updates when closed and re-opened &#129408; -- Storm Cannon range drawn at 1000m & 1300m -- IMM map tiles provided by <a target="_blank" href="https://rustard.itch.io/improved-map-mod">Improved Map Mod by Rustard</a></div>

</div>
