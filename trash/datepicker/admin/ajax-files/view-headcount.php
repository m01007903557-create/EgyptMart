<?php 
ob_start();
session_start(); 
include "../common.php";
	
	 $es_id = $_POST['es_id'];
	 $dept_id = $_POST['dept_id'];
	
if($es_id != '' && $dept_id != ''){
$sql = "select * from department join employee_job join employment_status where department.dept_id = employee_job.ej_dept_id and employee_job.ej_es_id = employment_status.es_id and employment_status.es_id = '".$es_id."' and department.dept_id = '".$dept_id."'";
$res = mysqli_query($con, $sql);
$dept = mysqli_fetch_array( $res); 
$emp = mysqli_num_rows($res);
}

if($dept_id != '')
{
	 $sql = "select * from department join employee_job where department.dept_id = employee_job.ej_dept_id  and department.dept_id = '".$dept_id."'";
$res = mysqli_query($con, $sql);
$dept = mysqli_fetch_array( $res); 
$emp = mysqli_num_rows($res);
}

 ?>
<table class="items">
<thead>
<tr>
    <th class="usr-name" style="width:200px;"><strong>Department Name</strong></th>
    <th class="usr-name" style="width:200px;"><strong>No. of Employee</strong></th>
</thead>
<tbody>
                                     <?php if($es_id == '' && $dept_id == '') {
									  $n_sql = "SELECT dept_name, count( ej_dept_id ) AS no_of_emp FROM employee_job JOIN department WHERE employee_job.ej_dept_id = department.dept_id GROUP BY dept_name";
									   $n_res = mysqli_query($con, $n_sql);
									   while($n_emp = mysqli_fetch_array( $n_res)){
									  ?>
        <tr>
            <td class="usr-name" style="width:200px; text-align:center;"><?php	echo $n_emp['dept_name'];	?></td>
			<td class="usr-name" style="width:200px; text-align:center;"><?php echo $n_emp['no_of_emp']; ?></td>
        </tr>
       <?php } } ?>
        <?php if( $dept_id != '') {?>
                <tr>
            <td class="usr-name" style="width:200px; text-align:center;">
                                         <?php  
										if($dept['dept_name'] != ''){
										    echo $dept['dept_name']; 
										}
										else if($emp == 0)
										{  
										$sql1 = "select * from  department where dept_id='".$dept_id."'";
                                           $res1 = mysqli_query($con, $sql1);
                                             $emp1 = mysqli_fetch_array( $res1);
											echo $emp1['dept_name'];
										}
										?>
            </td>
			<td class="usr-name" style="width:200px; text-align:center;"><?php echo $emp; ?></td>
        </tr>
        <?php }  ?>
                                     <?php if($es_id != '') {
									  $n_sql = "SELECT dept_name, count( ej_dept_id ) AS no_of_emp FROM employee_job JOIN department join employment_status WHERE employee_job.ej_dept_id = department.dept_id and employee_job.ej_es_id=employment_status.es_id and employment_status.es_id = '".$es_id."' GROUP BY dept_name";
									   $n_res = mysqli_query($con, $n_sql);
									   while($n_emp = mysqli_fetch_array( $n_res)){
									  ?> 
        <tr>
            <td class="usr-name" style="width:200px; text-align:center;"><?php	echo $n_emp['dept_name'];	?></td>
			<td class="usr-name" style="width:200px; text-align:center;"><?php echo $n_emp['no_of_emp']; ?></td>
        </tr>
                                      <?php } } ?> 
</tbody>
</table>