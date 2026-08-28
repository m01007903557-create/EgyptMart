<?php
ob_start();
session_start(); 
$uid=$_SESSION['uid_indm'];
include "common.php";	
?>
<style>
body{
background:silver;
}
</style>
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script>
function changeuemail()
{
	var emailad=$('input#emailad').val();
	var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	
	if(emailad=="" || emailad==null)
	{
		alert("Please enter email");
	}
	else if (!emailad.match(is_email))
    {
		alert("Please enter valid email");	
    }
	else
	{
   $.post("ajax-file/changeuemail.php", {emailad:emailad},
	 function(data){ 
	 //alert(data);
	 location.reload();
	 });	
	}
}
</script>
<div style="height: 100%; width: 100%; left: 0px; top: 33px; text-align:center;" class="simplemodal-container" id="simplemodal-container"><a class="modalCloseImg simplemodal-close" title="Close"></a><div style="height: 100%; outline: 0px none; width: 100%; overflow: auto;" class="simplemodal-wrap" tabindex="-1"><div id="Change_Email" class="overlay simplemodal-data" style=""><div style="left:481px; top:793px; display:none;" id="qtip2"></div>
        
        <div><h2>Change Your Login Email ID</h2></div><div>&nbsp;</div>	
        <div class="ma sbox c bnr fw lh" id="div_succ1" style="display:none;" align="left">Your new primary Email ID is already used by another User as alternative Email. You can use this as your primary Email ID</div>
        <div class="ma ibox c bnr fw lh" id="div_caut1" style="background-position: 4px -457px;display:none;" align="left"></div>
        <div class="ma ebox c bnr fw lh" id="div_error1" style="background-position:-581px -422px;display:none;" align="left">Your new primary Email ID is already used by another User as alternative Email. You can use this as your primary Email ID</div>
	<div align="center">
        <div id="fbloading" style="background-color:#fff1a8;font-weight:bold;line-height:23px;display:none;width:28%"><img src="http://my.imimg.com/gifs/my2-loading.gif" class="loading_m2">&nbsp;Loading...&nbsp;</div></div>
        
        <div class="c3">&nbsp;</div>
       
            <div class="c3">            					
            <table border="0" cellpadding="4" cellspacing="0" width="98%">
            <tbody>
            <tr>
            <td width="25%">&nbsp;</td>
            <td width="75%"><div id="messages"><?php echo $_SESSION['msg']; ?></div></td>
            </tr>
            
            <tr>
            <td width="50%" style="text-align:right;"><span class="r">*</span>&nbsp;New Email Id :</td>
            <td width="50%" style="text-align:left;"><input name="emailad" id="emailad" value="<?php echo user_info($uid,'email');?>" class="mu11" style="padding:7px;" maxlength="100" type="text"></td>
            </tr>
	    <tr><td colspan="2" nowrap=""><div id="saalt" name="saalt" style="display:none"><input name="saveasalt" id="saveasalt" checked="" type="checkbox">Save my current Email ID as my alternate Email ID.<br></div></td></tr>
            <tr>
            <td>&nbsp;</td>
            <td><input id="changeButton" name="changeButton" value="Change My Login Email" class="cmp" type="button" onClick="changeuemail();"></td>
            </tr>
            </tbody>
            </table>		
           
            </div>
          
        </div></div></div>