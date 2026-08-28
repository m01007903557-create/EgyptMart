<li id="thinColumn">
	<div align="center">
		</div>	
		<br>
		<section class="box2"title=" الاتصال بالشركة ">
		<div class="h3"><h3>Contact Details</h3></div>
		<nav>
		<b>
		<?php if($row->bnsprof_ceoprefix!='' && $row->bnsprof_ceofname!='' && $row->bnsprof_ceolname!=''){	echo $row->bnsprof_ceoprefix." ".$row->bnsprof_ceofname." ".$row->bnsprof_ceolname; ?>
(CEO)              
<?php }else{
	echo $row->name_prefix." ".$row->fname." ".$row->lname;	} ?>	</b>
					<p class="mb5px mt5px"><BR>
<?php echo $row->bnsprof_address1.", ".$row->bnsprof_address2; ?>
<BR>
<?php if($row->bnsprof_city!='0'){ echo get_city_name($row->bnsprof_city).", "; } ?>
<?php if($row->bnsprof_state!='0'){ echo get_state_name($row->bnsprof_state).", "; } ?>
<?php echo get_country_name($row->country); ?>
<?php
if($row->bnsprof_ph1!=''){		?>
<br>Phone: <?php echo $row->country_ph_code; if($row->bnsprof_phcode1!=''){ ?>-<?php echo $row->bnsprof_phcode1; } ?>-<?php echo $row->bnsprof_ph1; ?>
<?php
}
?>
<?php
if($row->bnsprof_ph2!=''){		?>
<?php if($row->bnsprof_ph1!=''){ echo ", "; }echo $row->country_ph_code; if($row->bnsprof_phcode2!=''){ ?>-<?php echo $row->bnsprof_phcode2; } ?>-<?php echo $row->bnsprof_ph2; ?>
<?php
}
?>
<?php
if($row->bnsprof_ph3!=''){		?>
<?php if($row->bnsprof_ph1!='' || $row->bnsprof_ph2!=''){ echo ", "; }echo $row->country_ph_code; if($row->bnsprof_phcode3!=''){ ?>-<?php echo $row->bnsprof_phcode3; } ?>-<?php echo $row->bnsprof_ph3; ?>
<?php
}
?>
<?php
if($row->bnsprof_ph4!=''){		?>
<?php if($row->bnsprof_ph1!='' || $row->bnsprof_ph2!='' || $row->bnsprof_ph3!=''){ echo ", "; }echo $row->country_ph_code; if($row->bnsprof_phcode4!=''){ ?>-<?php echo $row->bnsprof_phcode4; } ?>-<?php echo $row->bnsprof_ph4; ?>
<?php
}
?>

<?php
if($row->bnsprof_fax1!=''){		?>
<br>Fax: <?php echo $row->country_ph_code; ?>-<?php echo $row->bnsprof_fax1; ?>
<?php
}
?></p>
<p class="read"><a href="enquiry.php?c=<?php echo $c; ?>"><span class="rA" style="color: darkblue;">>></span> More detail</a></p>
		</nav>
		</section><br>
				<section class="box2"title=" المنتجات الهامة للشركة ">
        <div class="h3"><h3>Hot Products</h3></div>
        <div class="pro">
		<ul>			
<?php
$sql_pd_right="select * from products where  pd_uid='".$row->usr_id."' and pd_status='1' and pd_hot='1'";
$res_pd_right=mysql_query($sql_pd_right);
if(mysql_num_rows($res_pd_right)>0)
{	
while($row_pd_right=mysql_fetch_object($res_pd_right)){
	?>
					<li><a href="product-details.php?token=<?php echo rand(1000,9999).md5($row_pd_right->pd_id); ?>&c=<?php echo $c; ?>" title="<?php echo $row_pd_right->pd_title; ?>"><span class="rA" style="color: darkblue;">>></span> <?php echo $row_pd_right->pd_title; ?></a></li>
<?php }} ?>
	</ul>
		
		</div></section><br>
				<section class="box2" title=" منتجات الشركة" >
        <div class="h3"><h3>Other Products</h3></div>
        <div class="pro">
		<ul>			
<?php
$sql_pd_right="select * from products where  pd_uid='".$row->usr_id."' and pd_status='1' and pd_hot='0'";
$res_pd_right=mysql_query($sql_pd_right);
if(mysql_num_rows($res_pd_right)>0)
{	
while($row_pd_right=mysql_fetch_object($res_pd_right)){
	?>
					<li><a href="product-details.php?token=<?php echo rand(1000,9999).md5($row_pd_right->pd_id); ?>&c=<?php echo $c; ?>" title="<?php echo $row_pd_right->pd_title; ?>"><span class="rA" style="color: darkblue;">>></span> <?php echo $row_pd_right->pd_title; ?></a></li>
<?php }} ?>
					</ul>
		
		</div></section><br>
				</li>