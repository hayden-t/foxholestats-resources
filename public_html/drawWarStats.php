<style>
.tablesorter .header, .tablesorter .tablesorter-header {
  padding: 4px 20px 4px 4px;
  cursor: pointer;
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAJCAYAAADdA2d2AAABhWlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV/T1opUHCwi4pChCoIFURFHqWIRLJS2QqsOJpd+QZOGJMXFUXAtOPixWHVwcdbVwVUQBD9AHJ2cFF2kxP81hRYxHhz34929x907QKiXmWr6JgBVs4xkLCpmsqti4BV++DCAMXRJzNTjqcU0XMfXPTx8vYvwLPdzf45eJWcywCMSzzHdsIg3iGc2LZ3zPnGIFSWF+Jx43KALEj9yXXb4jXOhyQLPDBnp5DxxiFgsdLDcwaxoqMTTxGFF1ShfyDiscN7irJarrHVP/sJgTltJcZ3mMGJYQhwJiJBRRQllWIjQqpFiIkn7URf/UNOfIJdMrhIYORZQgQqp6Qf/g9/dmvmpSScpGAX8L7b9MQIEdoFGzba/j227cQJ4n4Erre2v1IHZT9JrbS18BPRtAxfXbU3eAy53gMEnXTKkpuSlKeTzwPsZfVMW6L8Fetac3lr7OH0A0tTV8g1wcAiMFih73eXd3Z29/Xum1d8PUj5ymk1HSpkAAAAGYktHRAAAAAAAAPlDu38AAAAJcEhZcwAALiMAAC4jAXilP3YAAAAHdElNRQfmBQQWKS+8dzdoAAAAGXRFWHRDb21tZW50AENyZWF0ZWQgd2l0aCBHSU1QV4EOFwAAADZJREFUKM9jYCAS3NrW8J9YtYykGqjm1cBIsaHYXEjIYEZyvUyMi6kKaOJS+ocpubHPQIt0CgBeOB4l/LYVEwAAAABJRU5ErkJggg==');
  background-position: center right;
  background-repeat: no-repeat;
}
.sorter-false{background:none!important;}
</style>
<div  style="max-width:500px;padding:5px;text-align:center;margin:10px auto;height: 228px;overflow-y: scroll;border: 1px solid #e3d7c5 !important;">
<?php

		$sql = "SELECT * FROM warapi_wars WHERE logsTo != 0 ORDER BY id DESC";
		$result = $mysqli->query($sql);
		
		$rows = [];
		$totalDays = $collieWins = $wardenWins = $casualties = 0;
		while($war = mysqli_fetch_assoc($result)){
		
			$war['days'] = round(($war['conquestEndTime']-$war['conquestStartTime'])/3600/24,1);
			$rows[] = $war;
			
			if($war['winner'] == 'WARDENS')$wardenWins++;
			if($war['winner'] == 'COLONIALS')$collieWins++;
			
			$totalDays += $war['days'];
			$casualties += $war['casualties'];
		}
	echo "<b>World Conquest History"
			."<br />Wardens ".$wardenWins." v ".$collieWins. " Colonials"	
			."<br />". floor($totalDays/365) ." Years, ".floor(fmod($totalDays, 365))." Days"
			."<br / >".number_format($casualties) . " Casualties <small>(since WC15)</small>"
		."<br /><br /></b>";			
					
	echo "<table style='width:100%;text-align:center;' class='tablesorter'>
		<thead>
			<tr style='font-weight:bold;'>
				<th>War</th>
				<th class='sorter-false'>Start (UTC)</th>
				<th>Days</th>
				<th>Casualties</th>
				<th class='sorter-false'>Winner</th>
			</tr></thead><tbody>";	
		foreach($rows as $war){?>
		
			<tr>
				<td><?php echo $war['id']; ?></td>
				<td><?php echo date('j M Y',$war['conquestStartTime']); ?></td>
				<td><?php echo $war['days']; ?></td>
				<td><?php echo number_format($war['casualties']); ?></td>
				<td><?php echo ucfirst(strtolower ($war['winner'])); ?></td>
			</tr>
		
		<?php }
	echo "</tbody></table>";		
		
		
?>
</div>