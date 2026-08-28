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

//$sql_inbox="select * from message,user where msg_to='".$_SESSION['uid_indm']."' and msg_from=usr_id and msg_to_status='1' order by msg_date desc LIMIT ".$start.", ".$per_page;
$sql_inbox="select * from message m LEFT JOIN user u ON m.msg_from=u.usr_id LEFT JOIN business_profile bf ON bf.bnsprof_uid=u.usr_id LEFT JOIN admin_user au ON m.msg_from = au.id where msg_to='".$_SESSION['uid_indm']."' and msg_to_status='1' order by msg_date desc LIMIT ".$start.", ".$per_page;


$recObj=mysqli_query($con, $sql_inbox) or die('MySql Error' . mysql_error());

/* -----Total count--- */
//$query_pag_num = "SELECT count(*) AS count from message,user where msg_to='".$_SESSION['uid_indm']."' and msg_from=usr_id and msg_to_status='1'"; 
$query_pag_num = "SELECT count(*) AS count from message where msg_to='".$_SESSION['uid_indm']."' and msg_to_status='1'"; // Total records

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

			<div class="fl_m2 my_m2">
				<div class="fl_m2">
					<!--div class="bc f11">Enquiries &#187;</div-->
				</div>
			</div>
			<span id="fol_m2"><h1>Inbox</h1></span><!-- My Mailbox start -->
  

<div class="fl_m2 my_m2" id="inbox" align="left">
            <br>
            <div id="yeartabs"><div class="tabs d"><ul id="date-list" class="tabs clearfix"></ul></div></div><div class="b11_m2 tmsg_m2" id="dymesg" align="center" style="height:2px;">
				<div id="loading" class="c2_m2 bo_m2 lh_m2" style="width: 15%; display: none;">
					<img src="images/my2-loading.gif" class="loading_m2">&nbsp;Loading...&nbsp;
				</div>
				<div id="noselect" class="c2_m2 bo_m2 lh_m2" style="display:none;width:25%">
					&nbsp;No Enquiry Selected.&nbsp;
				</div>
				<div id="fc_m2" class="c2_m2 bo_m2 lh_m2" style="display:none;width:25%">
					&nbsp;Folder has been created.&nbsp;
				</div>
				<div id="fr_m2" class="c2_m2 bo_m2 lh_m2" style="display:none;width:25%">
					&nbsp;Folder has been renamed.&nbsp;
				</div>
				<div id="fd_m2" class="c2_m2 bo_m2 lh_m2" style="display:none;width:25%">
					&nbsp;Folder has been deleted.&nbsp;
				</div>
			</div>
            <div id="pnavsec">
            <div class="b11_m2 b7_m2">
            
            <?php if($count>0){ ?>
            <span class="pagenavigation">
            
            <div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start --><?php echo $pagi_string; ?>&nbsp;&nbsp;
            
            <?php
			
			// FOR ENABLING THE FIRST BUTTON
				if ($first_btn && $cur_page > 1) {	?>
					<a href="javascript:showInbox('1')"><img id="firstmail" src="images/firsten.gif"></a>
			<?php	} else if ($first_btn) {	?><img id="firstmail" src="images/first.gif"><?php	}
			?>&nbsp;<?php
			
             // FOR ENABLING THE PREVIOUS BUTTON
	            if ($previous_btn && $cur_page > 1){
                 $pre = $cur_page - 1;
            ?><a href="javascript:showInbox('<?php echo $pre; ?>')"><img id="prevmail" src="images/prven.gif"></a><?php	
			}else if($previous_btn){	
			?><img id="prevmail" src="images/prevmail.gif"><?php	}	
			?>&nbsp;<?php
			
			// TO ENABLE THE NEXT BUTTON
                if($next_btn && $cur_page < $no_of_paginations){
  	                $nex = $cur_page + 1;
   	        ?><a href="javascript:showInbox('<?php echo $nex; ?>')"><img id="nextmail" src="images/nxten.gif"></a><?php
                }else if ($next_btn){
            ?><img id="nextmail" src="images/nextmail.gif"><?php	}
			?>&nbsp;<?php
             // TO ENABLE THE END BUTTON
			 
			if ($last_btn && $cur_page < $no_of_paginations) {	
			?><a href="javascript:showInbox('<?php echo $no_of_paginations; ?>')"><img id="lastmail" src="images/lastenv.gif"></a>
			<?php	}else if ($last_btn) {	?><img id="lastmail" src="images/last.gif"><?php	}
			?>
            &nbsp;
            <!-- My PageNavigation end -->
            </div>
            
            </span>
            <?php	}	?>
            
            <div style="clear: both;"></div></div></div>
            
            <div id="mhmdd">
            <div class="f4_m2 b5_m2 b11_m2 b10_m2 b7_m2 bg2_m2 ac_m2 p6_m2 hh_m2 b18_m2" id="mainheader">
            <div id="mailboxheader"><div id="selectfolder" class="box_m2">
			<div id="mailboxoptions" class="fl_m2 lh5_m2">
            
            <span id="backdrop"></span>
            </div>
            <div class="fl_m2 lh5_m2" id="mailoption">
            	<span id="reply_m2"></span>
                <div class="fl_m2 hh_m2 b17_m2" style="display:none;" id="muldiv"></div>
		
        <div class="fl_m2 hh_m2 b17_m2" id="deldiv"></div>
		<div id="delete" class="horizontalcssmenu_delete_m2 horizontalcssmenu_m2 fl_m2 lh4_m2">
			<ul id="delete_m2" style="margin-top: 0px; padding-top: 0pt;">
				<li style="z-index: 0; margin: 0px; padding: 0pt;"> 
					<span id="deleteall_m2"><a title="Delete" iscontextmenu="true" onclick="deleteInbox()"></a></span>
				</li>
			</ul>
		</div>
		<div class="fl_m2" id="derdiv" style="border-right:1px solid #ead5ff;height:24px">&nbsp;&nbsp;</div></div></div></div></div><div class="b11_m2" id="ci_m2"><br></div></div><div class="b11_m2" id="repseq"></div>
        
	<span class="mailbox">
    <form name="m_inbox" id="m_inbox" method='post'>
   <?php if($count>0){ ?>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody>
            <tr class="f1_m2">
                 <td class="lh_m2 msg_m2 b9_m2 b10_m2">
                 <table cellpadding="0" cellspacing="0" width="100%">
                 <tbody>
                 
                 <tr class="c1_m2 bo_m2 bg_m2 ff_m2 f3_m2">
                 
                 <td class="p2_m2 sb_m2 b_m2" width="3%"><input class="select-all" name="check_all" value="yes" id="check_all" type="checkbox" onClick="return checkedAll_inbox();"></td>
                 <td class="b_m2 sb_m2 p1_m2 dp_m2" width="43%">Sender&nbsp;</td>
                 <td class="sb_m2 p_m2" width="40%">Subject&nbsp;</td>
                 <td class="sb_m2 p_m2" width="11%">Date&nbsp;</td>
            </tr>
<?php 	while($row=mysqli_fetch_object($recObj)){	?>
			<tr class="f1_m2" id="mail<?php echo $row->msg_id; ?>" style="cursor:pointer;<?php if($row->msg_read == "0"){ ?>background-color:#F5ECFF;<?php } ?>" onmouseover="document.getElementById(this.id).className='f1_m2 mailbc_m2';" onmouseout="document.getElementById(this.id).className='f1_m2';">
<td class="p2_m2"><div class="td_m2"><input id="cbI" name="cbI" type="checkbox" value="<?php echo $row->msg_id; ?>" ></div></td>

<td class="p1_m2" onclick="openmail('<?php echo $row->msg_id; ?>','inbox')"><div class="td_m2" style="width:200px"><?php 
if($row->bnsprof_compname != ''){
	echo $row->bnsprof_compname;
}
else if($row->username != '') {
echo $row->username;
}
else {
echo $row->name_prefix." ".$row->fname." ".$row->lname;
} ?></div></td>
<td class="p_m2" onclick="openmail('<?php echo $row->msg_id; ?>','inbox')">
			<div class="td_m2" style="width:320px" title="Click to open this Enquiry">
            <?php if($row->msg_subject!=''){ 
            	echo stripslashes($row->msg_subject);
            	}else{ ?>
			No Subject
            <?php } ?>
            </div>
			</td>
            <td class="p_m2" onclick="openmail('<?php echo $row->msg_id; ?>','inbox')"><div class="td_m2"><?php echo date("d M Y",strtotime($row->msg_date)); ?></div></td>
            </tr>

<?php	}	?>
		</tbody>
		</table>
    </td></tr></tbody>
    </table>
    <?php
}
else
{	?>
	
    <table cellpadding="0" cellspacing="0" width="100%">
			<tbody><tr class="f1_m2"><td><div class="bo_m2 f3_m2 b9_m2 b12_m2 lh1_m2" id="nor_m2" align="center">There are no messages in your Inbox.</div><table cellpadding="0" cellspacing="0" width="100%"><tbody><tr class="co_m2 bo_m2"><td></td></tr></tbody></table></td></tr></tbody>
	</table>
            
<?php	}	?>
    </form>
    </span>

</div>
<?php } ?>