<?php
include "../common.php";


	$emp_name = $_POST['emp_name'];
	$name = explode(" ", $emp_name);
	$emp_firstName = $name[0];
	
	$fromdate = $_POST['fromdate'];
	$todate = $_POST['todate'];
	$dept_id = $_POST['dept_id'];
	
	
	$status_all = $_POST['status_all'];
	$status_rejected = $_POST['status_rejected'];
	$status_canceled = $_POST['status_canceled'];
	$status_pending = $_POST['status_pending'];
	$status_scheduled = $_POST['status_scheduled'];
	$status_taken = $_POST['status_taken'];
	
	
	
if($emp_name!="All"){	$sql_emp=" and emp_firstName like '%".$emp_firstName."%'";	}else{	$sql_emp="";	}

if($dept_id!=""){		$sql_dept=" and ej_dept_id='".$dept_id."'";		}else{		$sql_dept="";	}

if($status_all==''){
	if($status_rejected!='' || $status_canceled!='' || $status_pending!='' || $status_scheduled!='' || $status_taken!='')
	{
		$sql_leave_stat=" and (la_status='' ";
		if($status_rejected!=''){	$sql_leave_stat .= " or la_status='reject'";	}
		if($status_canceled!=''){	$sql_leave_stat .= " or la_status='cancel'";	}
		if($status_pending!=''){	$sql_leave_stat .= " or la_status='pending approval'";	}
		if($status_scheduled!=''){	$sql_leave_stat .= " or (la_status='approve' and now()<=la_from_date)";	}
		if($status_taken!=''){	$sql_leave_stat .= " or (la_status='approve' and now()>la_from_date)";	}
		$sql_leave_stat .= ")";
	}
}


//$sql="select la_from_date,la_to_date,emp_firstName,emp_lastName,DATEDIFF(la_to_date,la_from_date)+1 as duration from leave_assign,employee,employee_job,department where emp_id=la_emp_id and emp_id=ej_emp_id and ej_dept_id=dept_id and ((la_from_date between '".$fromdate."' and '".$todate."') or (la_from_date between '".$fromdate."' and '".$todate."'))".$sql_emp.$sql_dept.$sql_stat_rej.$sql_stat_can.$sql_stat_pending.$sql_stat_sche.$sql_stat_taken;

$sql="select la_id,la_from_date,la_to_date,emp_id,emp_firstName,emp_lastName,DATEDIFF(la_to_date,la_from_date)+1 as duration,la_status from leave_assign,employee,employee_job,department where emp_id=la_emp_id and emp_id=ej_emp_id and ej_dept_id=dept_id and ((la_from_date between '".$fromdate."' and '".$todate."') or (la_from_date between '".$fromdate."' and '".$todate."'))".$sql_emp.$sql_dept.$sql_leave_stat;

$recObj=mysqli_query($con, $sql);

?>
	<br clear="all"/>
        <?php
	$count=mysqli_num_rows($recObj);
		if($count >0)
		{
	?>
<table class="items">
<thead>
<tr>
    <th class="usr-name" style="width:200px;"><strong>Date</strong></th>
    <th class="usr-name" style="width:160px;"><strong>Name</strong></th>
    <th class="usr-name" style="width:90px;"><strong>Duration (Days)</strong></th>
    <th class="usr-name" style="width:200px;"><strong>Status</strong></th>
    <th class="action"><strong>Action</strong></th>
    <th class="usr-name" style="width:130px;">
    <button formaction="csv-files/csv-leave-list.php" type='submit' class="delete-btn" name="exptocsv" id="exptocsv">Export to CSV</button> 
    </th>
</thead>
<tbody>
    	<?php $j=0;
		while($row=mysqli_fetch_object($recObj)){ ?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
            <td class="usr-name" style="width:200px; text-align:center;">
            	<?php 
				if($row->la_from_date==$row->la_to_date)
				{
					echo date("d-M-Y",strtotime($row->la_from_date));	
				}
				else
				{
				echo date("d-M-Y",strtotime($row->la_from_date))." - ".date("d-M-Y",strtotime($row->la_to_date)); 
				}
				?>
            </td>
			<td class="usr-name" style="width:160px; text-align:center;">
            <?php echo ucfirst($row->emp_firstName)." ".ucfirst($row->emp_lastName); ?>&nbsp;(Id: <?php printf("%05d", $row->emp_id); ?>)
            </td>
            <td class="usr-name" style="width:200px; text-align:center;"><?php echo $row->duration;	?></td>
            <td class="usr-name" style="width:200px; text-align:center;">
            <?php
			if($row->la_status=='approve' && $row->la_to_date < date("Y-m-d")){
			echo "Taken";	
			}
			else
			{
			echo ucwords($row->la_status); 
			}
			?>
            </td>
            <td class="action">
            <?php	if($row->la_status=='pending approval' && $row->la_from_date > date("Y-m-d")){	?>
            <select name="chgStatus" id="chgStatus" onchange="updLeaveStatus('<?php echo $row->la_id; ?>',this.value)">
            <option value=""> - Select - </option>
            <option value="approve">Approve</option>
            <option value="reject">Reject</option>
            </select>
            <?php }	?>
            </td>
            <td  style="width:130px;">&nbsp;</td>
        </tr>
        <?php $j++; } ?>
</tbody>
</table>
<?php } else { ?>
<table class="items">
<tbody>
        <tr>
            <td class="usr-name" style="width:200px; text-align:center; color:#E00; font-weight:bold; font-size:13px;">No Records.</td>
        </tr>     
</tbody>
</table>
<?php } ?>