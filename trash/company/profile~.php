<?php include "includes/header.php"; 
?>
<div id="body">
	<ul class="cb">
<?php 
$sql_wc="select * from website_content where wc_usr_id='".$row->usr_id."'";
$res_wc=mysql_query($sql_wc);
$row_wc=mysql_fetch_object($res_wc);
?>	
	<li id="wideColumn">
		<br>
		<section class="box1">
		<div class="h2"><h2>Company Facts</h2></div>
		<nav class="comFact">		
					<p><span>Business Type </span> <span>
<?php
$sql="select * from business_profile,user,ownership_type,revenue_turnover where bnsprof_uid=usr_id and bnsprof_owntype=owntyp_id and bnsprof_turnover=revturnover_id and md5(bnsprof_id)='".$id."'";
$res=mysql_query($sql);
$row=mysql_fetch_object($res);

$bnsprof_businesstype=array();
$bnsprof_businesstype=explode(",",$row->bnsprof_businesstype);
$sql_btype="select * from business_type where bsntyp_id in(".$row->bnsprof_businesstype.")";
$res_btype=mysql_query($sql_btype);
if(mysql_num_rows($res_btype)>0)
{
?>
<?php while($row_btype=mysql_fetch_object($res_btype)){	?>
 <?php echo $row_btype->bsntyp_title; ?>/
<?php } } ?></span></p>
<P><span>Establishment</span><span><?php echo $row->bnsprof_yoe; ?></span></p>
<P><span>No. of Employees</span><span><?php echo $row->bnsprof_comemp;?></span></p>
<P><span>Legal Status</span><span><?php echo $row->owntyp_title; ?></span></p>
<P><span>Turnover</span><span><?php echo $row->revturnover_title; ?></span></p>
<?php 
if($row->bnsprof_designation!=''){
	$sql_desig="select * from designation where desig_id='".$row->bnsprof_designation."'";
	$res_desig=mysql_query($sql_desig);
	
if(mysql_num_rows($res_desig)>0){ 
$row_desig=mysql_fetch_object($res_desig);
?>
<P><span><?php echo $row_desig->desig_title; ?></span><span><?php echo $row->name_prefix." ".$row->fname." ".$row->lname; ?></span></p>
<?php	}
}
?>
<P><span>Registration No.</span><span><?php echo $row->bnsprof_regno; ?></span></p>
<P><span>PAN No.</span><span><?php echo $row->bnsprof_pan_no; ?></span></p>
<P><span>Sales Tax No.</span><span><?php echo $row->bnsprof_cst_no; ?></span></p>
<P><span>VAT Registration No.</span><span><?php echo $row->bnsprof_vat_no; ?></span></p>
<P><span>Excise Reg. No.</span><span><?php echo $row->bnsprof_excisereg_no; ?></span></p>
<P><span>Service Tax No.</span><span><?php echo $row->bnsprof_svtax_no; ?></span></p>
					</nav>
	    </section><br>
		
<br>
<style>
    
.info_table_right img{
    border: 2px solid black;
    }
    </style>
   
   <?php
   $abtsql=mysql_query("select * from about_us,profile_heading where abtus_ph_id=ph_id and abtus_wc_id='".$row_wc->wc_id."'"); 
   $totalabt=mysql_num_rows($abtsql);
   if($totalabt>0)
   {	 
		while($abtrow=mysql_fetch_object($abtsql))
		{
			?>
			
			<section class="box1">
		<div class="h2"><h2><?php echo $abtrow->ph_title; ?> </h2></div>	
                <div class="info_table">
					<div class="info_table_left">
						<?php echo $abtrow->abtus_desc; ?>
					</div>
					<div class="info_table_right" style='border:none;'>
						<?php
						  if($abtrow->abtus_image!="")
						  {
							?>
	                            <img src="<?php echo BASE_URL ?>/upload/myprofile/<?php echo $abtrow->abtus_image; ?>" id="img_small_form_1671511">
                       <?php } else { ?>
       
                         <img src="<?php echo BASE_URL ?>/images/noimage.jpg" id="img_small_form_1671511" >
	
        <?php } ?>
					</div>
					
				</div>
		</section>
   <br>
   <?php
		
		}
	}
	
	?>
	
<?php
$sql="select * from business_profile,user,ownership_type,revenue_turnover where bnsprof_uid=usr_id and md5(bnsprof_id)='".$id."'";
$res=mysql_query($sql);
$row=mysql_fetch_object($res);
?>
	</li><?php include "includes/right.php"; ?>
</ul>
	</div>
	<?php include "includes/footer.php"; ?>
</body></html>