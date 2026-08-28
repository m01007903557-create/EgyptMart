<?php 
include "../common.php";
$cid=$_GET[cid];
$sql_cn="select * from city where ct_status=1 and ct_cn_id=".$cid." order by ct_name";
$res_cn=mysqli_query($con, $sql_cn);	
?>
<table   id="sample-table-2" class="table table-striped table-bordered table-hover">
<TR><TD align="center" colspan="4">
<?php if($cid!=0){	?>
<table>
<tr id="save_link">
<TD style="border:0px;">
<span><button class="btn btn-xs btn-success" onclick="ShowaddCity()" type="button"><i class="icon-plus-sign"></i><b>ADD CITY</b></button></span>

</TD>
</tr>
<tr id="input_add" style="display:none;">
<TD style="border:0px;">
<span>
<input type="text" style="width:200px;" name="city_add" id="city_add" placeholder="Add" class="reg_txtfld" value=""/>
<select name="city_state" id="city_state">
	<option value="">State</option>
    <?php 
	$st_res = mysqli_query($con, "select * from states where state_status = '1' and state_cn_id = '".$cid."'");
	while($st_row = mysqli_fetch_object($st_res)){
	?>
    <option value="<?php echo $st_row->state_id;?>"><?php echo $st_row->state_name;?></option>
    <?php }?>
</select>
</span></TD>
<TD style="border:0px;">
<span><a href="javascript:addCity()" class="ajax badge badge-success"><i class="icon-check"></i></a></span>
</TD>
<TD style="border:0px;">
<span style=""><a href="javascript:CanCity()" class="badge badge-danger"><i class="icon-trash"></i></a></span>
</TD>
</TR></table>
<?php	}	?>
</TD><TR>
<?php  
$j=1;
while($rec_cn=mysqli_fetch_object($res_cn)) {  ?>
<?php if(($j == 1)||($j == 5)){?><tr><?php	} ?>
<td>
<table><tr>
<td style="width: 86%;border:0px;">
<span id="display_<?php echo $rec_cn->ct_id; ?>">
<?php echo $rec_cn->ct_name; ?></span>
<span id="input_<?php echo $rec_cn->ct_id; ?>" style="display:none;">
<input type="text" style="width:50px;" name="city_<?php echo $rec_cn->ct_id; ?>" id="city_<?php echo $rec_cn->ct_id; ?>" class="reg_txtfld" value="<?php echo $rec_cn->ct_name; ?>"/></span>
<span id="input_state_<?php echo $rec_cn->ct_id; ?>" style="display:none;">

<select name="state_<?php echo $rec_cn->ct_id; ?>" id="state_<?php echo $rec_cn->ct_id; ?>">
	<option value="">State</option>
    <?php 
	$st_res = mysqli_query($con, "select * from states where state_status = '1' and state_cn_id = '".$cid."'");
	while($st_row = mysqli_fetch_object($st_res)){
	?>
    <option value="<?php echo $st_row->state_id;?>" <?php if($st_row->state_id==$rec_cn->ct_state){?>selected<?php }?>><?php echo $st_row->state_name;?></option>
    <?php }?>
</select>
<select name="metro_<?php echo $rec_cn->ct_id; ?>" id="metro_<?php echo $rec_cn->ct_id; ?>">
	<option value="1">Metro</option>
    <option value="0" <?php if($st_row->state_id==$rec_cn->ct_state){?>selected<?php }?>>Non-metro</option>
</select>
</span>


</td>




<td style="width: 12%;border:0px;">
<span id="edit_<?php echo $rec_cn->ct_id; ?>"><a href="javascript:ShowEditCity(<?php echo $rec_cn->ct_id; ?>)" class="ajax badge badge-info"><i class="icon-edit"></i></a></span>
<span id="save_<?php echo $rec_cn->ct_id; ?>" style="display:none;"><a href="javascript:EditCity(<?php echo $rec_cn->ct_id; ?>)" class="ajax badge badge-success"><i class="icon-check"></i></a></span>
</td>
<td style="width: 4%;border:0px;"><a href = "javascript:DelCity(<?php echo $rec_cn->ct_id; ?>);" class="badge badge-danger"><i class="icon-trash"></i></a></td>
</tr></table>
</td>
<?php $j++; ?><?php if(($j == 1)||($j == 5)){?></tr><?php $j=1;} ?>
<?php } ?></table>