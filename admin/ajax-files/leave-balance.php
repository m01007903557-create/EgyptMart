<?php
ob_start();
session_start(); 
include "../common.php";	

if($_POST['la_emp_id'])
{   
    $la_emp_id = $_POST['la_emp_id'];
    $la_lt_id = $_POST['la_lt_id'];
	
	 
	$sql_tkn="select sum(DATEDIFF(la_to_date,la_from_date)+1) as lve from leave_assign where la_lt_id='".$la_lt_id."' and la_emp_id='".$la_emp_id."' and la_status='approve'";
	$res_tkn = mysqli_query($con, $sql_tkn);
	$row_tkn = mysqli_fetch_object($res_tkn);
	 
	$sql_avl = "select * from leave_type where lt_id= '".$la_lt_id."'";
	$res_avl = mysqli_query($con, $sql_avl);
	$row_avl = mysqli_fetch_object($res_avl);
	
	$bal_leave=$row_avl->lt_no_of_leave - $row_tkn->lve;
/*	if($bal_leave>0){
		
	}
	else
	{
		
	}
*/
	if($bal_leave<=0){	?><font color="#FF0000"><?php echo $bal_leave." days"; ?></font><?php	}
	else{ echo $bal_leave." days";	}
	
}

?>