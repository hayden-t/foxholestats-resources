<?php

?>  
  <style>
  .google-visualization-controls-rangefilter > div{display: flex;}
  .google-visualization-controls-slider-horizontal{width:90%;margin: 0 auto;}
  .google-visualization-controls-rangefilter-thumblabel{display: inline-block;color:#dab680;position: absolute;top: -25px;left: 5%;}
  .google-visualization-controls-rangefilter-thumblabel:nth-of-type(2){right:0;right: 5%;left: unset;}
  .google-visualization-controls-slider-thumb{background:#dab680;border-color:#806f56;cursor:pointer;}
  .google-visualization-controls-slider-handle{background:#7d6139;opacity: 0.5;}
/*  #chart_div{height:400px;} #filter_div{position: relative;top: -14px;}*/
 
  #day_div{text-align:center;padding-bottom: 5px;position: relative;top: -14px;}
/*  .google-visualization-tooltip {
  background:lightgrey!important;
}*/
  </style>
  <script type="text/javascript">
  
  		$(document).on('change','#days', function() {
			//alert( this.value );
			window.location.href = "/index.php?map=<?php echo $mapName; ?>&days="+this.value+"<?php echo (isset($_GET['full']) ? "&full=1" : ""); ?>";
		})		

 
 // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawDashboard);

      // Callback that creates and populates a data table,
      // instantiates a dashboard, a range slider and a pie chart,
      // passes in the data and draws it.
      function drawDashboard() {

		var data1 = new google.visualization.DataTable();	
		
		data1.addColumn('datetime', 'Time');//0
		data1.addColumn('number', 'Warden Players');//1		
		data1.addColumn('number', 'Collie Players');//2
		data1.addColumn('number', 'Warden Captures');//3		
		data1.addColumn('number', 'Collie Captures');//4
		data1.addColumn('number', 'Warden Casualties');//5
		data1.addColumn('number', 'Collie Casualties');//6
		data1.addColumn('number', 'Warden Casualty Rate');//7
		data1.addColumn({type: 'string', role: 'tooltip'});//8
		data1.addColumn('number', 'Collie Casualty Rate');//9
		data1.addColumn({type: 'string', role: 'tooltip'});//10
		data1.addColumn('number', 'Steam Players');//11
		//data1.addColumn('number', 'Total Casualties');//12
		data1.addColumn('number', 'Warden Play Hours');//	
		data1.addColumn('number', 'Collies  Play Hours');//
		data1.addColumn('number', 'Wardens Queued');//	
		data1.addColumn('number', 'Collies Queued');//
		data1.addColumn('number', 'Warden Players More');//
		data1.addColumn('number', 'Collie Players More');//
		data1.addColumn('number', 'Warden Queue Warning');//
		data1.addColumn('number', 'Collie Queue Warning');//
		
			<?php
		
		if($logsTo <= 2075361)	include "drawChartLive.php";
		if($logsTo >= 1672648)	include "drawChartWarapi.php";
			
			?>		
		

        // Create a dashboard.
        var dashboard = new google.visualization.Dashboard(
            document.getElementById('dashboard_div'));

        // Create a range slider, passing some options
        var slider = new google.visualization.ControlWrapper({
          'controlType': 'DateRangeFilter',
          'containerId': 'filter_div',
          'options': {
            'filterColumnLabel': 'Time',
            'ui': {'label': ''},
          }
        });
        var localTime = new Date();
		var tzOffset = localTime.getTimezoneOffset();
		

		
/* ----------------------------------------------------------------------------     */  
        
			 // Create a pie chart, passing some options
			var chart1 = new google.visualization.ChartWrapper({
			  'chartType': 'ComboChart',
			  'containerId': 'chart_div2',
			  'options': { 
			  curveType: 'function',	
			//  title : 'Players, Capturable Locations, Casualties & Casualty Rates - UTC'+(tzOffset < 0 ? "+":"")+ (-tzOffset/60),
			  backgroundColor:  "#2f2f2f",
		//	  isStacked: 'true',	
			 legend:{position: 'top'},
/*			  trendlines: { 4: {labelInLegend: 'Time Trend', visibleInLegend: true,color: '#c46060',title:'Time Trend %'} },*/
			  focusTarget: 'category',
			  titleTextStyle: {color: '#e77c48'},
			  legendTextStyle: {color:'#dab680'},
			  textStyle:{color: '#2f2f2f'},
			  areaOpacity: 0.2,
			  vAxes: {
				0: {textPosition: 'none', viewWindow: {min: 0}},//players
			//	1: { textStyle:{color: '#e4e35a'},minValue : -100, maxValue : 100,format: '#\'%\''},				
				2: {textPosition: 'none'},//day
				3: {textPosition: 'none'},//towns
				4: {textPosition: 'none'},//casualties
				5: {textPosition: 'none'},//rate
			  },
			  hAxes: {
				0: { textStyle:{color: '#dab680'}},
			  },		  
			  vAxis:{
			  gridlines: {
				color: 'transparent'
				},
				viewWindowMode: "explicit",
				viewWindow:{min:0}
			  },
			  hAxis: {
				format:'d MMM h aa',
				color: '#dab680',
					gridlines: {
					color: '#848484'
				}
			  },
			  chartArea:{
				backgroundColor: {
				stroke: '#e3d7c5',
				strokeWidth: 1,
				fill: "#262626"
			},
			 width: '94%',
			  },
			  lineWidth: 1,
			  seriesType: 'area',
			  series: {
				//0: {type: 'line',targetAxisIndex: 2, color: 'transparent'},//day			
				0: {type: 'line',targetAxisIndex: 3, color: '#7ba0e0'},//collietowns					
				1: {type: 'line',targetAxisIndex: 3, color: '#9ee070'},//wardentowns
				2: {type: 'line',lineDashStyle: [8, 4],targetAxisIndex: 4, color: '#7ba0e0'},//casualtiesWardens
				3: {type: 'line',lineDashStyle: [8, 4],targetAxisIndex: 4, color: '#9ee070'},//casualtiesCollies
				4: {type: 'line',lineDashStyle: [3, 4], targetAxisIndex: 5, color: '#7ba0e0'},//wardens rate
				5: {type: 'line',lineDashStyle: [3, 4],targetAxisIndex: 5, color: '#9ee070'},//collies rate
				6: {type: 'line',lineDashStyle: [3, 4],targetAxisIndex: 0, color: '#ffffff'},//steamplayers
				7: {type: 'line',lineDashStyle: [8, 4],targetAxisIndex: 4, color: '#ffffff'},//total casualties

			  }		  
			},
			view: {	
			<?php if($mapName == 'Conquest_Total'){ ?>						 		
					columns: [0,3,4,5,6,7,8,9,10,11],	//,12
			<?php }else{ ?>	
					columns: [0,3,4,5,6,7,8,9,10],	//,12
			<?php } ?>
			}
			});       
			
/* ----------------------------------------------------------------------------     */  

	  
	  <?php if(isset($warNumber) && ($warNumber <= 19 ||  ($warNumber >= 63 && $warNumber <= 111))){?>	

			// Create a pie chart, passing some options
			var chart2 = new google.visualization.ChartWrapper({
			  'chartType': 'ComboChart',
			  'containerId': 'chart_div11',
			  'options': {
			  curveType: 'function',
			//  title : 'Team Numbers',
			  backgroundColor:  "#2f2f2f",
			//  isStacked: 'true',	
			  legend:{position: 'top'},
			/*  trendlines: { 2: {labelInLegend: 'TI Trend', visibleInLegend: true,color: '#c46060',title:'TI Trend %'} },*/
			  focusTarget: 'category',
			  titleTextStyle: {color: '#e77c48'},
			  legendTextStyle: {color:'#dab680'},
			  textStyle:{color: '#2f2f2f'},
			  areaOpacity: 0.4,
			  vAxes: {
				1: { textPosition: 'none',minValue:0,viewWindow: {min: 0}},//players
				0: { textPosition: 'none'},//man hours
			  },
			  hAxes: {
				0: { textStyle:{color: '#dab680'}, textPosition: 'none',},
			  },		  
			  vAxis:{ 
			  gridlines: {
				color: 'transparent'
				},
			  },
			  hAxis: {
				format:'d MMM h aa',
				color: '#dab680',
					gridlines: {
					color: '#848484'
				}
			  },
			  chartArea:{
				backgroundColor: {
				stroke: '#e3d7c5',
				strokeWidth: 1,
				fill: "#262626"
			},
			 width: '94%',
			  },
			  lineWidth: 1,
			 // seriesType: 'area',
			  series: {
				0: {type: 'line',targetAxisIndex: 1, color: '#7ba0e0'},//wardens		
				1: {type: 'line',targetAxisIndex: 1, color: '#9ee070'},//collies		
				2: {type: 'line',targetAxisIndex: 0,lineDashStyle: [8, 4], color: '#7ba0e0'},//wardens hours		
				3: {type: 'line',targetAxisIndex: 0, lineDashStyle: [8, 4],color: '#9ee070'},//collies hours
				4: {type: 'area',targetAxisIndex: 1, color: '#7ba0e0'},//difference warden,lineDashStyle: [0, 1]
				5: {type: 'area',targetAxisIndex: 1, color: '#9ee070'},//difference collie,lineDashStyle: [0, 1]
			  }		  
			},
			view: {
			<?php if($mapName == 'Conquest_Total'){ ?>	
				columns: [0,1,2,12,13,16,17]
			<?php }else{ ?>
				columns: [0,1,2]
			<?php } ?>
			}		
			});
			
		/* ----------------------------------------------------------------------------     */  			
			
			// Create a pie chart, passing some options
			var chart3 = new google.visualization.ChartWrapper({
			  'chartType': 'ComboChart',
			  'containerId': 'chart_div3',
			  'options': {
			  curveType: 'function',
			//  title : 'Team Numbers',
			  backgroundColor:  "#2f2f2f",
			  isStacked: 'true',	
			  legend:{position: 'top'},
			/*  trendlines: { 2: {labelInLegend: 'TI Trend', visibleInLegend: true,color: '#c46060',title:'TI Trend %'} },*/
			  focusTarget: 'category',
			  titleTextStyle: {color: '#e77c48'},
			  legendTextStyle: {color:'#dab680'},
			  textStyle:{color: '#2f2f2f'},
			  areaOpacity: 0.8,
			  vAxes: {
				1: { textPosition: 'none', textStyle: {color: '#dab680'}},//queued //, minValue:1,viewWindow: {min: 0}
				0: { textPosition: 'none',maxValue:6, direction: -1,minValue:0,viewWindow: {min: 0}},//warning
			  },
			  hAxes: {
				0: { textStyle:{color: '#dab680'}, textPosition: 'none',},
			  },		  
			  vAxis:{ 
			  gridlines: {
				color: 'transparent'
				},
			  },
			  hAxis: {
				format:'d MMM h aa',
				color: '#dab680',
					gridlines: {
					color: '#848484'
				}
			  },
			  chartArea:{
				backgroundColor: {
				stroke: '#e3d7c5',
				strokeWidth: 1,
				fill: "#262626"
			},
			 width: '94%',
			  },
			  lineWidth: 1.5,
			 // seriesType: 'area',
			  series: {
				0: {type: 'area',targetAxisIndex: 1, color: '#7ba0e0',lineDashStyle: [0, 1]},//wardens queued
				1: {type: 'area',targetAxisIndex: 1, color: '#9ee070',lineDashStyle: [0, 1]},//collies
				2: {type: 'line',targetAxisIndex: 0, color: '#7ba0e0'},//wardens		
				3: {type: 'line',targetAxisIndex: 0, color: '#9ee070'},//collies	
			  }		  
			},
			view: {
			<?php if($mapName == 'Conquest_Total'){ ?>	
				columns: [0,14,15,18,19]//
			<?php }else{ ?>
				columns: [0]
			<?php } ?>
			}		
			});
			
			 <?php }//team splits ?>
			 
			 
	/* ----------------------------------------------------------------------------     */  		 
			 
       
        dashboard.bind(slider, chart1);
       <?php if(isset($warNumber) && ($warNumber <= 19 ||  ($warNumber >= 63 && $warNumber <= 111))){?>		
         dashboard.bind(slider, chart2); 
          <?php if($warNumber >= 63 && $warNumber <= 111){?>		
				dashboard.bind(slider, chart3); 
			<?php }//team splits ?>        	
       <?php }//team splits ?>        
        // Draw the dashboard. 
        dashboard.draw(data1);       

      }
    </script>


<div class="row" style="position:relative;">
    <!--Div that will hold the dashboard-->
    <div id="dashboard_div">
          


		    

    

		<div id="chart_div2" class='chart' style="height:300px;"></div>    
     <?php if(isset($warNumber) && ($warNumber <= 19 ||  ($warNumber >= 63 && $warNumber <= 111))){?>	
		<div id="chart_div11" class='chart' style="height:300px;"></div>  
		<?php if($warNumber >= 63 && $warNumber <= 111){?>		
			<div id="chart_div3" class='chart' style="height:300px;"></div>    
			<?php } ?>        
	<?php } ?>
	<div style="text-align:center;">Graph does not update live, you must refresh the page to get the latest data.</div>
 	<div id="day_div" style=""><br />
		<label for="days"><b>Show: </b></label>
			<select id="days" style="font-size:15px;">
				<?php	
					
				echo "<option ".(1==$days ? "selected='selected'": "" )." value='1'>1 Days</option>";
				echo "<option ".(3==$days ? "selected='selected'": "" )." value='3'>3 Days</option>";
				echo "<option ".(5==$days ? "selected='selected'": "" )." value='5'>5 Days</option>";
				echo "<option ".(7==$days ? "selected='selected'": "" )." value='7'>7 Days</option>";
				echo "<option ".(999==$days ? "selected='selected'": "" )." value='999'>WC". $warState['id'] ."</option>";
		
				for($i = $warState['id']-1; $i  >= ($settings["first_wc"] ? $settings["first_wc"] : 1); $i--){
					echo "<option ".('WC'.$i==$days ? "selected='selected'": "" )." value='WC".$i."'>WC".$i.(($i <= 19 ||  ($i >= 63 && $i <= 111)) ? '**' : '')."</option>";
				}


				//echo "<option ".('All'==$days ? "selected='selected'": "" )." value='All'>All Past</option>";
				 ?>
				
			</select> <span style="color:#4ffff7">** indicates war with population data !</span>
	</div>	 

	
        <div id="filter_div">
       
       </div>
  

    </div>

    	</div>  