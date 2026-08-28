<?php
include "../common.php";
$q=$_GET["q"];

//lookup all hints from array if length of q>0
if (strlen($q) > 0 )
{
	$sql="select * from additional_field where af_pc_id=".$q." order by af_name";  
	$j=0;
	$result=mysqli_query($con, $sql);
	?>
    
<div class="table-responsive">
<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<thead>
<tr>
	<th class="center">&nbsp;</th>
	<th><strong>Field Name</strong></th>
	<th><strong>Field Label</strong></th>
    <th><strong>Field Type</strong></th>
    <th style="text-align:center"><strong>Action</strong></th>
    </tr>
</thead>
<tbody>
    	<?php $j=0;
		if(mysqli_num_rows($result)>0)
	{
		while($row=mysqli_fetch_object($result)){	?>
        <tr>
	        <td class="center">#<?php echo $j+1;?></td>
            <td>
	            <span id="display_nm_<?php echo $row->af_id; ?>"><?php echo $row->af_name; ?></span>
				<span id="input_nm_<?php echo $row->af_id; ?>" style="display:none;">
					<input type="text" name="af_nm_<?php echo $row->af_id; ?>" id="af_nm_<?php echo $row->af_id; ?>" class="col-sm-6" value="<?php echo $row->af_name; ?>"/>
				</span>
            </td>
            <td>
	            <span id="display_lbl_<?php echo $row->af_id; ?>"><?php echo $row->af_label; ?></span>
				<span id="input_lbl_<?php echo $row->af_id; ?>" style="display:none;">
					<input type="text" name="af_lbl_<?php echo $row->af_id; ?>" id="af_lbl_<?php echo $row->af_id; ?>" class="col-sm-9" value="<?php echo $row->af_label; ?>"/>
				</span>
            </td>
            <td><span id="display_<?php echo $row->af_id; ?>"><?php echo ucfirst($row->af_type); ?></span></td>
            <td align="center">
				<span id="edit_<?php echo $row->af_id; ?>">
					<a href="javascript:ShowEditField('<?php echo  $row->af_id; ?>')" style="text-decoration:none;">
						<img width="15"  alt="edit" src="images/edit.jpg" border="0" style="styles:clear" />
					</a>
                    &nbsp;
                    <a href=javascript:DelField('<?php echo $row->af_id; ?>')>
						<img width="15"  alt="delete" src="images/delete.jpg" border="0" style="styles:clear" />
					</a>
				</span>
				<span id="save_<?php echo $row->af_id; ?>" style="display:none;">
					<a href="javascript:SaveField('<?php echo  $row->af_id; ?>')">
						<img width="15"  alt="save" src="images/save.png" border="0" style="styles:clear" />
					</a>
				</span>
				
            </td>
        </tr>
        <?php $j++; }
	}
	else
	{	?>
		<tr><td colspan="5" align="center" style="color:#F00"><b>No Fields</b></td></tr>	
<?php	}
		?>
</tbody>
</table>
</div>

    <?php
}
//echo $ct;
?>
<script type="text/javascript">
			jQuery(function($) {
				var oTable1 = $('#sample-table-2').dataTable( {
				"aoColumns": [
			      { "bSortable": false },
			      null,null,null, { "bSortable": false },
				  { "bSortable": false }
				] } );
				
				$('table th input:checkbox').on('click' , function(){
					var that = this;
					$(this).closest('table').find('tr > td:first-child input:checkbox')
					.each(function(){
						this.checked = that.checked;
						$(this).closest('tr').toggleClass('selected');
					});
						
				});
			
			
				$('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
				function tooltip_placement(context, source) {
					var $source = $(source);
					var $parent = $source.closest('table')
					var off1 = $parent.offset();
					var w1 = $parent.width();
			
					var off2 = $source.offset();
					var w2 = $source.width();
			
					if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
					return 'left';
				}
				
			
			
							
			
			
				
				$('#id-input-file-1 , #id-input-file-2').ace_file_input({
					no_file:'No File ...',
					btn_choose:'Choose',
					btn_change:'Change',
					droppable:false,
					onchange:null,
					thumbnail:false //| true | large
					//whitelist:'gif|png|jpg|jpeg'
					//blacklist:'exe|php'
					//onchange:''
					//
				});
				
				$('#id-input-file-3').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'small'//large | fit
					//,icon_remove:null//set null, to hide remove/reset button
					/**,before_change:function(files, dropped) {
						//Check an example below
						//or examples/file-upload.html
						return true;
					}*/
					/**,before_remove : function() {
						return true;
					}*/
					,
					preview_error : function(filename, error_code) {
						//name of the file that failed
						//error_code values
						//1 = 'FILE_LOAD_FAILED',
						//2 = 'IMAGE_LOAD_FAILED',
						//3 = 'THUMBNAIL_FAILED'
						//alert(error_code);
					}
			
				}).on('change', function(){
					//console.log($(this).data('ace_input_files'));
					//console.log($(this).data('ace_input_method'));
				});
				
			
				//dynamically change allowed formats by changing before_change callback function
				$('#id-file-format').removeAttr('checked').on('change', function() {
					var before_change
					var btn_choose
					var no_icon
					if(this.checked) {
						btn_choose = "Drop images here or click to choose";
						no_icon = "icon-picture";
						before_change = function(files, dropped) {
							var allowed_files = [];
							for(var i = 0 ; i < files.length; i++) {
								var file = files[i];
								if(typeof file === "string") {
									//IE8 and browsers that don't support File Object
									if(! (/\.(jpe?g|png|gif|bmp)$/i).test(file) ) return false;
								}
								else {
									var type = $.trim(file.type);
									if( ( type.length > 0 && ! (/^image\/(jpe?g|png|gif|bmp)$/i).test(type) )
											|| ( type.length == 0 && ! (/\.(jpe?g|png|gif|bmp)$/i).test(file.name) )//for android's default browser which gives an empty string for file.type
										) continue;//not an image so don't keep this file
								}
								
								allowed_files.push(file);
							}
							if(allowed_files.length == 0) return false;
			
							return allowed_files;
						}
					}
					else {
						btn_choose = "Drop files here or click to choose";
						no_icon = "icon-cloud-upload";
						before_change = function(files, dropped) {
							return files;
						}
					}
					var file_input = $('#id-input-file-3');
					file_input.ace_file_input('update_settings', {'before_change':before_change, 'btn_choose': btn_choose, 'no_icon':no_icon})
					file_input.ace_file_input('reset_input');
				});
			
			
			
			
				$('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
				.on('change', function(){
					//alert(this.value)
				});
				$('#spinner2').ace_spinner({value:0,min:0,max:10000,step:100, touch_spinner: true, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
				$('#spinner3').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'icon-plus smaller-75', icon_down:'icon-minus smaller-75', btn_up_class:'btn-success' , btn_down_class:'btn-danger'});
			
			
				
				$('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				$('input[name=date-range-picker]').daterangepicker().prev().on(ace.click_event, function(){
					$(this).next().focus();
				});
				
				$('#timepicker1').timepicker({
					minuteStep: 1,
					showSeconds: true,
					showMeridian: false
				}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				
				$('#colorpicker1').colorpicker();
				$('#simple-colorpicker-1').ace_colorpicker();
			
				
				$(".knob").knob();
				
				
				//we could just set the data-provide="tag" of the element inside HTML, but IE8 fails!
				var tag_input = $('#form-field-tags');
				if(! ( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase())) ) 
				{
					tag_input.tag(
					  {
						placeholder:tag_input.attr('placeholder'),
						//enable typeahead by specifying the source array
						source: ace.variable_US_STATES,//defined in ace.js >> ace.enable_search_ahead
					  }
					);
				}
				else {
					//display a textarea for old IE, because it doesn't support this plugin or another one I tried!
					tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
					//$('#form-field-tags').autosize({append: "\n"});
				}
				
				
				
			
				/////////
				$('#modal-form input[type=file]').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'large'
				})
				
				//chosen plugin inside a modal will have a zero width because the select element is originally hidden
				//and its width cannot be determined.
				//so we set the width after modal is show
				$('#modal-form').on('shown.bs.modal', function () {
					$(this).find('.chosen-container').each(function(){
						$(this).find('a:first-child').css('width' , '210px');
						$(this).find('.chosen-drop').css('width' , '210px');
						$(this).find('.chosen-search input').css('width' , '200px');
					});
				})
				/**
				//or you can activate the chosen plugin after modal is shown
				//this way select element becomes visible with dimensions and chosen works as expected
				$('#modal-form').on('shown', function () {
					$(this).find('.modal-chosen').chosen();
				})
				*/
							});
			</script>
