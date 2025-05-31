 <?php 
		include "_settings.php";
 
		if(isset($_GET['lang'])){
			setcookie("lang", $_GET['lang'], strtotime( '+300 days' ));//1y
			$language = $_GET['lang'];
		}else{
			if(isset($_COOKIE["lang"]))$language = $_COOKIE["lang"];
			else $language = "EN";//default
		}
		
 
		require_once $path.'/vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
		$detect = new Mobile_Detect;
		
		if($detect->isMobile() && !isset($_GET['full']))$_GET['slim'] = 1;


		$mysqli = new mysqli($servername, $username, $password, $dbname);
		
		date_default_timezone_set('UTC');
		$now = date('U');//utc
		
		$minutes = date('i');
		$minute = str_split($minutes);
		$minute  =  10 - array_pop($minute);
		
		$mapName = (isset($_GET['map'],$localIndex)?$_GET['map']:'Conquest_Total');	
		
		$sql = "SELECT * FROM warapi_wars ORDER BY id DESC LIMIT 1";
		$result = $mysqli->query($sql);
		$warState = mysqli_fetch_assoc($result);
		
		
		$sql = "SELECT * FROM warapi_state WHERE id = 'viewers'";
		$result = $mysqli->query($sql);
		if($viewers = mysqli_fetch_assoc($result)){;	
			 $viewers = $viewers['value'];
		}else $viewers = 0;
		
		
		$showSkirmish = false;
		
		$latestTables = false;
		
		if(!isset($_GET['days'])){
			if(isset($_GET['slim']))$_GET['days'] = 1;
			else $_GET['days'] = 3;
		}
		

		if(strpos($_GET['days'], 'WC') !== FALSE){
			$warNumber = str_replace("WC", "", $_GET['days']);
			$sql = "SELECT * FROM warapi_wars WHERE id = ". intval($warNumber);
			$result = $mysqli->query($sql);	
			$result = mysqli_fetch_assoc($result);			
			
			//$from = $timeFrom = $result['timeFrom'];
			//$to = $timeTo = $result['timeTo'];
			$logsFrom = $result['logsFrom'];
			$logsTo = $result['logsTo'];
			$eventsFrom = $result['eventsFrom'];
			$eventsTo = $result['eventsTo'];
			$days = $_GET['days'];			
			
		}else{//latest
		
				$days = intval($_GET['days']);
				$from = $timeFrom = ($now -($days*24*60*60))/**1000*/;//utc				
				$to = $timeTo = $now;
				//$logsFrom = 2480334;
				$logsTo = 999999999999;
				$latestTables = true;			
		
		}


		//extract wc numbers and selected state

		$serverState = '';
		//$victoryTowns = [];
		$dynamicStates = [];

		$sql = "SELECT * FROM `warapi_dynamic` WHERE etag != -1";
		$result2 = $mysqli->query($sql);
		$factories = [0,0];
		while($row =  mysqli_fetch_assoc($result2)){
			
			$dynamicStates[$row['mapName']] = $row;
			$temp = json_decode($dynamicStates[$row['mapName']]['dynamic'],true);
			if($temp){
				$temp = $temp['mapItems'];
				foreach($temp as $k => $t){
					unset($temp[$k]['name']);
					
					if($temp[$k]['iconType'] == 34){
						if($temp[$k]['teamId'] == 'WARDENS')$factories[0]++;
						else if($temp[$k]['teamId'] == 'COLONIALS')$factories[1]++;
					}
				}
			}	
			$dynamicStates[$row['mapName']]['mapItems'] = $temp;	
			unset($dynamicStates[$row['mapName']]['dynamic']);//not needed
			unset($dynamicStates[$row['mapName']]['casualtyLog']);//not needed
			$dynamicStates[$row['mapName']]['captures'] = json_decode($dynamicStates[$row['mapName']]['captures'],true);	
			$dynamicStates[$row['mapName']]['displayName'] = (isset($localIndex[$row['mapName']]) ? $localIndex[$row['mapName']]['name'] : '');
			
			if($mapName == $row['mapName'])$serverState = $row;

		}
		
		$conquestStartTime = $warState['conquestStartTime'];
		$conquestEndTime = $warState['conquestEndTime'];
		$resistanceStartTime = $warState['resistanceStartTime'];
		
		if(!$conquestEndTime){//conquest
			$timerStartTime = $conquestStartTime;
			
		}else{//resistance
			$timerStartTime = $resistanceStartTime;
			
			$conquestLengthStart = new DateTime();
			$conquestLengthStart->setTimestamp($conquestStartTime);
			
			$conquestLengthEnd = new DateTime();		
			$conquestLengthEnd->setTimestamp($conquestEndTime);	
			$conquestLength = $conquestLengthStart->diff($conquestLengthEnd);			
		}		
		
		//timer
			$timerStart = new DateTime();
			$timerStart->setTimestamp($timerStartTime);		
			$timer = $timerStart->diff(new DateTime());	

		?> 

 <html>
 <title>Foxhole Game Stats Live & Historic</title>
  <head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=0.75">
  <meta itemprop="description" content="Foxhole world conquest event notifications, game state, statistics, graphs, maps & more...">
<!--  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />-->
  <link rel="shortcut icon" href="images/favicon.png" type="image/png"/>
  <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-11113962-33"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-11113962-33');
</script>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">	
    <script src="lib/featherlight-1.7.13/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
    <link href="lib/featherlight-1.7.13/release/featherlight.min.css" type="text/css" rel="stylesheet" />
    <script src="lib/jquery-i18n-develop/jquery.i18n.js" type="text/javascript" charset="utf-8"></script>
    <script src="lib/push.js"></script>
    <script src="lib/eventsource.min.js"></script>
    <script src="lib/jquery.pulsate.min.js"></script>
    <script src="lib/easytimer/dist/easytimer.js"></script>
     <script src="lib/moment.min.js"></script>
      <script src="lib/moment-timezone-with-data-10-year-range.min.js"></script>    
       <script src="lib/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
       
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=New+Rocker&display=swap" rel="stylesheet"> 
       
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart', 'controls']});
    </script>
    
    <link href="_style.css?v=8 type="text/css" rel="stylesheet" />
    <?php if(isset($_GET['stream'])){ ?>
    <link href="_style_stream.css" type="text/css" rel="stylesheet" />
    <?php } ?>
    <script>
		$( function() {
		
			  $(".tablesorter").tablesorter();
			 	 
		  });
		  
		
	 </script>

  </head>
  <body>

<?php 
	if(isset($_GET['stream']))include "drawEventLog.php"; 
	else{ ?>


  <div id="header">
  	<?php include "affiliate.php"; ?>



  <a style="text-decoration:none;" href="/"><h3 class="torn">Foxhole Stats</h3></a>
 <a href="/data/" style="color: #4ffff7;text-decoration: none;">** View 49 Wars (3.5 years) of until now, unreleased population and queue data ! **</a><br />
 watch a <a style="color:#fff562;" target="_blank" href="https://www.youtube.com/watch?v=Ai8igpz5uFg">youtube video</a> on the topic by "Robert LuvsGames"

<div id="shards">

	<a class='<?php echo ($dbname == 'foxholestats' ? 'orange' :''); ?>' href="https://foxholestats.com/">ABLE</a>  
	<a class='<?php echo ($dbname == 'shard2' ? 'orange' :''); ?>' href="https://shard2.foxholestats.com/">BAKER</a>
	<a class='<?php echo ($dbname == 'shard3' ? 'orange' :''); ?>' href="https://shard3.foxholestats.com/">CHARLIE</a> 

</div>


    </div>
    
    <?php if(!isset($warNumber)){ ?>
   		<div style="text-align:center;font-size: 20px;line-height:30px;">
   		<span style="color: #ff7e40;">World Conquest <?php echo $warState['id']; ?>  
   		<?php if($conquestEndTime){
			echo 'Winner: '.ucfirst(strtolower($warState['winner']));
			echo " ".$conquestLength->format("%ad : %Hh : %Im : %Ss");
			echo "<br />Resistance Mode";
			}			
		?>
			</span> <span id='newTimer'>
			
			</span>
			<script>
				 timer = new easytimer.Timer();
				 timer.start({startValues: [<?php echo "0,".$timer->s.",".$timer->i.",".$timer->h.",".$timer->days; ?>]});
				 
				 timer.addEventListener('secondsUpdated', function (e) {
					 $('#newTimer').html(timer.getTimeValues().toString(['days','hours', 'minutes', 'seconds'],' : '));
				});
			</script>
   		
   		</div>
   	<?php } ?>
      
	<div style="clear:both;">	
					
				 <div class="row" style="text-align:left;border:none;text-align:center;">
				
							<?php 
								if(!isset($warNumber))include "drawState.php";	 				
							?>
							
							
							<div >
							<?php

							 if(!isset($_GET['slim']))include "drawChart.php";
							 include "drawMapLinks.php"; 
							 include "drawEventLog.php"; ?>
							 </div> 
							
					 
					 </div>
					 
					<?php	
		 
				  	//if($mapName != 'Conquest_Total' && !isset($warNumber))include "drawMap.php";	
					// if(isset($warNumber))include "drawMapLinks.php";
					
					?>		
				
				
		</div>  
<?php include "drawFooter.php"; ?>

<?php }?>


  </body>
</html>
