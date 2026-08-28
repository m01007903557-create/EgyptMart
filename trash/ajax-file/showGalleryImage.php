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

$sql_active="select * from photo where ph_status = '1' and ph_u_id = '".$_SESSION['uid_indm']."' order by ph_id desc LIMIT ".$start.", ".$per_page;

$recObj=mysqli_query($con, $sql_active) or die('MySql Error' . mysql_error());

/* -----Total count--- */
$query_pag_num = "SELECT count(*) AS count from photo where ph_status = '1' and ph_u_id = '".$_SESSION['uid_indm']."'"; // Total records

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
<script type="text/javascript" src="../js/jquery-1.2.1.min.js"></script>
<div id="active_offer">
<div class="pbl_top_border1">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>
		<td background="images/gray-line.gif"><img src="images/gray-line.gif" height="2" width="1"></td>
				<td height="33" width="100%">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tbody><tr>
					<td height="30">
					<div class="pbl_liv"><b>
			<script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
			<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
			<script type="text/javascript">
				jQuery(function(){
					jQuery('#file_upload').uploadifive({
						'auto'     : true,
						'formData' : {'id' : '<?php echo $_SESSION['uid_indm']; ?>'},
						'queueID'  : 'queue',
						'debug'    : true,
						'method'   : 'post',
						'uploadScript' : 'ajax-file/uploadGalleryImage.php',
						'onAddQueueItem' : function(file) {
							//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
							//$("#drop").html('<img src="images/loading.gif" alt="Uploading...." />');
						},
						'onUploadComplete' : function(file,data) {
							showGalleryImage(1);
						}
					});
				});
			</script>
            <div id="drop" style="padding-left:10px;">
            <input type="file" id="file_upload" name="file_upload"/>
            </div>
            <div id="queue"></div>
                </b></div>
                    </td>
					<td>
					<div class="pbl_liv" align="RIGHT">
                     <?php if($count>0){ ?>
            <span class="pagenavigation">
            
            <div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start --><b><?php echo $pagi_string; ?></b>&nbsp;&nbsp;
            
            <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showGalleryImage('1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showGalleryImage('<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showGalleryImage('<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showGalleryImage('<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
            &nbsp;
            <!-- My PageNavigation end -->
            </div>
            
            </span>
            <?php	}	?>
                    <b></b></div></td>
					</tr>
					</tbody></table></td>
				</tr>
				</tbody></table>
			
				<table class="pbl_bg_top1" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody><tr>
					<td class="pbl_top_m" height="24" width="115">Image</td>
					<td class="pbl_top_m" height="24" width="162">Posting Date</td>
					<td class="pbl_top_m" height="24" width="122">Choose Action</td>
				</tr>
				</tbody></table>
				</div>
                <table class="select_sp" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tbody>
            <?php
			
			if($count>0){
			while($row_ph=mysqli_fetch_object($recObj))
			{
			?>
            <tr><td align="CENTER" valign="top" width="122">
				<div style="border:1px solid #FFCACA; width:100px;line-height:100px;margin:10px auto;">
                <?php if($row_ph->ph_fileName!=''){ ?>
				<img src="upload/image_gallery/<?php echo $row_ph->ph_fileName; ?>" id="6363630246_1" style="margin-right:5px;" border="0" height="74" hspace="0" vspace="0" width="100">
                <?php }else{ ?>No Image<?php } ?>
				<div id="6363630246_1_H" vspace="0" hspace="0" style="display:none;position:absolute;top:0;left:0;width:0;height:0;background:#FFFFFF;" height="90">
				</div>
				</div></td>

				<td class="mp5" valign="TOP" width="160"><?php echo date("d M,Y",strtotime($row_ph->ph_updated_date)); ?><br></td>
				<td class="mp5" valign="TOP" width="120"><img src="images/rmv.gif" alt="Remove" align="ABSMIDDLE" height="10" hspace="5" vspace="5" width="9"><a onclick="delGalleryImage(<?php echo $row_ph->ph_id; ?>);" style="cursor:pointer;">Delete</a></td>
			</tr>
            <?php	}
			}
			else
			{
			?>
            <tr><td colspan="4" align="center" style="vertical-align:middle;color:#F00;padding-top:10px; padding-bottom:10px;">No Image</td></tr>
            <?php	}	?>
			</tbody></table>
            
</div>
<?php } ?>