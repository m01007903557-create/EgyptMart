<div class="f1 w61n tb lh ml br" id="lnav">
		<ul class="nln1" style="margin:0px; padding:0px">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#f50000; margin:0;padding: 18px 5px 18px 5px;background-color: ;">Company Profile</h3></li></ul><ul id="ulid" class="nln1" style="margin:0px; padding:0px">
        <?php if($file!="business-details"){ ?>
        <li><center><div><!--style="height:98px;margin-left:-5px"-->

	
<div class="file_button" style="margin:0px;height:110px;" id="addbut">
 <script type="text/javascript">

function list_photo()
{
	$.get("companylogo-list.php", {'uid' : <?php echo $uid; ?>}, 
	function(data){ 
	$('#list_photo').html(data); 
	});
}
function DelTempImage(imid)
{
   var cnf = confirm("Remove the Company LOGO?");
   if(cnf==true)
   {
	$.get("del_companylogo.php", {imid:imid},
 	function(data){
	list_photo();
 	});	 
   }
}	

</script>

<script type="text/javascript">list_photo()</script>

        <div id="queue">
        <div align="left" id="list_photo" class="line clearfix">
        </div>
        </div>
 <div class="upload_div">
    <img  style="float:left; margin-right:10px;" src="<?php echo BASE_URL ?>images/comp-logo-90.gif"/>
     <input id="file_upload" type="file" name="files" style="cursor:pointer;" />
	<span class="file_input">Add Image</span>
    </div>
</div>

        </div></center>
        </li>
        <?php } ?>
		<li class="np npnew"><a <?php if($file=="my-contactdetails"){ ?>class="leftindi txtcol"<?php } ?> href="my-contactdetails.php">&raquo;&nbsp;Contact Details</a></li>
		<li class="np npnew">
        <a <?php if($file=="business-details" || $file=="statutory-details" || $file=="myproduct-buy"){ ?>class="leftindi txtcol"<?php } ?> href="business-details.php">&raquo;&nbsp;Business Profile</a>
        </li>



        
		<li style="border-bottom:0052ce"><h3>Website Pages</h3></li>
		<li class="np npnew"><a <?php if($file=="my-homepage"){ ?>class="leftindi txtcol"<?php } ?> href="my-homepage.php">&raquo;&nbsp;My Home Page</a></li><!---tabs:start--->
		<li class="np npnew"><a <?php if($file=="myprofile"){ ?>class="leftindi txtcol"<?php } ?> href="myprofile.php">&raquo; Profile & News</a></li>

<li class="np npnew">
        <a <?php if($file=="company-video"){ ?>class="leftindi txtcol"<?php } ?> href="company-video.php">&raquo;&nbsp;Company Video</a>
        </li>
        <li class="np npnew">
        <a <?php if($file=="company-banner"){ ?>class="leftindi txtcol"<?php } ?> href="company-banner.php">&raquo;&nbsp;Company Images</a>
        </li>

        <li class="np npnew"><a href="product-list.php">&raquo;&nbsp;Products / Service Pages</a></li>
        <li style="border-bottom:none"><h3>Account Details</h3></li>
		<!--<li class="np npnew"><a <?php /*if($file=="my-settings"){*/ ?>class="leftindi txtcol"<?php /*}*/ ?> href="my-settings.php">&raquo;&nbsp;Privacy Settings</a></li>-->
        <?php if(user_info($uid,'usr_oauth_reg') == '0'){?>
        <li class="np npnew"><a <?php if($file=="change-password"){ ?>class="leftindi txtcol"<?php } ?> href="change-password.php">&raquo;&nbsp;Change Password</a></li>
        <?php }?>
        <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=''){	?>
        <li class="np npnew"><a href="logout.php">&raquo;&nbsp;Sign Out</a></li>
        <?php } ?>
		<li style="border-bottom:none; margin-top:40px"><h2>You may also like to</h2></li>
        <li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
        <li class="np npnew"><a href="product-add.php">Add Products</a></li>
		</ul>
</div>        