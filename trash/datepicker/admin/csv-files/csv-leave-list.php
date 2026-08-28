<?php
ob_start();
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
	
	
	
if($emp_name!="All"){$sql_emp=" and emp_firstName like '%".$emp_firstName."%'";	}else{	$sql_emp="";}

if($dept_id!=""){$sql_dept=" and ej_dept_id='".$dept_id."'";}else{$sql_dept="";}

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


$filename = tempnam(sys_get_temp_dir(), "csv");

$file = fopen($filename,"w");


    $fieldArray = array('From','To','First Name','Last Name','Duration(days)','Status');

fputcsv($file,$fieldArray);

// Write data rows
$result = mysqli_query($con, "select la_from_date,la_to_date,emp_firstName,emp_lastName,DATEDIFF(la_to_date,la_from_date)+1 as duration,la_status from leave_assign,employee,employee_job,department where emp_id=la_emp_id and emp_id=ej_emp_id and ej_dept_id=dept_id and ((la_from_date between '".$fromdate."' and '".$todate."') or (la_from_date between '".$fromdate."' and '".$todate."'))".$sql_emp.$sql_dept.$sql_leave_stat);

for ($i = 0; $i < mysqli_num_rows($result); $i++) {
    $dataArray[$i] = mysql_fetch_assoc($result);
}
foreach ($dataArray as $line) {
    fputcsv($file,$line);
}

fclose($file);

header("Content-Type: application/csv");
header("Content-Disposition: attachment;Filename=leave-list.csv");

// send file to browser
readfile($filename);
unlink($filename);
?>