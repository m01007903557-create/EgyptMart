<?php
include "../common.php";
$hid=$_GET[hid];
if(($hid!="")&&($hid!="undefined")){
$recObjsql="select * from custom_faq_arabyos where cf_fc_id=".$hid;
$recObj=mysqli_query($con, $recObjsql);
?>

<div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
    <th><strong>Heading</strong></th>
    <th><strong>Content</strong></th>
    <th><strong>Action</strong></th>
</thead>
<tbody>
<?php 
   $j=1;
   $count=mysqli_num_rows($recObj);
   if($count >0)
   {		  
	   while($row=mysqli_fetch_array( $recObj)){
	   $content=substr(stripslashes($row['cf_content']),0,150);
	   ?>
        <tr>
        <td style="text-align:center;"><?php  echo ucwords($row['cf_heading']);?></td>
        <td style="text-align:center;"><?php  echo $content ;?>&nbsp;... <a id="id-btn-job<?php echo $row['cf_id']; ?>" style="cursor:pointer;">more..</a></td> 
        <td style="text-align:center">
            <a href="support_change.php?pid=<?php echo $row['cf_id']; ?>&mode=edit" title="Edit" ><img alt="edit" src="images/edit.jpg"></a>
            <a onClick="DelPageList(<?php echo $row['cf_id']; ?>)"><img alt="delete" src="images/delete.jpg" border="0" style="cursor:pointer;"></a>
        </td>
        </tr>
        <!---->
        <div  id="job_form<?php echo $row['cf_id']; ?>" class="backLayer" style="left: 15%; top: 5%;  display: none;">
	<h3>Content</h3>
   
			<div class="">
				<?php echo $row['cf_content']; ?>
			</div>
  
			<div class="space-6"></div>
			
	
	<div style="float: right; padding-bottom:10px"><button class="btn btn-xs" type="button" id="clse_job<?php echo $row['cf_id']; ?>">Close</button></div>
</div>
<!--########################******************   POPUP   *****************########################-->

<div class="background_overlay" style="display: none;"></div>
<style>
.backLayer{position: fixed;border: 5px solid lightblue;padding:0px 20px;background: white;width: 70%;height:auto;z-index: 9999999999999; overflow:scroll; overflow-x:hidden;}

.background_overlay { position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 99999999; background:black; opacity: 0.4;}
</style>

<script type="text/javascript">
$(document).ready(function(){
//open popup

	$("#id-btn-job<?php echo $row['cf_id']; ?>").click(function(){
		$("#job_form<?php echo $row['cf_id']; ?>").fadeIn(1000);
		$(".background_overlay").fadeIn(500);
		positionCookiePopup();	
	});
	

//close popup
	
$("#clse_job<?php echo $row['cf_id']; ?>, .background_overlay").click(function(){
		$("#job_form<?php echo $row['cf_id']; ?>").fadeOut(500);
		$(".background_overlay").fadeOut(500);
	});

});

function positionCookiePopup(){
  if(!$("#job_form<?php echo $row['cf_id']; ?>").is(':visible')){
    return;
  } 
  $("#job_form<?php echo $row['cf_id']; ?>").css({
      left: ($(window).width() - $('#job_form<?php echo $row['cf_id']; ?>').width()) / 2,
      top: ($(window).height() - $('#job_form<?php echo $row['cf_id']; ?>').height()) / 5,
      position:'fixed'
  });
}


$(window).bind('resize',positionCookiePopup);
</script>

<!--########################******************   POPUP   *****************########################-->
        <!---->
        <?php $j++; 
		} }?>
</tbody>
</table>
</div>
<?php } ?>