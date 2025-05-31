
   

   <?php if(!isset($_GET['slim'])){ ?>		  
	<div class="widgets" style="display:flex;flex-wrap:wrap;justify-content:center;padding: 0 40px;">

<?php if($dbname == 'foxholestats'){?>
		<div  style="border: 1px solid #e3d7c5 !important;color:#dab680;text-align:center;">
			<?php include "drawAffiliates.php"; ?>
		</div>
<?php } ?>
			

	

	
	
			<!--<div style="width:400px;margin:10px;text-align:center;border: 1px solid #e3d7c5 !important;">
<a name="widgets" ></a>
			<iframe src="https://titanembeds.com/embed/445506700812877835?theme=DiscordDark&defaultchannel=445506701559332865" height="600" width="100%" frameborder="0" style=""></iframe>
		Discord based shoutbox,  click "login" and enter a guest name, or go direct to our discord server via this invite link: <br /><a target="_blank" href="https://discord.gg/dnegnws">https://discord.gg/dnegnws</a><br />
			</div> 
			-->
<?php } ?>	 



	 </div>   
	 

		
    
<div class="footer" style="padding: 30px 0;clear:both;text-align:center;">


<div style="display:flex;flex-wrap:wrap;justify-content: center;">
		   <?php if(!isset($_GET['slim'])){ ?>		  
			<div style="text-align:center;margin:10px;">     
				<script type='text/javascript' id='clustrmaps' src='//cdn.clustrmaps.com/map_v2.js?cl=dddddd&w=400&t=m&d=JL_ZNDw7qhvCcld7jmPLXFQ08U47VptghH4IDQQaqKs&co=3e3e3e&cmo=d1ac74&cmn=e77c48&ct=ffffff'></script>
				(click map for a large detailed live map)
			</div>
	<!--	<div>This is a test foxhole community stream:<br / ><iframe src="https://player.twitch.tv/?channel=chrisincontact&parent=foxholestats.com" frameborder="0" allowfullscreen="true" scrolling="no" height="378" width="620"></iframe>
           </div>-->
           <?php } ?> 
           
		<div style="text-align:center;margin:10px;">     
		<?php include "drawWarStats.php"; ?> 
		</div>
</div>
<?php if(isset($_GET['slim'])){ ?>
<b>You are viewing the Slim Mobile Version of the site.
<br /> <a href="<?php echo $domain; ?>/index.php?full=1" >Click here for full version.</a></b>
<?php }else{ ?>

For a fast <a href="<?php echo $domain; ?>/index.php?slim=1" >slim version of this site click here</a> (no charts or widgets)<?php } ?>

<br />
   <div style="text-align:center;">
 <br /> 
	 <a style="color:white"  target="_blank" href="https://github.com/pickles976/FoxholeProjects" >Foxhole Projects List</a>
		
	  </div>
<br /><br />
Data collected via the <a target="_blank" href="https://github.com/clapfoot/warapi">Foxhole API</a>, <i> <a href="http://www.foxholegame.com/" target="_blank" >Foxhole</a> is a registered trademark of <a href="http://www.foxholegame.com/" target="_blank" >Siege Camp</a>, used on this website with their permission.</i>
<br /><br /><br /><br />
<i><a href="https://youtu.be/ChhW3pa6wgg" target="_blank">"Stay Foxy"</a><br /><small>(dont click)</small></i>
<br /><br /><br /><br />

</div>
