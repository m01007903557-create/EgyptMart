<?php 
include "../common.php";
$cid=$_GET[cid];
$sql_cn="select * from states where state_status=1 and state_cn_id=".$cid." order by state_name";
$res_cn=mysqli_query($con, $sql_cn);	
?>
<table  id="sample-table-2" class="table table-striped table-bordered table-hover">
<TR><TD align="center" colspan="4">
<?php if($cid!=0){	?>
<table>
<tr id="save_link">
<td style="width:10px;border:0px;">&nbsp;</td>
<TD style="border:0px;">

<span><button class="btn btn-xs btn-success" onclick="ShowaddState()" type="button"><i class="icon-plus-sign"></i><b>ADD STATE</b></button></span>


</TD>
</tr>
<tr id="input_add" style="display:none;">
<td style="width:10px;border:0px;">&nbsp;</td>
<TD style="border:0px;">
<span>
<input type="text" style="width:200px;" name="states_add" id="states_add" placeholder="Add" class="reg_txtfld" value=""/>
</span></TD>
<TD style="border:0px;">
<span>
<a href="javascript:addState()" class="ajax badge badge-success">
<i class="icon-check"></i></a></span>
</TD>
<TD style="border:0px;">
<span style="">
<a href="javascript:CanState()" class="badge badge-danger">
<i class="icon-trash"></i></a></span>
</TD>
</TR></table>
<?php } ?>
</TD><TR>
<?php  
$j=1;
while($rec_cn=mysqli_fetch_object($res_cn)) {  ?>
<?php if(($j == 1)||($j == 5)){?><tr><?php	} ?>
<td>
<table><tr>
<td style="width:86%;border:0px;">
<span id="display_<?php echo $rec_cn->state_id; ?>">
<?php echo $rec_cn->state_name; ?></span>
<span id="input_<?php echo $rec_cn->state_id; ?>" style="display:none;">
<input type="text" style="width:150px;" name="states_<?php echo $rec_cn->state_id; ?>" id="states_<?php echo $rec_cn->state_id; ?>" class="reg_txtfld" value="<?php echo $rec_cn->state_name; ?>"/></span>

</td>
<td style="width: 12%;border:0px;">
<span id="edit_<?php echo $rec_cn->state_id; ?>">
<a href="javascript:ShowEditState(<?php echo $rec_cn->state_id; ?>)" class="ajax badge badge-info">
<i class="icon-edit"></i></a></span>
<span id="save_<?php echo $rec_cn->state_id; ?>" style="display:none;">
<a href="javascript:EditState(<?php echo $rec_cn->state_id; ?>)" class="ajax badge badge-success">
<i class="icon-check"></i></a></span>
</td>
<td style="width: 4%;border:0px;"><a href=javascript:DelState(<?php echo $rec_cn->state_id; ?>) class="badge badge-danger">
<i class="icon-trash"></i></a></td>
</tr></table>
</td>
<?php $j++; ?><?php if(($j == 1)||($j == 5)){?></tr><?php $j=1;} ?>
<?php } ?></table>