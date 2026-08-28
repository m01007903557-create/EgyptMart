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

$sql_inbox="select * from buy_requirement,user,business_profile where br_u_id=usr_id and usr_id=bnsprof_uid and br_display_status='1' and br_status='1' and br_u_id='".$_SESSION['uid_indm']."' order by br_updated_date desc LIMIT ".$start.", ".$per_page;

$recObj=mysqli_query($con, $sql_inbox) or die('MySql Error' . mysql_error());

/* -----Total count--- */
$query_pag_num = "SELECT count(*) AS count from buy_requirement,user,business_profile where br_u_id=usr_id and usr_id=bnsprof_uid and br_display_status='1' and br_status='1' and br_u_id='".$_SESSION['uid_indm']."'"; // Total records

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
					<a href="javascript:openRequirement('1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:openRequirement('<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:openRequirement('<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:openRequirement('<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
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
<table class="pbl_bg_topBuy" id="toptitle" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>

<tr>
<td class="pbl_top_mBuy" height="24">Buy Requirement Details</td>
<td class="pbl_top_mBuy" height="24" width="208">Choose Action</td></tr>
</tbody></table></td></tr></tbody></table></div>
<div id="bllistinmain"><div id="Listing1"><table class="selectsp1" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>


<?php 	while($row=mysqli_fetch_object($recObj)){	?>
<tr>
<td><img src="upload/buy_requirement/<?php if($row->br_pic !=''){	echo $row->br_pic;	}else{ echo "no-image.png";	} ?>" id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125"></td>
<td valign="TOP">
	<div class="mp5 blhd" style="border-right: 1px solid #E7EAEE;">
    	<p>
        	<a onclick="detailRequirement(<?php echo $row->br_id; ?>)" style="cursor:pointer;"><?php echo $row->br_pd_name; ?></a>
            <?php if($row->br_approval_status=='0'){ ?>
            <img src="images/waiting_ico1.png" title="&lt;b&gt;Your Buy Requirement is under review by our system&lt;/b&gt;" id="imgWaiting" name="imgWaiting" class="imgWaiting" align="absmiddle">
            <?php } ?>
		</p>
		<div style="padding:0 0 5px 0;font-size:11px;color:#727272;"><b>Posted on:</b> <?php echo date("d M, Y",strtotime($row->br_updated_date)); ?></div> <?php echo stripslashes($row->br_requirement); ?>
        <div class="blvd"><a onclick="detailRequirement(<?php echo $row->br_id; ?>)" style="cursor:pointer;">View complete details</a></div>
	</div>
</td>
<td class="mp5" valign="TOP" width="208">
<!--<div class="bulletImage"><a href="javascript:void(0)" rel="NjM1MTAwMDA4NS0yMDE0MDIxMg==#9061497#Mr.  Rakesh Bose" id="additDetLayer1" class="additDetLayer">Add More Details</a></div>-->
<div class="bulletImage"><a id="apprvDel1" class="apprvDel" style="cursor:pointer;" onClick="closeRequirement(<?php echo $row->br_id; ?>,'op');">Delete</a></div>
</td>
	</tr>
<?php	}	?>
</tbody></table></div>
   </div>
<?php	}else{	?>
	<div style="font-size:12px;color:#b60000;padding: 10px 0 10px 0;" align="center">There are no Open Requirements to manage.</div>    
<?php	}	?>
    
<?php } ?>
