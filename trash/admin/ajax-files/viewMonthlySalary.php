<?php
include "../common.php";
$months=array("January","February","March","April","May","June","July","August","September","October","November","December");

/*	$status_all = $_POST['status_all'];
	$status_rejected = $_POST['status_rejected'];
	$status_canceled = $_POST['status_canceled'];
	$status_pending = $_POST['status_pending'];
	$status_scheduled = $_POST['status_scheduled'];
	$status_taken = $_POST['status_taken'];

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
}*/



$emp_id = $_POST['emp_id'];
$year = $_POST['year'];
$month = $_POST['month'];

//start weekly calculation
//$year           = 2010;


//end weekly calculation
	
$workdays = array();
$type = CAL_GREGORIAN;
//$month = date('n'); // Month ID, 1 through to 12.
//$year = date('Y'); // Year in 4 digit 2009 format.
$day_count = cal_days_in_month($type, $month, $year); // Get the amount of days
 
//loop through all days
for ($i = 1; $i <= $day_count; $i++) {
 
		$date = $year.'/'.$month.'/'.$i; //format date
		$get_name = date('l', strtotime($date)); //get week day
		$day_name = substr($get_name, 0, 3); // Trim day name to 3 chars
 
		//if not a weekend add day to array
		if($day_name != 'Sun' && $day_name != 'Sat'){
			$workdays[] = $i;
		}
 
}
$sql_es="select * from employee_job,department,employee_salary,job_title where ej_emp_id='".$emp_id."' and ej_dept_id=dept_id and ej_jt_id=jt_id and es_emp_id='".$emp_id."' limit 1";
$res_es=mysqli_query($con, $sql_es);
$row_es=mysqli_fetch_object($res_es);


$sql_hl="select * from holiday_list where year(hl_fromDate)='".$year."' and month(hl_fromDate)='".$month."' and hl_status='1' order by hl_fromDate";
$res_hl=mysqli_query($con, $sql_hl);

$c=0;
$hDays=array();
//$lvType=array();
while($row_hl=mysqli_fetch_object($res_hl))
{
	$sStartDate = gmdate("Y-m-d", strtotime($row_hl->hl_fromDate));  
	$sEndDate = gmdate("Y-m-d", strtotime($row_hl->hl_toDate));  
	$sCurrentDate = $sStartDate;  
  // While the current date is less than the end date  
	while($sCurrentDate <= $sEndDate){  
		// Add a day to the current date  
		$sCurrentDate = date("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));  
		$hDays[$c] = date("d",strtotime($sCurrentDate)); 
		$c++;
	}  
}

$sql_lv="select * from leave_assign where la_emp_id='".$emp_id."' and year(la_from_date)='".$year."' and month(la_from_date)='".$month."' and la_status='approve'";
$res_lv=mysqli_query($con, $sql_lv);

$c=0;
$lvDays=array();
while($row_lv=mysqli_fetch_object($res_lv))
{
	$sStartDate = gmdate("Y-m-d", strtotime($row_lv->la_from_date));  
	$sEndDate = gmdate("Y-m-d", strtotime($row_lv->la_to_date));  
	$sCurrentDate = $sStartDate;  
  // While the current date is less than the end date  
	while($sCurrentDate <= $sEndDate){  
		// Add a day to the current date  
		$sCurrentDate = date("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));  
		$lvDays[$c] = date("d",strtotime($sCurrentDate)); 
		$c++;
	}  
}

$sql_att="select * from employee_attendance where ea_emp_id='".$emp_id."' and year(ea_date)='".$year."' and month(ea_date)='".$month."' and ea_inTime != '' and ea_outTime != ''";
$res_att=mysqli_query($con, $sql_att);
$c=0;
$prsDays=array();
while($row_att=mysqli_fetch_object($res_att))
{
	$prsDays[$c] = date("d",strtotime($row_att->ea_date)); 
	$c++;
}
/*echo "<br/><br/>Present Days: ";
print_r($prsDays);*/

$sql_neg="select ea_negetiveTime from employee_attendance where ea_emp_id='".$emp_id."' and year(ea_date)='".$year."' and month(ea_date)='".$month."' and ea_inTime != '' and ea_outTime != ''";
$res_neg=mysqli_query($con, $sql_neg);

function HourMinuteToDecimal($hour_minute) 
{
    $t = explode(':', $hour_minute);
    return ($t[0] * 60 + $t[1]);
}
$sum_time=0;
while($row_neg=mysqli_fetch_object($res_neg))
{
	$sum_time=$sum_time+HourMinuteToDecimal($row_neg->ea_negetiveTime);
}
$negHour=floor($sum_time/60);

/*echo "<br/><br/>Working days except sunday & satarday: ";
print_r($workdays);

echo "<br/><br/>Holidays: ";
print_r($hDays);*/


$gr_working=array();
$j=0;
$h=0;
$l=0;
for($c=0;$c<count($workdays);$c++)
{
	if($workdays[$c]==$hDays[$h])
	{
		$h++;
	}
	else if($workdays[$c]==$lvDays[$l])
	{
		$l++;
	}
	else
	{
		if($workdays[$c]!='')
		{
			$gr_working[$j]=$workdays[$c];
			$j++;
		}
	}
}

/*echo "<br/><br/>Leave: ";
print_r($lvDays);

echo "<br/><br/>Gross working days: ";
print_r($gr_working);*/

$net_absent=array();
$p=0;
$a=0;
for($c=0;$c<count($gr_working);$c++)
{
	if($gr_working[$c]==$prsDays[$p])
	{
		$p++;
	}
	else
	{
		if($gr_working[$c]!='')
		{
			$net_absent[$a]=$gr_working[$c];
			$a++;
		}
	}
}

$tot_absent=count($net_absent);
$tot_present=count($prsDays);
$tot_leave=count($lvDays);

//echo "<br/><br/>Net Absent: ";
//print_r($net_absent);

//echo "<br/><br/>Number of Absent: ".$tot_absent;
//echo "<br/><br/>Actual Salary: ".$row_es->es_amount;

$per_day_amt=$row_es->es_amount/30;
$per_hour_amt=$per_day_amt/$row_es->jt_minWorkHour;

if($tot_absent>=15)
{
	$net_sal=round(($per_day_amt*$tot_present+$per_day_amt*$tot_leave)-($per_hour_amt*$negHour),0);	
}
else
{
	
	$net_sal=round(($row_es->es_amount-($per_day_amt*count($net_absent))-($per_hour_amt*$negHour)),0);
}
//echo "<br/><br/>Net Payable: ".$net_sal;
?>
 
<div class="x2-layout">
 <div class="formSection showSection">
<div class="tableWrapper">
<table>	<tbody>
<tr class="formSectionRow">
<td  style="width:278px">

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Month:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;">
<?php echo $months[$month-1].", ".$year; ?>
</div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Id:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php printf("%05d", $row_es->es_emp_id); ?> </div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Job Title:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo ucfirst($row_es->jt_title); ?></div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Department:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo ucfirst($row_es->dept_name); ?> </div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Actual Salary:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo ucfirst($row_es->es_amount); ?> </div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Number of working days:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo count($workdays); ?></div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Leave taken:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo count($lvDays)." day(s)"; ?> </div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Absent:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo count($net_absent)." day(s)"; ?></div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Total Present:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo $tot_present." day(s)"; ?>  </div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Total Negetive Hour(s):</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo $negHour; ?> </div></div>

<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
<label style="width:150px;"><strong>Net Salary Payable:</strong></label>
<div class="formInputBox" style="width:187px;height:auto;"><?php echo "Rs.".$net_sal; ?></div></div>
</td>
</tr>
</tbody></table> </div></div> </div>