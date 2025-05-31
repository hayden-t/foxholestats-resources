<html><body style="padding-right:10px;">
<?php // content="text/plain; charset=utf-8"
$warNumber = (isset($_GET['war']) ? $_GET['war'] : '63');// >= 63
		include "_settings.php";


		$mysqli = new mysqli($servername, $username, $password, $dbname);
		

	if($warNumber){
		
			$sql = "SELECT * FROM warapi_wars WHERE id = ". intval($warNumber);
			$result = $mysqli->query($sql);	
			$war = mysqli_fetch_assoc($result);				
	
			$sql = "SELECT * FROM warapi_logs WHERE id >= ". $war['logsFrom'] ." AND id <= ". $war['logsTo'] . " AND regionId = 0 AND aux != '' AND aux != '[]'";//
			$result = $mysqli->query($sql);	
		
	}else{
			$sql = "SELECT * FROM warapi_logs_latest WHERE regionId = 0 AND aux != '' AND aux != '[]'";
			$result = $mysqli->query($sql);	
	}
	
	$rows = [];
	
	while($row = mysqli_fetch_assoc($result)){
		$row['aux'] = json_decode($row['aux'],true);
		$rows[] = $row;	
	}
	echo "War: ";
for($i = 63; $i <= 111; $i++){
	echo "<a href='drawProto.php?war=".$i."' >".$i."</a> ";
}	
	
	echo "<br /><br /><br />World Conquest: ".$warNumber;
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.21.0/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<canvas style="width:100%;height:300px;" id="myChart"></canvas>
<canvas style="width:100%;height:300px;" id="myChart4"></canvas>
<!--<canvas style="width:100%;height:300px;" id="myChart5"></canvas>-->
<canvas style="width:100%;height:200px;" id="myChart2"></canvas>

<script>
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
      //  labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [{
            label: 'Wardens',
            backgroundColor: 'transparent',
            borderColor: 'blue',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){					
					echo "{x: new Date(".($row['time']*1000)."), y: ".$row['aux']['players']['wardens']."},".PHP_EOL;
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        },
        {
            label: 'Colonial',
            backgroundColor: 'transparent',
            borderColor: 'green',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){							
					echo "{x: new Date(".($row['time']*1000)."), y: ".$row['aux']['players']['collies']."},".PHP_EOL;
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        },
 {
            label: 'Towns (+ = Warden)',
            backgroundColor: 'transparent',
            borderColor: 'grey',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){					
					echo "{x: new Date(".($row['time']*1000)."), y: ".($row['aux']['towns']['wardens']-$row['aux']['towns']['collies'])."},".PHP_EOL;
				}
	?>
             ],
             yAxisID: 'y-axis-2',
        }
        ]
    },

    // Configuration options go here
       options: {
        scales: {
            xAxes: [{
                type: 'time',
                position: 'bottom',
                time: {
                 unit: 'day',
                 displayFormats: {
                        quarter: 'MMM YYYY'
                    }
                }
            }],
            yAxes: [{
				type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
				display: true,
				position: 'left',
				id: 'y-axis-1',
			}, {
				type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
				display: true,
				position: 'right',
				id: 'y-axis-2',

				// grid line settings
				gridLines: {
					drawOnChartArea: false, // only want the grid lines for one axis to show up
				},
			}],
        }
        }
});
</script>
<script>
var ctx = document.getElementById('myChart4').getContext('2d');
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
      //  labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [{
            label: 'Warden Man Hours',
            backgroundColor: 'transparent',
            borderColor: 'blue',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php $wardenhours = 0;
				foreach($rows as $row){
				$wardenhours += $row['aux']['players']['wardens']*0.5;		
					echo "{x: new Date(".($row['time']*1000)."), y: ".$wardenhours."},";
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        },
        {
            label: 'Colonial Man Hours',
            backgroundColor: 'transparent',
            borderColor: 'green',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php $colliehours = 0;
				foreach($rows as $row){
				$colliehours += $row['aux']['players']['collies'] * 0.5; 							
					echo "{x: new Date(".($row['time']*1000)."), y: ". $colliehours ."},";
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        },
		{
            label: 'Players Difference (+ = more Warden)',
            backgroundColor: 'transparent',
            borderColor: 'grey',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){					
					echo "{x: new Date(".($row['time']*1000)."), y: ".($row['aux']['players']['wardens']-$row['aux']['players']['collies'])."},".PHP_EOL;
				}
	?>
             ],
             yAxisID: 'y-axis-2',
        }
        ]
    },

    // Configuration options go here
       options: {
        scales: {
            xAxes: [{
                type: 'time',                
                position: 'bottom',
                display: false
            }],
            yAxes: [{
				type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
				display: false,
				position: 'left',
				id: 'y-axis-1'
			}, {
				type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
				display: true,
				position: 'right',
				id: 'y-axis-2',

				// grid line settings
				gridLines: {
				//	drawOnChartArea: false, // only want the grid lines for one axis to show up
				},
			}],
        }
        }
});
</script>
<script>
var ctx = document.getElementById('myChart2').getContext('2d');

var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
      //  labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [
                {
            label: 'Queued Wardens',
            backgroundColor: 'transparent',
            borderColor: 'blue',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){							
					echo "{x: new Date(".($row['time']*1000)."), y: ".$row['aux']['queued']['wardens']."},";
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        },
                {
            label: 'Queued Colonials',
            backgroundColor: 'transparent',
            borderColor: 'green',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){							
					echo "{x: new Date(".($row['time']*1000)."), y: ".$row['aux']['queued']['collies']."},";
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        },
 {
            label: 'Join Warden Warning',
            backgroundColor: 'transparent',
            borderColor: 'blue',
            pointRadius: 0,
            borderWidth: 5,
            data: [
    <?php 
				foreach($rows as $row){							
					echo "{x: new Date(".($row['time']*1000)."), y: ".$row['aux']['warning']['wardens']."},";
				}
	?>
             ],
             yAxisID: 'y-axis-2',
        },
                {
            label: 'Join Colonial Warning',
            backgroundColor: 'transparent',
            borderColor: 'green',
            pointRadius: 0,
            borderWidth: 5,
            data: [
    <?php 
				foreach($rows as $row){							
					echo "{x: new Date(".($row['time']*1000)."), y: ".$row['aux']['warning']['collies']."},";
				}
	?>
             ],
             yAxisID: 'y-axis-2',
        }       
        ]
    },

    // Configuration options go here
       options: {
        scales: {
            xAxes: [{
                type: 'time',
                position: 'bottom',
                display: false,
            }],
            yAxes: [{
				type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
				display: true,
				position: 'right',
				id: 'y-axis-1',
			},
			{
				type: 'category', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
				display: false,
				labels: [1,0],
				position: 'right',
				id: 'y-axis-2',
			}
			],
        },responsive: false
        }
});

</script>


<script>
/*var ctx = document.getElementById('myChart5').getContext('2d');
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {    
        datasets: [{
            label: 'Player Imbalance',
            backgroundColor: 'transparent',
            borderColor: 'grey',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){					
					echo "{x: new Date(".($row['time']*1000)."), y: ". ($row['aux']['players']['wardens']-$row['aux']['players']['collies'])/($row['aux']['players']['wardens']+$row['aux']['players']['collies']) ."},".PHP_EOL;
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        },
        {
            label: 'Town Imbalance',
            backgroundColor: 'transparent',
            borderColor: 'purple',
            pointRadius: 0,
            borderWidth: 2,
            data: [
    <?php 
				foreach($rows as $row){					
					echo "{x: new Date(".($row['time']*1000)."), y: ". ($row['aux']['towns']['wardens']-$row['aux']['towns']['collies'])/($row['aux']['towns']['wardens']+$row['aux']['towns']['collies']) ."},".PHP_EOL;
				}
	?>
             ],
             yAxisID: 'y-axis-1',
        }
        ]
    },

    // Configuration options go here
       options: {
        scales: {
            xAxes: [{
                type: 'time',
                position: 'bottom',
                time: {
                 unit: 'day',
                 displayFormats: {
                        quarter: 'MMM YYYY'
                    }
                }
            }],
            yAxes: [{
				type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
				display: true,
				position: 'left',
				id: 'y-axis-1',
			}],
        }
        }
});*/
</script>


 <br />
Warden Hours: <?php echo number_format($wardenhours); ?> Colonial Hours: <?php echo number_format($colliehours); ?>  <br />
IRL Days Difference: <?php echo round(abs($wardenhours-$colliehours)/24); ?> (<?php echo number_format(abs($wardenhours-$colliehours)); ?> hours)
</body>
</html>