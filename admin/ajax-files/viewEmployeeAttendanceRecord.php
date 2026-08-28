<?php
include "../common.php";

$ea_emp_id=$_POST['ea_emp_id'];
$ea_date=$_POST['ea_date'];

$sql="select * from employee,employee_attendance where emp_id=ea_emp_id and ea_emp_id='".$ea_emp_id."' and ea_date='".$ea_date."'";
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
		<div class="eID" style="width:180px;"><strong>Name</strong></div>
		<div class="eID" style="width:80px"><strong>Punch In</strong></div>
		<div class="eID" style="width:170px"><strong>Punch In Note</strong></div>
		<div class="eID" style="width:80px"><strong>Punch Out</strong></div>
		<div class="eID" style="width:170px"><strong>Punch Out Note</strong></div>
		<div class="eID" style="width:140px"><strong>Duration</strong></div>
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
                <div class="eID" style="width:180px"><?php echo $row->emp_firstName." ".$row->emp_lastName; ?></div>	
                <div class="eID" style="width:80px"><?php echo ucfirst($row->ea_inTime); ?></div>	
                <div class="eID" style="width:170px"><?php echo ucwords($row->ea_inNote); ?></div>	
                <div class="eID" style="width:80px"><?php echo ucfirst($row->ea_outTime); ?></div>	
                <div class="eID" style="width:170px"><?php echo ucwords($row->ea_outNote); ?></div>
                <div class="eID" style="width:140px">
				<?php 
				$minute=round((strtotime($row->ea_outTime)-strtotime($row->ea_inTime))/60);
				$hr=round($minute/60);
				$min=round($minute%60);
				echo $hr." hours ".$min." minutes"; ?>
                
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