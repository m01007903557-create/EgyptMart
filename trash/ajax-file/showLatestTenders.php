<?php
ob_start();
session_start();
include "../common.php";


if($_POST['page'])
{
$page = $_POST['page'];

$cur_page = $page;
$page -= 1;
$per_page = 10; // Per page records
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

if(isset($_COOKIE['loc_id']))
{
	$sql_tnd_ck=" and (
	(tnd_preferred_location='domestic' and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(tnd_preferred_location='any' and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(tnd_preferred_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	/*
	(so_preferred_buyer_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(so_preferred_buyer_location='abroad' and tnd_usr_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/
}
else
{
	$sql_tnd_ck=" and (
	
	(tnd_preferred_location='any')
	or
	(tnd_preferred_location='abroad' and tnd_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	/*(so_preferred_buyer_location='domestic' and tnd_usr_id in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(so_preferred_buyer_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/
}

$sql_inbox="select * from tender,user,business_profile where tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_publish_date<=now() and tnd_due_date>=now() ".$sql_tnd_ck." and tnd_approval_status='1' and  tnd_status='1' order by tnd_publish_date desc LIMIT ".$start.", ".$per_page;

$recObj=mysqli_query($con, $sql_inbox) or die('MySql Error' . mysql_error());

/* -----Total count--- */
$query_pag_num = "SELECT count(*) AS count from tender,user,business_profile where tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_publish_date<=now() and tnd_due_date>=now() ".$sql_tnd_ck." and tnd_approval_status='1' and tnd_status='1'"; // Total records

$result_pag_num = mysqli_query($con, $query_pag_num);
$row = mysqli_fetch_array( $result_pag_num);
$count = $row['count'];
$no_of_paginations = ceil($count / $per_page);
$pagi_string="Page ".($page+1)." of ".$no_of_paginations;
/* ---------------Calculating the starting and endign values for the loop----------------------------------- */
if($cur_page >= 7)
{
    $start_loop = $cur_page - 3;
    if($no_of_paginations > $cur_page + 3)
        $end_loop = $cur_page + 3;
    else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6)
    {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    }
    else
    {
        $end_loop = $no_of_paginations;
    }
}
else
{
    $start_loop = 1;
    if($no_of_paginations > 7)
        $end_loop = 7;
    else
        $end_loop = $no_of_paginations;
}
?>

<?php if($count>0){ ?>
<div class="pbl_top_borderBuy">
 <table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr><td height="33" width="100%">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr><td height="33" width="100%">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr>
<td height="30">
<div class="pbl_liv" style="font-family:arial;color:#474747;font-size:12px"><b><!--Displaying 1 - 1 of total 1	 Open Buy Requirements--></b></div></td><td>
<div class="pbl_liv" align="RIGHT"><b>
<span class="pagenavigation">
            
            <div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start --><?php echo $pagi_string; ?>&nbsp;&nbsp;
            
            <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showTenders('1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showTenders('<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showTenders('<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showTenders('<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
            &nbsp;
            <!-- My PageNavigation end -->
            </div>
            
            </span>
</b></div>

</td>
</tr></tbody></table></td>
</tr></tbody></table>
</td></tr></tbody></table></div>
<div id="bllistinmain"><div id="Listing1"><table class="selectsp1" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>


<?php 	while($row=mysqli_fetch_object($recObj)){	?>
<tr>
<td valign="TOP">
	<div class="mp5 blhd" style="border-right: 1px solid #E7EAEE;">
    	<p>
        	<a href="tender-details.php?id=<?php echo rand(1000,9999).md5($row->tnd_id); ?>" style="cursor:pointer;"><?php echo $row->tnd_heading; ?></a>
            <?php if($row->br_approval_status=='0'){ ?>
            <img src="images/waiting_ico1.png" title="&lt;b&gt;Your Tender is under review by our system&lt;/b&gt;" id="imgWaiting" name="imgWaiting" class="imgWaiting" align="absmiddle">
            <?php } ?>
		</p>
		<div style="padding:0 0 5px 0;font-size:11px;color:#727272;"><b>Published on:</b> <?php echo date("d M, Y",strtotime($row->tnd_publish_date)); ?></div> <?php echo stripslashes($row->br_requirement); ?>
        <div class="blvd"><a href="tender-details.php?id=<?php echo rand(1000,9999).md5($row->tnd_id); ?>" style="cursor:pointer;">View complete details</a></div>
	</div>
</td>
</tr>
<?php	}	?>
</tbody></table></div>
   </div>
<?php	}else{	?>
	<div style="font-size:12px;color:#b60000;padding: 10px 0 10px 0;" align="center">There are no Tender to display.</div>    
<?php	}	?>
    
<?php } ?>
