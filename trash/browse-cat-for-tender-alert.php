<?php
include "common.php";
?>
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script src="js/jquery.colorbox.js"></script>
<script type="text/javascript">
function addAlertCategory()
{
	$.post("ajax-file/addTenderAlertCat.php",{},    function(data){	window.location.reload();   });
}
function delAlertCat(id)
{
	if(confirm("Are you sure to delete this Category?")){
		$.post("ajax-file/delTenderAlertCat.php",{id:id},    function(data){	window.location.reload();   });
	}
}
</script>
<script type="text/javascript">
function searchcat()
{
	$("#sc").removeClass("tabclose").addClass("tabopen");
	$("#bc").removeClass("tabopen").addClass("tabclose");

	$("#browse_cat").css("display","none");
	$("#search_cat").css("display","block");
}
function beowswcat()
{
	$("#bc").removeClass("tabclose").addClass("tabopen");
	$("#sc").removeClass("tabopen").addClass("tabclose");

	$("#search_cat").css("display","none");
	$("#browse_cat").css("display","block");
}
function showCategory(id)
{
	$.post("ajax-file/showSubcategory.php",{id:id},	function(data){
		if(data!='')
		{
			$('#grp').html(data);
			$("#cat_select_area").show();
		}
	});
}
function catajaxFunction(id)
{
	$("#display_mcat").css("display","block");

	$("#scat").css("display","none");
	$("#loading_scat").css("display","block");
	/*setTimeout(function () {*/
		type="tender";
		$.post("ajax-file/subcategoryCheckBox.php",{id:id,type:type},    function(data){
			$("#scat").html(data);
			$("#loading_scat").css("display","none");
			$("#scat").css("display","block");
		});
	/*}, 500);*/
}
function scatAddDel(id)
{
	if($('#scat_'+id).attr('checked')) {
		$.post("ajax-file/addTempTenderAlertCat.php",{id:id},    function(data){	showList()	});
	} else {
		$.post("ajax-file/delTempTenderAlertCat.php",{id:id},    function(data){	showList()	});
	}
}

function addalertlead(){
    $.post("ajax-file/addTenderAlertCat.php",{},    function(data){	location='';	});
}
function showList()
{
	$.post("ajax-file/showTempTenderAlertCat.php",{},    function(data){	$("#div1").html(data);	});
}
function remove(id)
{
	$.post("ajax-file/delTempTenderAlertCat.php",{id:id},    function(data){	showList()	});
}
</script>
<style>
#addLeadCatBtn{
	background-color: #B90000;
	background: -moz-linear-gradient(top,  #B90000 0%, #B90000 8%, #DF0000 54%, #DF0000 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#B90000), color-stop(8%,#B90000), color-stop(54%,#DF0000), color-stop(100%,#DF0000));
	background: -webkit-linear-gradient(top,  #B90000 0%,#B90000 8%,#710000 54%,#B90000 100%);
	background: -o-linear-gradient(top,  #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
	background: -ms-linear-gradient(top,  #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
	background: linear-gradient(to bottom,  #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#B90000', endColorstr='#DF0000',GradientType=0 );
	box-shadow: 0pt 1px 5px #AAA;
font-family: Arial,Helvetica,sans-serif;
font-size: 16px;
font-weight: bold;
text-align: center;
color: #FFF;
border: 1px solid #C10000;
border-radius: 6px;
padding: 5px 20px;
cursor: pointer;
}
</style>
<div class="bg_border_new" style="height:675px" id="dvh1">
<div style="background-color:#FFFFFF; height:670px" id="dvh2">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" >
    	<tbody>
        	<tr>
            <td bgcolor="#FAF4FF"><div class="myta">Manage Your Tender Preference</div></td>
            </tr>
		</tbody>
	</table>
    <img src="images/zero.gif" height="10" width="1"><br> <div style="height: 450px;">
<form style="margin:0px;" name="test" action="/cgi/eto-alert-subs-new.mp" onsubmit="return false"><div>
 <img src="images/zero.gif" height="14" width="1"><br>

 <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"><tbody>
 <tr><td valign="TOP" width="19"><img src="images/zero.gif" height="6" width="1"><br><img src="images/11.gif" height="15" width="19"></td><td><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody>
 <tr>
 <td class="tabclose" onclick="searchcat()" id="sc" width="152">Search Categories</td>
 <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
 <td class="tabopen" onclick="beowswcat()" id="bc" width="155">Browse Categories</td>
 <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
 </tr>
 </tbody></table></td></tr></tbody></table>

 <div id="browse_cat" style="display: block;">
 <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%" >
 <tbody><tr> <td width="19"><img src="images/zero.gif" height="1" width="19"></td> <td bgcolor="#FAF4FF"> <div class="border_bottom" align="left"><img src="images/zero.gif" height="10" width="1"><br>
 <table align="CENTER" border="0" cellpadding="0" cellspacing="0" style="width:500px;">
 <tbody><tr>
 <td style="font-family:arial; font-size:12px; padding-left:3px;" width="100%">
 <span id="grp1">
 <?php
	if(get_page_settings('25')=='manual')
	{
		$sql_order=" order by pc_order,pc_name";
	}
	else
	{
		$sql_order=" order by pc_name";
	}

$sql_cat="select pc_id,pc_name from product_category_arabyos where pc_parent_id = '0' and pc_status = '1' ".$sql_order;
$res_cat=mysql_query($sql_cat);
 ?>
 <select size="10" style="font-size:13px; font-family:arial; height:180px;  width:100%;" name="mcat" id="mcat" onchange="showCategory(this.value)">
 <?php while($row_cat=mysql_fetch_object($res_cat)){ ?>
 <option value="<?php echo $row_cat->pc_id; ?>"><?php echo $row_cat->pc_name; ?></option>
 <?php	}	?>
 </select>
 </span>
 </td>
 </tr>
 <tr id="cat_select_area" style="display:none;">
 <td style="font-family:arial; font-size:12px; padding-left:3px;" width="100%">
 <span id="grp1">
<select size="10" style="font-size:13px; font-family:arial; height:180px;  width:100%;" name="grp" id="grp" onclick="catajaxFunction(this.value);" >
</select>
 </span>
 </td>
 </tr>
  <tr> <td width="100%"><img src="images/zero.gif" height="1" width="5"></td> </tr>
  <tr> <td style="font-family:arial; font-size:12px; padding-left:3px;background:none;" width="100%"> <br>
  <div style="height:170px">
  <div class="displayon" id="display_mcat" style="display:none;">
  <div style="background-color:#ffffff; overflow: auto; height: 170px; padding-left: 1px; font-size: 13px; text-align:left">
  <div id="loading_scat" align="center" style="padding-top:70px;"><img src="images/indicator.gif" /></div>

  <div style="border: 1px solid rgb(51, 102, 153); background-color:#ffffff; overflow: auto; height: 165px; padding-left:1px; font-size: 13px; display:none" id="scat">


</div>

</div></div></div>	</td>  </tr> </tbody>
</table>
<img src="images/zero.gif" height="8" width="1"><br> </div> </td></tr></tbody></table></div>



 <script>

function produtcustomcategory()
{
   keywordsFilter = $("#txt_cat_mcat").val();
   if(keywordsFilter==""){
     alert("Please select any product first");
     return false;
   }
   $.post("ajax-file/produtcustomcategory.php",{'keywordsFilter':keywordsFilter,'type':'addTempTenderAlertCat'},function(data){ $('#div1').append(data);})
}


</script>
 <div id="search_cat" style="display: none; text-align: left;">
 <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
 <tbody>
 <tr> <td valign="TOP"><img src="images/zero.gif" height="1" width="19"></td>
 <td bgcolor="#F8FCFF" valign="TOP" width="100%"> <div class="border_bottom"><img src="images/zero.gif" height="5" width="1"><br>
 <div class="blwnew" style="padding-top:0px; margin-top:0px;">
 <b style="font-size:13px;"><font color="#E95801">Enter product keywords to find a category</font></b></div>
  <table border="0" cellpadding="0" cellspacing="0" width="525"> <tbody>
  <tr> <td>
  <input role="textbox" class="txt ui-placeholder-input ui-autocomplete-input" name="txt_cat_mcat" id="txt_cat_mcat" type="text" maxlength="60" size="33" ></td>
   <td style="cursor:pointer;"><input name="button5" value="Add Category" onclick="return produtcustomcategory();" type="button"></td> <td valign="BOTTOM"> <div class="blw1">For example: "arm chair" or "furniture"</div> <img src="images/zero.gif" height="1" width="240"></td> </tr></tbody> </table> <img src="images/zero.gif" height="5" width="1"><br> <div style="height:326px;"> <div id="s_result" style="display:none;"> <div class="s_text"> <div style="height:298px; overflow:auto;"> <span id="head"></span><span id="ajax"></span></div> <img src="images/zero.gif" height="8" width="1">
 </div> </div> </div> </div>
  </td>
  </tr>
   </tbody>
   </table>
 </div>

    <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"> <tbody><tr> <td valign="TOP" width="19"><img src="images/22.gif" height="15" vspace="3" width="19"></td> <td>
 <table bgcolor="#FAF4FF" border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr> <td> <div style="margin-left:10px; padding-top:4px; padding-bottom:4px;text-align:left"> <div class="setcatnew"><b class="kk">Selected Categories</b></div>
 <div style="height:100px; overflow:auto;">
 <span id="div1">



 </span>
 </div>
 </div></td> </tr> </tbody></table>
 <div align="CENTER"><img src="images/zero.gif" height="10" width="1"><br>
 <input name="confirm1" id="addSOCatBtn" value="Submit Categories" onclick="addalertlead();" type="button">
 <br><img src="images/zero.gif" height="10" width="1"><br> </div></td> </tr> </tbody></table>
  </div></form></div> </div></div>