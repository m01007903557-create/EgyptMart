<?php
include "common.php";

$usr_id=substr($_GET['id'],4);
$lead_headline = $_GET['headline'];
$sql="select * from user where md5(usr_id)='".$usr_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$sql_own="select * from user,business_profile where usr_id='".$_SESSION['uid_indm']."' and bnsprof_uid=usr_id limit 1";
$res_own=mysqli_query($con, $sql_own);
$row_own=mysqli_fetch_object($res_own);

?>

<link href="css/overlay-v2.css" type="text/css" rel="stylesheet">

<script type="text/javascript">
$(document).ready(function() {
	$(document).on('keyup', '#msg_message', function(e){
//		var msgSpan = $(this).parents('li').find('#Charcount');
//		var ml     = parseInt( $(this).attr('maxlength') );
		var maxLength = 2000;
		var text = $(this).val();
		var length = $(this).val().length;
		if(length>maxLength)
		{
			$(this).val(text.substring(0, (maxLength)));
		}
		var msg =maxLength - $(this).val().length;
//		msgSpan.empty().html(msg);
		$("#charCount").empty().html(msg);
    });					   
});
function sendEnquiry()
{
	var msg_from=document.getElementById('msg_from');
	var msg_to=document.getElementById('msg_to');
	var msg_subject=document.getElementById('msg_subject');
	var msg_message=document.getElementById('msg_message');
	var lead_headline = document.getElementById('lead_headline').innerHTML;
	var msg="";
	var valid=true;
	
	if(msg_message.value == '' || msg_message.value == null)
	{
		msg="Kindly describe your enquiry.";
		msg_message.focus();
		valid=false;
	}
	else if(msg_message.value.length < 50)
	{
		msg="Enquiry must be atleast 50 characters length.";
		msg_message.focus();
		valid=false;
	}
	
	if(valid==false)
	{
//		$("#errmsg").html(msg);
//		$("#errmsg").css("display","block");
		alert(msg);
		msg_message.focus();
	}
	else
	{
		$("#msg_message").attr('readonly','readonly');
		$("#b_sub").css("display","none");
		$("#loading").css("display","block");
		
		$.post("ajax-file/sendMessage.php", {lead_headline:lead_headline,msg_from:msg_from.value,msg_to:msg_to.value,msg_subject:msg_subject.value,msg_message:msg_message.value}, function(data){
			if(data==1)
			{
				setTimeout(function () {
					$("#loading").css("display","none");
					$("#succ_result").css("display","block");
				}, 500);
			}
			else
			{
				setTimeout(function () {
					$("#loading").css("display","none");
					$("#err_result").css("display","block");
				}, 500);
			}
		});	
	}
}
</script>
<div class="ov-base">
	<div class="neff2-nw">
    	<p style="width: 635px;">Send Enquiry:<span class="co-name"><?php /*echo $row->bnsprof_compname;*/ ?></span> </p>
    </div>
    <div class="bo k9 err-msg" id="errmsg" style="display: none;"></div>
    <form name="dataform" style="margin: 0px;padding: 0px;" method="post">
    <div id="lead_headline" style="display:none;"><?php print $lead_headline; ?></div>
    <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $_SESSION['uid_indm']; ?>" />
    <input type="hidden" id="msg_to" name="msg_to" value="<?php echo $row->usr_id; ?>" />
    <input type="hidden" id="msg_subject" name="msg_subject" value="<?php echo "Enquiry from ".$row_own->name_prefix." ".$row_own->fname." ".$row_own->lname; ?>" />
    
    <div class="enn1-nw nef4-nw">
    	<textarea id="msg_message" name="msg_message" style="resize: none;" class="nef10-nw" tabindex="1"></textarea>        
            <div class="nef9-nw nef12-nw" style="text-align:right;width:99%;" id="Description-status">Remaining Characters:&nbsp;<b><strong id="charCount">2000</strong></b><div class="m2"></div></div>
		</div><fieldset style="height: 108px; border: 1px solid #6500CA; margin-top: 2px; width:178px; *width=190px"><legend style="font-size: 13px;color:#017BBC; text-align: center;"><strong>Describe your requirement</strong> </legend>    <div class="f1-nw" style=" color:#055985;"><ul><li class=" li-1"> Product requirement</li><li class="li-1"> Specifications needed</li><li class="li-1"> Packaging &amp; delivery</li><li class="li-1"> Your company details etc.</li></ul></div>
            </fieldset>
            <div class="clr-nw" style="margin-bottom:2px"></div>
			<?php if($row_own->usr_mp_id > 4) {?>
            <div>
            	<div class="w12" style="font-size:14px;padding: 5px; border-bottom: 1px solid #6500CA; margin: 5px; width:658px; color: rgb(15, 84, 135); background-color: rgb(241, 241, 241);float:left;font-weight:700" align="LEFT"><b>Buyer information:</b></div>
                <div class="text" style="padding-top: 5px; border: 4px double #6500CA; padding-bottom: 10px; padding-left: 10px; background-color: rgb(241, 241, 241);" align="LEFT">  <div style="clear:both"></div>
		<div id="yourcontactinfo">
        	<div class="text" style="padding-top:5px;" align="LEFT"><?php echo $row->name_prefix; ?> <?php echo $row->fname; ?> <?php echo $row->lname; ?>
            <br>
            <?php /*if($row->bnsprof_city!='0' && $row->bnsprof_city!=''){ echo get_city_name($row->bnsprof_city).", "; }*/ ?>
            <?php /*if($row->bnsprof_state!='0' && $row->bnsprof_state!=''){ echo get_state_name($row->bnsprof_state).", "; }*/ ?>
            <?php if($row->country!='0' && $row->country!=''){ echo get_country_name($row->country); } ?>
            <br>Email: <?php echo $row->email; ?>
            <?php if($row->mobile1!='' && $row->mobile1!='0'){	?><br>
            Mobile / Cell Phone: +(<?php echo $row->country_ph_code; ?>)-<?php echo $row->mobile1; ?><?php } ?></div>
            </div>
		</div>
            <div style="font-size: 12px; margin-left: 0px; padding: 0px 0pt 10px 15px;" align="LEFT"><br> </div></div>
			<?php } else { ?>
			<div>
            	<div class="w12" style="font-size:14px;padding: 5px; border-bottom: 1px solid #6500CA; margin: 5px; width:658px; color: rgb(15, 84, 135); background-color: rgb(241, 241, 241);float:left;font-weight:700" align="LEFT"><b>Contact information:</b></div>
                <div class="text" style="padding-top: 5px; border: 4px double #6500CA; padding-bottom: 10px; padding-left: 10px; background-color: rgb(241, 241, 241);" align="LEFT">  <div style="clear:both"></div>
		<div id="yourcontactinfo">
        	<div class="text" style="padding-top:5px;" align="LEFT"><?php echo $row_own->name_prefix; ?> <?php echo $row_own->fname; ?> <?php echo $row_own->lname; ?>
            <br>
            <?php /*if($row->bnsprof_city!='0' && $row->bnsprof_city!=''){ echo get_city_name($row->bnsprof_city).", "; }*/ ?>
            <?php /*if($row->bnsprof_state!='0' && $row->bnsprof_state!=''){ echo get_state_name($row->bnsprof_state).", "; }*/ ?>
            <?php if($row_own->country!='0' && $row_own->country!=''){ echo get_country_name($row_own->country); } ?>
            <br>Email: <?php echo $row_own->email; ?>
            <?php if($row_own->mobile1!='' && $row_own->mobile1!='0'){	?><br>
            Mobile / Cell Phone: +(<?php echo $row_own->country_ph_code; ?>)-<?php echo $row_own->mobile1; ?><?php } ?></div>
            </div>
		</div>
            <div style="font-size: 12px; margin-left: 0px; padding: 0px 0pt 10px 15px;" align="LEFT"><br> </div></div>	
			<?php } ?>
			<div class="clr-nw"></div><div id="nu_frm"><div class="nef4-nw" align="center">
            <div style="display: block;" id="b_sub">
            
            <input name="submit_member" id="button" value="Send Enquiry" class="snd-enq" style="box-shadow: 0pt 1px 5px rgb(170, 170, 170); font-family: Arial,Helvetica,sans-serif; font-size: 16px; font-weight: bold; text-align: center; color: rgb(255, 255, 255); border: 1px solid #6500CA;border-radius:6px; _border-radius: 0px; padding:5px 20px; cursor:pointer;" type="button" onclick="sendEnquiry();"/>
            
            </div>
            <div id="loading" style="display:none;padding-left:5px;color:#1045B0;padding-top:16px;" class="g9 bo off">
            	<img class="loading" src="images/loading-small.gif" alt="loading" height="16" width="16"><b> Please Wait...</b>
			</div>
            <div id="succ_result" style="display:none;padding-left:5px;color:#009700;padding-top:16px;" class="g9 bo off">
            	Enquiry Sent Successfully
			</div>
			<div id="err_result" style="display:none;padding-left:5px;color:#F00;padding-top:16px;" class="g9 bo off">
            	Error on enquiry sent. Please try after sometime.
			</div>
		</div></div>
	</form>
    <div class="clr-nw"></div>
</div>