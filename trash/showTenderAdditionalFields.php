<?php
include "common.php";
$id=$_POST['id'];

$fld_id="";
$fld_type="";

$sql="select * from additional_field where af_pc_id='".$id."'";
$res=mysql_query($sql);
if(mysql_num_rows($res)>=1)
{

?>

    <!--<h2 class="legend" style="margin-top:20px;"><?php /*echo $lang[404];*/ ?></h2>-->
	
    <?php
    $f=0;
	while($row=mysql_fetch_object($res)){ 
	if($fld_id!='' && $fld_type!='')
	{
		$fld_id.=",";
		$fld_type.=",";
	}
	$fld_id.=$row->af_id;
	$fld_type.=$row->af_type;
	?>
		<tr id="r4">
      
        	<td valign="TOP" width="30%"><p class="pd15"><b><?php echo stripslashes($row->af_label); ?></b></p></td>
            <td valign="TOP">
            <?php if($row->af_type=="text"){	?>
			<input id="<?php echo $row->af_id; ?>" name="<?php echo $row->af_id; ?>" tabindex="100" maxlength="50" type="text"/><span class="status-icon"></span>
            <?php }else if($row->af_type=="select"){ ?>
            <select id="<?php echo $row->af_id; ?>" name="<?php echo $row->af_id; ?>">
				<option value=""><?php echo $lang[133]; ?></option>
                <?php
				$sql_afv="select * from additional_field_value where afv_af_id='".$row->af_id."'";
				$res_afv=mysql_query($sql_afv);
				if(mysql_num_rows($res_afv)>0)
				{
					$j=1;
					while($row_afv=mysql_fetch_object($res_afv))
					{
			?>
            		<option value="<?php echo $row_afv->afv_value; ?>"><?php echo $row_afv->afv_value; ?></option>
            <?php	}
			$j++;
				}	?>
			</select>
            <?php }else if($row->af_type=="radio"){

				$sql_afv="select * from additional_field_value where afv_af_id='".$row->af_id."'";
				$res_afv=mysql_query($sql_afv);
				if(mysql_num_rows($res_afv)>0)
				{
					$j=1;
					while($row_afv=mysql_fetch_object($res_afv))
					{
			?>
                <input id="radio-<?php echo $row->af_id.$j; ?>" name="radio-<?php echo $row->af_id; ?>" type="radio" value="<?php echo $row_afv->afv_value; ?>"/><label style="top:0px;"><?php echo $row_afv->afv_value; ?></label>

            <?php	}
			$j++;
				}	?>
              
			<?php }else if($row->af_type=="textarea"){ ?>
            <textarea style="overflow: hidden;" id="<?php echo $row->af_id; ?>" name="<?php echo $row->af_id; ?>" tabindex="103"></textarea>
            <?php }else if($row->af_type=="checkbox"){ ?>

				<?php
				$sql_afv="select * from additional_field_value where afv_af_id='".$row->af_id."'";
				$res_afv=mysql_query($sql_afv);
				if(mysql_num_rows($res_afv)>0)
				{
					$j=1;
					while($row_afv=mysql_fetch_object($res_afv))
					{
			?>
                
                <input id="chk-<?php echo $row->af_id.$j; ?>" name="chk-<?php echo $row->af_id; ?>[]" type="checkbox" value="<?php echo $row_afv->afv_value; ?>"><label style="top:0px;"><?php echo $row_afv->afv_value; ?></label>&nbsp;

            <?php	}
			$j++;
				}	?>

            <?php } ?>
			
    	</td>
        </tr>
	<?php 
	$f++;
	} ?>
        
    
<?php } ?>|<?php echo $fld_id; ?>|<?php echo $fld_type; ?>