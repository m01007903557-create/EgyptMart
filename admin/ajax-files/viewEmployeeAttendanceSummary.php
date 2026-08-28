<?php
include "../common.php";

//$ea_emp_id=$_POST['ea_emp_id'];
//$ea_date=$_POST['ea_date'];

	$emp_name = $_POST['emp_name'];
	$name = explode(" ", $emp_name);
	$emp_firstName = $name[0];
	
	$jt_id = $_POST['jt_id'];
	$dept_id = $_POST['dept_id'];
	$es_id = $_POST['es_id'];
	$ea_fromdate = $_POST['ea_fromdate'];
	$ea_todate = $_POST['ea_todate'];
	
if($emp_name!="All"){	$sql_ea=" and emp_firstName like '%".$emp_firstName."%'";	}else{	$sql_ea="";	}

if($jt_id!=""){		$sql_jt=" and ej_jt_id='".$jt_id."'";	}else{		$sql_jt="";		}

if($dept_id!=""){		$sql_dept=" and ej_dept_id='".$dept_id."'";		}else{		$sql_dept="";	}

if($es_id!=""){		$sql_es=" and ej_es_id='".$es_id."'";	}else{		$sql_es="";		}



$sql="select emp_id,emp_firstName,emp_lastName,ea_inTime,ea_outTime,SEC_TO_TIME(sum(TIME_TO_SEC(ea_outTime)-TIME_TO_SEC(ea_inTime))) as worked,ej_jt_id,ej_dept_id from employee,employee_attendance,employee_job where emp_id=ea_emp_id and emp_id=ej_emp_id and ea_date between '".$ea_fromdate."' and '".$ea_todate."'".$sql_ea.$sql_jt.$sql_dept.$sql_es." group by emp_id";

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
    <th class="usr-name" style="width:200px;"><strong>Name</strong></th>
    <th class="usr-name" style="width:200px;"><strong>Designation</strong></th>
    <th class="action"><strong>Duration</strong></th>
    <th class="usr-name" style="width:200px;">
    <button formaction="csv-files/csv-attendance-summary.php" type='submit' class="delete-btn" name="viewcsv" id="viewcsv">Export to CSV</button>
    </th>
</thead>
<tbody>
    	<?php $j=0;
		while($row=mysqli_fetch_object($recObj)){ ?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
            <td class="usr-name" style="width:200px; text-align:center;"><?php echo ucfirst($row->emp_firstName)." ".ucfirst($row->emp_lastName); ?></td>
                            <?php
                	$desg_sql="select * from employee_job,department,job_title where ej_dept_id=dept_id and ej_jt_id=jt_id and ej_dept_id='".$row->ej_dept_id."' and ej_jt_id='".$row->ej_jt_id."'";
					$desg_res=mysqli_query($con, $desg_sql);
					$desg_row=mysqli_fetch_object($desg_res);
				?>

			<td class="usr-name" style="width:200px; text-align:center;"><?php echo $desg_row->jt_title. " (".$desg_row->dept_name.")"; ?></td>
            <td class="action">
            <?php 
				/*$minute=round((strtotime($row->ea_outTime)-strtotime($row->ea_inTime))/60);
				$hr=round($minute/60);
				$min=round($minute%60);
				echo $hr." hours ".$min." minutes"; */
//				echo date("H:m",strtotime($row->worked));
				echo $row->worked;
				?>
            </td>
           
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