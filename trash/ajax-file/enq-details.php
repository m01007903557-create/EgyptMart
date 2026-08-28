<?php
ob_start();
session_start();
include "../common.php";

$msg_id=$_POST['id'];
$type=$_POST['type'];

$sql="select * from message where msg_id='".$msg_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$sql_comp="select * from business_profile where bnsprof_uid='".$row->msg_from."'";
$res_comp=mysqli_query($con, $sql_comp);
$row_comp=mysqli_fetch_object($res_comp);

$sql_upd="update message set msg_read='1' where msg_id='".$msg_id."'";
mysqli_query($con, $sql_upd);
?>
<!-- webcast -->
<style type="text/css">
	#wbr>div>div:last-child>div>div:last-child {
	    position: relative;
	    left: 10px;
	    background: transparent;
	}
	#wbr>div>div:last-child>div>div:last-child>div {
	    background: transparent;
	}
</style>
			<div class="fl_m2 my_m2">
				<div class="fl_m2">
					<!--div class="bc f11">Enquiries &#187;</div-->
				</div>
			</div>
			<span id="fol_m2"><h1><?php if($type=='inbox'){ ?>Inbox<?php }else if($type=='sent'){?>Sent Box<?php } ?></h1></span><!-- My Mailbox start -->
            
<div class="fl_m2 my_m2" id="det" align="left">
            <br>
            <div id="yeartabs"><div style="height:28px;border-bottom:1px solid #FFD9D9;"></div></div><div class="b11_m2 tmsg_m2" id="dymesg" align="center">
				<div id="loading_det" class="c2_m2 bo_m2 lh_m2" style="width: 15%; display: none;">
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
			</div><div id="pnavsec"><div class="b11_m2 b7_m2">
            
            <span class="pagenavigation">
            
         
            
            </span>
            
            <div id="new_m2" class="fl_m2 p9_m2" style="padding-left:40px"></div><div style="clear: both;"></div></div></div><div id="mhmdd"><div class="f4_m2 b5_m2 b11_m2 b10_m2 b7_m2 bg2_m2 ac_m2 p6_m2 hh_m2 b18_m2" id="mainheader"><div id="mailboxheader"><div id="selectfolder" class="box_m2">
<div id="mailboxoptions" class="fl_m2 lh5_m2">
<span id="backdrop">
<div class="fl_m2 p_m2" id="backto" style="width:250px">&nbsp;<span style="cursor:pointer;" title="Back" class="sd_m2" onclick="closeDetail();">« Back</span>&nbsp;&nbsp;</div></span></div><div class="fl_m2 lh5_m2" id="mailoption"><span id="reply_m2"></span><div class="fl_m2 hh_m2 b17_m2" style="" id="muldiv"></div>

		<div id="delete" class="horizontalcssmenu_delete_m2 horizontalcssmenu_m2 fl_m2 lh4_m2">
			<ul id="delete_m2" style="margin-top: 0px; padding-top: 0pt;">
				<li style="z-index: 0; margin: 0px; padding: 0pt;"> 
					<span id="deleteall_m2"><a title="Delete" iscontextmenu="true" onclick="delMessage(<?php echo $row->msg_id; ?>);"></a></span>
				</li>
			</ul>
		</div>
		<div class="fl_m2" id="derdiv" style="border-right:1px solid #85B3D5;height:24px">&nbsp;&nbsp;</div>
        <div id="showmandiv" style="height: 22px; padding-top: 2px; display: none;" class="fl_m2 b17_m2" align="center">&nbsp;&nbsp;<span id="showmanage"></span>&nbsp;&nbsp;</div>
        
        </div></div></div>
        
        <?php if($type=='inbox'){ ?>
		<div id="movetocombox"><div id="movetoptions" title="Move To">
			      <div id="movedropdown" class="horizontalcssmenu_move_m2 horizontalcssmenu_m2 fl_m2">
			      <ul id="my2cssmenu" style="margin-top:0px; padding-top:0;">
			      <li style="z-index:0; margin:0px; padding:0;" align="center"> 
			      <script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
                  
                 <script>
	$(document).ready(function(){
		$('body').on('click', '.ajax', function(event) {
			
			$.colorbox(
			{
				href:$(this).attr('href'),
				open:true, 
				width: '750px'
			}
			);
		  return false;
		});
		
		/*$('.ajax').live('click', function() {
		  $.colorbox({href:$(this).attr('href'), open:true, width: '750px'});
		  return false;
		});*/
		//Examples of how to assign the ColorBox event to elements
		
		//$(".inline").colorbox({inline:true, width:"50%"});
		//Example of preserving a JavaScript event for inline calls.
		$("#click").click(function(){ 
		$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
			return false;
		});
	});
</script>
 
			<span id="moveto_m2">
			<?php //echo $row->msg_entity; 
			if($row->msg_entity == 'membership_plan' || $row->msg_entity == 'membership_requirement'){ ?>
			
			<a href="membership_plans.php" style="text-decoration:none;color:#666">
				  <span class=""><div title="WEB" class="td_m2 m2_sym m2_sym3">Reply</div></span>
				  </a>
			<?php } 
			else if($row->msg_entity == 'advertisement_requirement'){ ?>
			<a href="advertise-with-us.php" style="text-decoration:none;color:#666">
				  <span class=""><div title="WEB" class="td_m2 m2_sym m2_sym3">Reply</div></span>
				  </a>
			<?php }
			else { ?>
			      <a href="sendmessage-form.php?id=<?php echo $row->msg_from; ?>" style="text-decoration:none;color:#666" iscontextmenu="true" class="ajax" rel="nofollow">
				  <span class=""><div title="WEB" class="td_m2 m2_sym m2_sym3">Reply</div></span>
				  </a>
			<?php } ?>
			      </span>	
			      </li></ul></div></div><div class="fl_m2" style="border-right:1px solid #85B3D5;height:24px"></div></div>

<?php	}	?>
                  
                  

</div><div class="b11_m2" id="ci_m2"><br></div></div>
<div class="" id="repseq"></div><span class="mailbox"><div id="qidtype"></div>
<!-- My Showmessage start -->
<div class="b9_m2 b10_m2" id="reptable" style="display:none"></div>

<div class="b9_m2 b10_m2" id="detable">
<table class="lh2_m2" border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
		<tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2"><td class="p_m2"><b>Enquiry Information</b></td></tr>
						<tr>
						<td class="sh_m2">
						<table cellpadding="0" cellspacing="0">
						<tbody>
                        <?php
							$sql_to="select * from user where usr_id='".$row->msg_to."'";
							$res_to=mysqli_query($con, $sql_to);
							$row_to=mysqli_fetch_object($res_to);
						?>
                        <tr>
						<td width="150"><b>&nbsp;To:</b></td><td><?php echo $row_to->fname." ".$row_to->lname; ?>&nbsp;&lt;<?php echo $row_to->email; ?>&gt;</td>
						</tr>
						<tr>
                        <?php
						$from_admin = 0;
							$sql_from="select * from user where usr_id='".$row->msg_from."'";
							$res_from=mysqli_query($con, $sql_from);
							$row_from=mysqli_fetch_object($res_from);
if( $row_from->fname != '') {
						?>
						<td><b>&nbsp;From:</b></td> <td><?php echo $row_from->fname." ".$row_from->lname; ?>&nbsp;&lt;<?php echo $row_from->email; ?>&gt;</td>
<?php }
else {
	$from_admin = 1;
$sql_from="select * from admin_user where id='".$row->msg_from."'";
$res_from=mysqli_query($con, $sql_from);
$row_from=mysqli_fetch_object($res_from);
?>
<td><b>&nbsp;From:</b></td> <td><?php echo $row_from->username; ?>&nbsp;&lt;<?php echo $row_from->email; ?>&gt;</td>

<?php } ?>
						</tr>
                        
						<tr><td><b>&nbsp;Date:</b></td><td><?php echo date("d-M-Y H:i:s A",strtotime($row->msg_date)); ?>&nbsp;<?php echo date('T'); ?></td>
                        </tr>
                        <tr>
                        <td><b>&nbsp;Subject:</b></td>
                        <td><?php if($row->msg_subject!=''){ echo $row->msg_subject; }else{ ?>No Subject<?php } ?></td>
                        </tr>
                        </tbody></table></td></tr><tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2"><td class="p_m2"><b>Message</b></td></tr>
					<tr class="f5_m2"><td class="sh_m2"><span style="width:750px;word-wrap:break-word;" id="wbr"><?php
					if($from_admin == 1) {
						echo stripslashes(html_entity_decode($row->msg_message));
					}
					else {
					echo stripslashes($row->msg_message);
					}					?></span></td></tr>
                    <tr class="f5_m2"><td class="sh_m2">

                    <?php
					$sql_ma="select * from message_attachment where ma_msg_id='".$row->msg_id."'";
					$res_ma=mysqli_query($con, $sql_ma);
					if(mysqli_num_rows($res_ma)>0)
					{
					?>
                    <span style="width:750px;word-wrap:break-word;" id="wbr"><b>Attachments:</b></span>
                    <?php
                    	while($row_ma=mysqli_fetch_object($res_ma))
						{
							$path="upload/message_attachment/".$row_ma->ma_file;
							?>
						<div><a href="<?php echo $path; ?>" target="_blank"><?php echo $row_ma->ma_file; ?></a></div>
						<?php
						}
                    
                    } ?>
                    </td></tr>
                    </tbody></table></div><!-- My Showmessage end --></span></div>
