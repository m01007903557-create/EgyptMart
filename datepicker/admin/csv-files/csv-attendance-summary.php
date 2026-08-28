<?php
ob_start();
include "../common.php";


//if($_POST['viewcsv']){
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




$filename = tempnam(sys_get_temp_dir(), "csv");

$file = fopen($filename,"w");


    $fieldArray = array('First Name','Last Name','Designation','Department','Duration');

fputcsv($file,$fieldArray);

// Write data rows
$result = mysqli_query($con, "select emp_firstName,emp_lastName, jt_title, dept_name, SEC_TO_TIME(sum(TIME_TO_SEC(ea_outTime)-TIME_TO_SEC(ea_inTime))) as worked from employee,employee_attendance,employee_job, job_title,department where ej_dept_id=dept_id and ej_jt_id=jt_id and emp_id=ea_emp_id and emp_id=ej_emp_id and ea_date between '".$ea_fromdate."' and '".$ea_todate."'".$sql_ea.$sql_jt.$sql_dept.$sql_es." group by emp_id");

for ($i = 0; $i < mysqli_num_rows($result); $i++) {
    $dataArray[$i] = mysql_fetch_assoc($result);
}
foreach ($dataArray as $line) {
    fputcsv($file,$line);
}

fclose($file);

header("Content-Type: application/csv");
header("Content-Disposition: attachment;Filename=attendance-summary.csv");

// send file to browser
readfile($filename);
unlink($filename);

	

//}
?>