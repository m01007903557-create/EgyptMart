<?php
include "common.php";

$bnsprof_id=substr($_GET['id'],4);
$sql="select * from business_profile,user where bnsprof_uid=usr_id and md5(bnsprof_id)='".$bnsprof_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$sql_own="select * from user,business_profile where usr_id='".$_SESSION['uid_indm']."' and bnsprof_uid=usr_id limit 1";
$res_own=mysqli_query($con, $sql_own);
$row_own=mysqli_fetch_object($res_own);

?>
<link type="text/css" rel="stylesheet" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/css/main-v2.css">        
		<link href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/css/dir-style-8.css" type="text/css" rel="stylesheet">
		<link href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/css/overlay-v2.css" type="text/css" rel="stylesheet">
		<link href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/css/bl_form_temp5.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="js/jquery-1.11.1.min.js"></script>

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
function showTempAttachments(usr)
{
	$.post("ajax-file/showTempAttachments.php", {usr:usr}, function(data){	$("#file_list").html(data);	});
}
function delAttachment(id,usr)
{
	$.post("ajax-file/delTempAttachments.php", {id:id}, function(data){	showTempAttachments(usr);	});
}
function sendEnquiry()
{
	//alert('hello'); return false;
	var msg_from=document.getElementById('msg_from');
	var msg_to=document.getElementById('msg_to');
	var msg_subject=document.getElementById('msg_subject');
	var msg_message=document.getElementById('msg_message');
	var msg="";
	var valid=true;
	
	if(msg_message.value == '' || msg_message.value == null)
	{
		msg="Kindly describe your requirement.";

		valid=false;
	}
	else if(msg_message.value.length < 50)
	{
		msg="Description of requirement must be atleast 50 characters length.";
		msg_message.focus();
		valid=false;
	}
	
	if(valid==false)
	{
//		$("#errmsg").html(msg);
//		$("#errmsg").css("display","block");
		//alert(msg);
		msg_message.focus();
	}
	else
	{
		$("#msg_message").attr('readonly','readonly');
		$("#b_sub").css("display","none");
		$("#loading").css("display","block");
		
		$.post("ajax-file/sendMessage.php", {msg_from:msg_from.value,msg_to:msg_to.value,msg_subject:msg_subject.value,msg_message:msg_message.value}, function(data){
			if(data==1)
			{
				setTimeout(function () {
					$("#loading").css("display","none");
					$("#succ_result").css("display","block");
				}, 500);
				showTempAttachments(msg_from.value);
				document.getElementById('msg_message').value='';
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
    	<p style="width: 635px;">Send Enquiry:<span class="co-name"><?php echo $row->bnsprof_compname; ?></span> </p>
    </div>
    <div class="bo k9 err-msg" id="errmsg" style="display: none;"></div>
    <form name="dataform" class="mp0-nw" method="post">
    
    <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $_SESSION['uid_indm']; ?>" />
    <input type="hidden" id="msg_to" name="msg_to" value="<?php echo $row->usr_id; ?>" />
    <input type="hidden" id="msg_subject" name="msg_subject" value="<?php echo "Enquiry from ".$row_usr->name_prefix." ".$row_usr->fname." ".$row_usr->lname; ?>" />
    
    <div class="enn1-nw nef4-nw">
    	<textarea id="msg_message" name="msg_message" style="resize: none;" class="nef10-nw" tabindex="1"></textarea>
        <div class="nef9-nw nef12-nw">
        	<!-- Send me a copy of this Enquiry-->
            </div>
            <div class="nef9-nw nef12-nw" style="text-align:right;width:99%;" id="Description-status">Remaining Characters:&nbsp;<b><strong id="charCount">2000</strong></b><div class="m2"></div></div></div><fieldset style="height: 108px; border: 1px solid rgb(134, 182, 217); margin-top: 2px; width:178px; *width=190px"><legend style="font-size: 13px;color:#017BBC; text-align: center;"><strong>Describe your requirement</strong> </legend>    <div class="f1-nw" style=" color:#055985;"><ul>
            <li class=" li-1"> Products/ Service details</li>
            <li class="li-1"> Required Specifications</li>
            <li class="li-1"> Minimum Order</li>
            <li class="li-1"> Delivery Time or Term</li>
            </ul></div>
            </fieldset>
            <div style="padding:5px;">
        <script src="company/uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
			<link rel="stylesheet" type="text/css" href="company/uploadifive/uploadifive.css">
			<script type="text/javascript">
				jQuery(function(){
					jQuery('#file_upload').uploadifive({
						'auto'     : true,
						'formData' : {'id' : '<?php echo $_SESSION['uid_indm']; ?>'},
						'queueID'  : 'queue',
						'debug'    : true,
						'method'   : 'post',
						'uploadScript' : 'ajax-file/addTempEnquiryFile.php',
						'onAddQueueItem' : function(file) {
							//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
							//$("#drop").html('<img src="images/loading.gif" alt="Uploading...." />');
						},
						'onUploadComplete' : function(file,data) {
							showTempAttachments('<?php echo $_SESSION['uid_indm']; ?>');
						}
					});
				});
			</script>
            <div id="drop" style="padding-left:10px;">
            <input type="file" id="file_upload" name="file_upload"/>
            </div>
            <div id="queue"></div>
            <div id="file_list" style="margin:2px;"></div>
        </div>
            <div style="padding:5px;float:left"><input type="checkbox" id="" name=""/>&nbsp;If this supplier doesn't contact me on message center in 24 hours, I want ARABYOS to recommend me more selected suppliers.</div>
            <div class="clr-nw" style="margin-bottom:2px"></div>
            
            <div>
            	<div class="w12" style="font-size:14px;padding: 5px; border-bottom: 1px solid rgb(134, 182, 217); margin: 5px; width:658px; color: rgb(15, 84, 135); background-color: rgb(241, 241, 241);float:left;font-weight:700" align="LEFT"><b>Your contact information:</b></div><div class="text" style="padding-top: 5px; border: 4px double rgb(134, 182, 217); padding-bottom: 10px; padding-left: 10px; background-color: rgb(241, 241, 241);" align="LEFT">  <div style="clear:both"></div>
		<div id="yourcontactinfo">
        	<div class="text" style="padding-top:5px;" align="LEFT"><?php echo $row_own->name_prefix; ?> <?php echo $row_own->fname; ?> <?php echo $row_own->lname; ?>
            <br>
            <?php echo $row_own->bnsprof_compname; ?>
            <br>
            <?php if($row_own->bnsprof_address1!=''){ ?>
            <?php echo $row_own->bnsprof_address1; ?>
            <br>
            <?php } ?>
            <?php if($row_own->bnsprof_address2!=''){ ?>
            <?php echo $row_own->bnsprof_address2; ?>
            <br>
            <?php } ?> 
            <?php if($row_own->bnsprof_city!='0' && $row_own->bnsprof_city!=''){ echo get_city_name($row_own->bnsprof_city).", "; } ?>
            <?php if($row_own->bnsprof_state!='0' && $row_own->bnsprof_state!=''){ echo get_state_name($row_own->bnsprof_state).", "; } ?>
            <?php if($row_own->country!='0' && $row_own->country!=''){ echo get_country_name($row_own->country); } ?>
            <br>Email: <?php echo $row_own->email; ?>
            <?php if($row_own->mobile1!='' && $row_own->mobile1!='0'){	?><br>
            Mobile / Cell Phone: +(<?php echo $row_own->country_ph_code; ?>)-<?php echo $row_own->mobile1; ?><?php } ?></div>
            </div>
		</div>
            <div style="font-size: 12px; margin-left: 0px; padding: 0px 0pt 10px 15px;" align="LEFT"><br> </div></div><div class="clr-nw"></div><div id="nu_frm"><div class="nef4-nw" align="center">
            <div style="display: block;" id="b_sub">
            
            <input name="submit_member" id="button" value="Send Enquiry" class="snd-enq" style="box-shadow: 0pt 1px 5px rgb(170, 170, 170); font-family: Arial,Helvetica,sans-serif; font-size: 16px; font-weight: bold; text-align: center; color: rgb(255, 255, 255); border: 1px solid rgb(24, 143, 205);border-radius:6px; _border-radius: 0px; padding:5px 20px; cursor:pointer;" type="button" onclick="sendEnquiry();"/>
            
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