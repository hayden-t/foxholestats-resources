<div id="mapLinks">
<h3>Filter By Map:</h3>
<?php

		$link = (isset($_GET['days']) ? "&days=".$days : "");
		$link.= (isset($_GET['slim']) ? "&slim=1" : "");
		$link.= (isset($_GET['full']) ? "&full=1" : "");


echo '<a class="mapLink" href="./?map=Conquest_Total'.$link.'">Conquest_Total</a>';
$goo = $localIndex;
ksort($goo);
foreach($goo as $i => $map){
	$html = '<a class="mapLink" href="./?map='.$i.$link.'">'.$map['name'].'</a>';;
	if(isset($warNumber))echo $html;
	else if($map['id'] >= 20)echo $html;
}

?>
</div>