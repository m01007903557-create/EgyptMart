<?php
include "../common.php";

$sql_cn="select * from country where cn_status=1 order by cn_name";
$res_cn=mysqli_query($con, $sql_cn);
?>
		<link rel="stylesheet" href="assets/css/ace.min.css" />
<script type="text/javascript" src="../js/jquery-1.2.1.min"></script>
                        <script src="../uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../uploadifive/uploadifive.css">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<?php
$j=1;
while($rec_cn=mysqli_fetch_object($res_cn)) {  ?>
<?php if($j == 1){	?><tr><?php	} ?>
<td width="590px;">
<table><tr>
<td style="width: 86%;border:0px;">
<span id="display_<?php echo $rec_cn->cn_id; ?>">
<img src="../images/country_flag/<?php echo $rec_cn->cn_flag; ?>" alt="<?php echo ucwords($rec_cn->cn_name); ?>" align="top" height="16" width="23"/>
&nbsp;
<?php echo ucwords($rec_cn->cn_name); ?> - <?php echo $rec_cn->cn_currency; ?> - <?php echo $rec_cn->cn_ph; ?>
</span>
<span id="input_<?php echo $rec_cn->cn_id; ?>" style="display:none;">
<input type="text" style="width:100px;" name="country_<?php echo $rec_cn->cn_id; ?>" id="country_<?php echo $rec_cn->cn_id; ?>" value="<?php echo $rec_cn->cn_name; ?>" title="Country Name"/>
<input type="text" style="width:50px;" name="currency_<?php echo $rec_cn->cn_id; ?>" id="currency_<?php echo $rec_cn->cn_id; ?>" value="<?php echo $rec_cn->cn_currency; ?>" title="Currency"/>
<input type="text" style="width:50px;" name="phone_<?php echo $rec_cn->cn_id; ?>" id="phone_<?php echo $rec_cn->cn_id; ?>" value="<?php echo $rec_cn->cn_ph; ?>" title="Phone Code"/>
</span>

</td>
<td style="width: 12%;border:0px;">
<span id="edit_<?php echo $rec_cn->cn_id; ?>">

<a id="id-btn-job<?php echo $rec_cn->cn_id; ?>" role="button" class="editCun ajax badge badge-info" title="Edit"><i class="icon-edit"></i></a>
<!--<a href="javascript:ShowEditCountry(<?php /*echo $rec_cn->cn_id;*/ ?>)" class="ajax badge badge-info" title="Edit"><i class="icon-edit"></i></a>-->
</span>
<span id="save_<?php echo $rec_cn->cn_id; ?>" style="display:none;">
<a href="javascript:EditCountry(<?php echo $rec_cn->cn_id; ?>)" class="ajax badge badge-success" title="Update"><i class="icon-check"></i></a>
</span>
</td>
<td style="width: 4%;border:0px;">
<span id="del_<?php echo $rec_cn->cn_id; ?>">
<a href="javascript:DelCountry(<?php echo $rec_cn->cn_id; ?>)" class="badge badge-danger" title="Delete"><i class="icon-trash"></i></a>
</span>
<span id="cancel_<?php echo $rec_cn->cn_id; ?>" style="display:none;">
<a href="javascript:CancelEditCountry(<?php echo $rec_cn->cn_id; ?>)" class="ajax badge badge-danger" title="Cancel"><i class="icon-remove"></i></a>
</span>
</td>
</tr></table>
</td>
<?php $j++; ?><?php if($j == 3){?></tr><?php $j=1;} ?>


<div  id="job_form<?php echo $rec_cn->cn_id; ?>" class="backLayer" style="left: 25%; top: 5%;  display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
   			<form id="Edit_Country_<?php echo $rec_cn->cn_id; ?>" name="Add_New_Country" action="" method="post" enctype="multipart/form-data">
			<div class="modal-header">
				<button type="button" class="close" id="clse_job<?php echo $rec_cn->cn_id; ?>">&times;</button>
				<h4 class="blue bigger">Please fill the following fields</h4>
			</div>

			<div class="modal-body overflow-visible">
				<div class="row">
					<div class="col-xs-12 col-sm-5">
						<div class="space"></div>
                        
                        <input type="hidden" id="cn_id" name="cn_id" value="<?php echo $rec_cn->cn_id; ?>"/>

				<script type="text/javascript">
					jQuery(function(){
						jQuery('#file_upload<?php echo $rec_cn->cn_id; ?>').uploadifive({
							'auto'     : true,
							'formData' : {'cn_id' : '<?php echo $rec_cn->cn_id; ?>'},
							'queueID'  : 'queue',
							'debug'    : true,
							'method'   : 'post',
							'uploadScript' : 'editCountryImg.php',
							'onAddQueueItem' : function(file) {
								
								//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
							//	$("#img_disp_<?php /*echo $rec_cn->cn_id;*/ ?>").html('<img src="../images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
							},
							'onUploadComplete' : function(file,data) {
								showCountryImg(<?php echo $rec_cn->cn_id; ?>);
							}
						});
					});
				</script>
                <div>
                <div id="img_disp_<?php echo $rec_cn->cn_id; ?>">
                <img src="../images/country_flag/<?php echo $rec_cn->cn_flag; ?>" alt="<?php echo ucwords($rec_cn->cn_name); ?>" align="top" height="18" width="26"/>
                
                </div>
                <div id="drop" style="padding-left:10px;float:right">
		            <input type="file" id="file_upload<?php echo $rec_cn->cn_id; ?>" name="file_upload"/>
                    Only accepts png image.
	            </div>
	            <div id="queue"></div>
                </div>
					</div>

					<div class="col-xs-12 col-sm-7">
						<div class="form-group">
							<label for="form-field-username">Country Name</label>
							<div>
                                <input id="cn_name_<?php echo $rec_cn->cn_id; ?>" name="cn_name_<?php echo $rec_cn->cn_id; ?>" class="input-large" type="text" placeholder="Country Name" value="<?php echo $rec_cn->cn_name; ?>" />
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label for="form-field-username">Currency Code</label>

							<div>
                                <input id="cn_currency_<?php echo $rec_cn->cn_id; ?>" name="cn_currency_<?php echo $rec_cn->cn_id; ?>" class="input-medium" type="text" placeholder="Currency Code" value="<?php echo $rec_cn->cn_currency; ?>" />
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label for="form-field-first">Phone Code</label>
                            <div>
                                <input id="cn_ph_<?php echo $rec_cn->cn_id; ?>" name="cn_ph_<?php echo $rec_cn->cn_id; ?>" class="input-medium" type="text" placeholder="Phone Code" value="<?php echo $rec_cn->cn_ph; ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <!--<button class="btn btn-sm" id="clse_job<?php /*echo $rec_cn->cn_id;*/ ?>">
                    <i class="icon-remove"></i>
                    Cancel
                </button>-->

                <button class="btn btn-sm btn-primary" type="button" onclick="updCountry(<?php echo $rec_cn->cn_id; ?>);">
                    <i class="icon-ok"></i>
                    Save
                </button>
            </div>
            </form>
        </div>
    </div>
			
	
	
</div>
<!--########################******************   POPUP   *****************########################-->

<div class="background_overlay" style="display: none;"></div>
<style>
.backLayer{position: fixed;background: white;z-index: 9999999999999;}

.background_overlay { position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 999999; background:black; opacity: 0.4;}
</style>

<script type="text/javascript">
$(document).ready(function(){
//open popup

	$("#id-btn-job<?php echo $rec_cn->cn_id; ?>").click(function(){
		$("#job_form<?php echo $rec_cn->cn_id; ?>").fadeIn(200);
		$(".background_overlay").fadeIn(200);
		positionCookiePopup();	
	});
	

//close popup
	
$("#clse_job<?php echo $rec_cn->cn_id; ?>, .background_overlay").click(function(){
		$("#job_form<?php echo $rec_cn->cn_id; ?>").fadeOut(200);
		$(".background_overlay").fadeOut(200);
	});

});

function positionCookiePopup(){
  if(!$("#job_form<?php echo $rec_cn->cn_id; ?>").is(':visible')){
    return;
  } 
  $("#job_form<?php echo $rec_cn->cn_id; ?>").css({
      left: ($(window).width() - $('#job_form<?php echo $rec_cn->cn_id; ?>').width()) / 2,
      top: ($(window).height() - $('#job_form<?php echo $rec_cn->cn_id; ?>').height()) / 5,
      position:'fixed'
  });
}


$(window).bind('resize',positionCookiePopup);
</script>


<?php 
} ?>
</table>