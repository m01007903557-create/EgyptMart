<?php include "includes/header.php";

$sql_bf="select * from business_profile where bnsprof_uid='".$row->usr_id."'";
$res_bf=mysql_query($sql_bf);
$row_bf=mysql_fetch_object($res_bf);

?>
<script>
$(document).ready(function(){
						   
	showVideoList(<?php echo $row_bf->bnsprof_id; ?>,1);
});
function openUploadArea()
{
	$("#upload_area").show();
	$("#add_new_video").hide();
}
function closeUploadArea()
{
	$("#upload_area").hide();
	$("#add_new_video").show();
}
function saveVideo()
{
	var cv_bnsprof_id=$("#cv_bnsprof_id").val();
	var vlink=$("#cv_video_link").val();
	
	if(vlink!='')
	{
		$.post("ajax-file/addCompanyVideo.php", {cv_bnsprof_id:cv_bnsprof_id,vlink:vlink},	function(data){
			$("#cv_video_link").val('');
			closeUploadArea();
			$("#video_disp").html('<img src="images/animated_loading.gif" />');
			alert('Video added successfully.');
			showVideoList(cv_bnsprof_id,1);
		});	
	
	}
	else
	{
	    alert('Please a valid embeded Youtube video Link.');
	}
}
function delVideo(id,cv_bnsprof_id)
{
	if(confirm("Are you sure to delete this Video?"))
	{
		$.post("ajax-file/delCompanyVideo.php", {cv_id:id},	function(data){	//alert(data);
			showVideoList(cv_bnsprof_id,1);
		});	
	}
}
function showVideoList(cv_bnsprof_id,page)
{
	$.post("../ajax-file/showCompanyVideoList.php", {cv_bnsprof_id:cv_bnsprof_id,page:page},	function(data){
		$("#video_disp").html(data);
	});	
}

</script>



<div id="body">
	<ul class="cb">
		<li id="wideColumn">
			<section class="box1">
				<div class="h2">
					<h2>Company Video</h2>
				</div>
				<nav class="comPro">
				    <div id="video_disp" style="text-align:center;padding-top:10px;"></div>
				</nav>
			</section>
		</li>
		<li id="thinColumn">
			<?php include "includes/right.php"; ?>
		</li>
	</ul>
</div>

		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>