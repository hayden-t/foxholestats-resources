			
						
						<h3 style="padding-top: 20px;color:#e77c48;">Our Top Commanders!</h3>
							<div class="affiliate" style="margin: 0 auto;">
							<ul style="height: 300px;display: flex;flex-wrap: wrap;justify-content: center;">
								<?php $tooBig = [];
										$sql = "SELECT `affiliateLinks`.`name`, `affiliateHits`.`affiliateId`,COUNT(`affiliateHits`.`id`) AS 'count' 
												FROM `affiliateHits`
												JOIN `affiliateLinks` ON `affiliateLinks`.`id` = `affiliateHits`.`affiliateId`
												GROUP BY `affiliateHits`.`affiliateId`
												ORDER BY `count` DESC" ;
										$affResult = $mysqli->query($sql);
										while($affRow =  mysqli_fetch_assoc($affResult)){		
											if($affRow['count'] > 3000)$tooBig[] = $affRow['name'];
											else echo "<li>".$affRow['name']."<a style='float:right;clear:right;text-decoration:none;' title='Vote' href='/?a=".$affRow['affiliateId']."'><span class='count'>".$affRow['count']."</span> &#8683;</a></li>";
										}
								
								?>
								
							</ul>
							
							</div><div style="color:#e77c48;text-align:center;">
			Overthrown (>3k): <?php echo implode(', ',$tooBig); ?>
			</div>
						<span style="padding-bottom:10px;">Sign up for a commander affiliate account, spread your link to gain rank.</span>
								<form action="index.php" style="" method="post">
									<input placeholder="Enter Name" type="text" style="font-size:16px;text-align:center;margin-top:10px;" name="name" maxlength="15" size="15">
								</form>
								
					

