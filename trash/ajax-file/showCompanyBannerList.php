<?php
ob_start();
session_start();
include "../common.php";

$cb_bnsprof_id=trim($_POST['cb_bnsprof_id']);

?>
<script type="text/javascript">
function showDelButt(id)
{
	$("#butt_area_"+id).show();
}
function hideDelButt(id)
{
	$("#butt_area_"+id).hide();
}
</script>
<?php

if($_POST['page'])
{
$page = $_POST['page'];

$cur_page = $page;
$page -= 1;
$per_page = 5; // Per page records
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

$sql="select * from company_banner where cb_bnsprof_id='".$cb_bnsprof_id."' and cb_status='1' order by cb_id desc LIMIT ".$start.", ".$per_page;
$recObj=mysqli_query($con, $sql) or die('MySql Error' . mysql_error());

/* -----Total count--- */
$query_pag_num = "SELECT count(*) AS count from company_banner where cb_bnsprof_id='".$cb_bnsprof_id."' and cb_status='1'"; // Total records

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

if($count>0)//next, prev button area
{
?>
<div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="margin-bottom:15px;margin-top:15px;text-align:right">
<div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start --><?php echo $pagi_string; ?>
            
			 <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
            
            <!-- My PageNavigation end -->
            </div>
</div>
<?php
}

if($count>0)
{
	while($row=mysqli_fetch_object($recObj))
	{ 
?>
		<div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="margin-bottom:10px;" onmouseover="showDelButt(<?php echo $row->cb_id; ?>);" onmouseout="hideDelButt(<?php echo $row->cb_id; ?>);">
        <span style="float:right;margin-top:-18px;margin-right:-28px;display:none;width:28px;position:static" id="butt_area_<?php echo $row->cb_id; ?>">
        	<a style="cursor:pointer;" title="Delete Video" onclick="delBanner(<?php echo $row->cb_id; ?>,<?php echo $row->cb_bnsprof_id; ?>);"><img src="images/close_m2.png" /></a>
        </span>
		<div class="c3"></div>
        <!--https://www.youtube.com/watch?v=Vf2fHbzToD0-->
        <!--<iframe width="315" height="315" src="//www.youtube.com/embed/Vf2fHbzToD0" frameborder="0" allowfullscreen></iframe>-->
		<img src="upload/company_banner/<?php echo $row->cb_image; ?>" width=100"" Height=100"/>
		



		<div class="c3"></div>
        
		</div>
<?php }
}
else
{
	?>
		<div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="color:#F00;text-align:center">
		<div class="c3"></div>
        No Video listed by you.
		<div class="c3"></div>
		</div> 
<?php
}
if($count>0)//next, prev button area
{
?>
<div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="margin-bottom:15px;margin-top:15px;text-align:right">
<div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start --><?php echo $pagi_string; ?>
            
			 <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showBannerList(<?php echo $cb_bnsprof_id; ?>,'<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
            
            <!-- My PageNavigation end -->
            </div>
</div>
<?php
}
?>

<?php } ?>