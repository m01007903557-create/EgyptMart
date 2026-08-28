<?php
include "../common.php";


	$emp_id = $_POST['emp_id'];
	$fromdate = $_POST['fromdate'];
	$todate = $_POST['todate'];
	
	
	$status_all = $_POST['status_all'];
	$status_rejected = $_POST['status_rejected'];
	$status_canceled = $_POST['status_canceled'];
	$status_pending = $_POST['status_pending'];
	$status_scheduled = $_POST['status_scheduled'];
	$status_taken = $_POST['status_taken'];
	
	
	
/*if($emp_id!="All"){	$sql_emp=" and emp_id='".$emp_id."'";	}else{	$sql_emp="";	}
if($dept_id!=""){		$sql_dept=" and ej_dept_id='".$dept_id."'";		}else{		$sql_dept="";	}*/

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

$sql="select la_id,la_from_date,la_to_date,emp_firstName,emp_lastName,DATEDIFF(la_to_date,la_from_date)+1 as duration,la_status from leave_assign,employee,employee_job where emp_id='".$emp_id."' and emp_id=la_emp_id and emp_id=ej_emp_id and ((la_from_date between '".$fromdate."' and '".$todate."') or (la_from_date between '".$fromdate."' and '".$todate."'))".$sql_leave_stat;

$recObj=mysqli_query($con, $sql);

?>
	<br clear="all"/>
    <?php
	$count=mysqli_num_rows($recObj);
		if($count >0)
		{
	?>
	<div class="admin-hdr-bg">				 
		<div class="eID" style="width:5px;"></div>
        <div class="eID" style="width:180px;"><strong>Date</strong></div>
		<!--<div class="eID" style="width:180px;"><strong>Name</strong></div>-->
        <div class="eID" style="width:100px;"><strong>Duration (Days)</strong></div>
		<div class="eID" style="width:110px;" align="center"><strong>Status</strong></div>
        <div class="action"><strong>Action</strong></div>
		<div class="clr"></div>
		<br clear="all"/>
	</div>
	<?php $j=0;
			while($row=mysqli_fetch_object($recObj)){
	?>
	<div class="admin-dtls">
        <ul>
            <li <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?>>
                <div class="eID" style="width:5px"></div>	
                <div class="eID" style="width:180px">
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
                </div>
                <!--<div class="eID" style="width:180px"><?php /*echo ucfirst($row->emp_firstName)." ".ucfirst($row->emp_lastName);*/ ?></div>-->	
                <div class="eID" style="width:100px;" align="center"><?php echo $row->duration;	?></div>
                <div class="eID" style="width:110px;" align="center">
				<?php
					if($row->la_status=='approve' && $row->la_to_date < date("Y-m-d")){
						echo "Taken";
					}
					else
					{
						echo ucwords($row->la_status); 
					}
				?>
                </div>
                <div class="action">
                <?php		if($row->la_status=='pending approval' && $row->la_from_date > date("Y-m-d")){	?>
                	<select name="chgStatus" id="chgStatus" onchange="updLeaveStatus('<?php echo $row->la_id; ?>',this.value)">
                    <option value=""> - Select - </option>
                    <option value="cancel">Cancel</option>
                    </select>
                <?php	}	?>
                </div>
                <div class="clr"></div>
            </li>
           
        </ul>   							
	</div> <!-- end admin-dtls -->
	<?php $j++; } }
		else
		{
	?>
	<div class="admin-dtls" style="text-align:center; color:red; border-bottom:1px solid #767676; padding-bottom:5px; font-weight:bold; font-size:13px;">
<div class="clr"></div>No Records.</div>
	<?php } ?>
						 <!-- end pagicon -->