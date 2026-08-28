<?php
include "../common.php";

$fromdate = $_POST['fromdate'];
$todate = $_POST['todate'];
$dept_id = $_POST['dept_id'];
	
if($dept_id!=""){ $sql_dept=" and ej_dept_id='".$dept_id."'";} else{	$sql_dept="";	}

$sql="select * from employee,employee_job,department,job_title where emp_id=ej_emp_id and ej_dept_id=dept_id and ej_jt_id=jt_id and emp_status='1' and ej_endDate between '".$fromdate."' and '".$todate."'".$sql_dept;
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
    <th class="usr-name" style="width:200px;"><strong>Employee Name</strong></th>
    <th class="usr-name" style="width:200px;"><strong>Job Title</strong></th>
    <th class="usr-name" style="width:200px;"><strong>Department</strong></th>
    <th class="usr-name" style="width:200px;"><strong>Termination Date</strong></th>
</thead>
<tbody>
    	<?php $j=0;
		while($row=mysqli_fetch_object($recObj)){ ?>
        <tr <?php if($j % 2 == 1) { ?> class="row-clr" <?php } ?> >
            <td class="usr-name" style="width:200px; text-align:center;"><?php echo ucfirst($row->emp_firstName)." ".ucfirst($row->emp_lastName); ?></td>
			<td class="usr-name" style="width:200px; text-align:center;"><?php echo $row->jt_title;	?></td>
            <td class="usr-name" style="width:200px; text-align:center;"><?php echo $row->dept_name;	?></td>
            <td class="usr-name" style="width:200px; text-align:center;"><?php echo date("d-M-Y",strtotime($row->ej_endDate)); ?></td>
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