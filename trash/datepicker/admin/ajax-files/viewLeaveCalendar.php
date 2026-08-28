<?php
include "../common.php";
	
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


//$sql="select la_from_date,la_to_date,emp_firstName,emp_lastName,DATEDIFF(la_to_date,la_from_date)+1 as duration from leave_assign,employee,employee_job,department where emp_id=la_emp_id and emp_id=ej_emp_id and ej_dept_id=dept_id and ((la_from_date between '".$fromdate."' and '".$todate."') or (la_from_date between '".$fromdate."' and '".$todate."'))".$sql_emp.$sql_dept.$sql_stat_rej.$sql_stat_can.$sql_stat_pending.$sql_stat_sche.$sql_stat_taken;

//$sql="select la_id,la_from_date,la_to_date,emp_id,emp_firstName,emp_lastName,DATEDIFF(la_to_date,la_from_date)+1 as duration,la_status from leave_assign,employee,employee_job,department where emp_id=la_emp_id and emp_id=ej_emp_id and ej_dept_id=dept_id and ((la_from_date between '".$fromdate."' and '".$todate."') or (la_from_date between '".$fromdate."' and '".$todate."'))".$sql_emp.$sql_dept.$sql_leave_stat;

	$emp_id = $_POST['emp_id'];
	$year = $_POST['year'];
	$month = $_POST['month'];

$sql="select * from leave_assign,employee,leave_type where la_emp_id=emp_id and emp_id='".$emp_id."' and lt_id=la_lt_id and ((year(la_from_date)='".$year."' and month(la_from_date)='".$month."') or (year(la_to_date)='".$year."' and month(la_to_date)='".$month."'))";
//echo $sql;
$recObj=mysqli_query($con, $sql);

$c=0;
$aDays=array();
$lvType=array();
while($row=mysqli_fetch_object($recObj))
{
	
	
	$sStartDate = gmdate("Y-m-d", strtotime($row->la_from_date));  
	$sEndDate = gmdate("Y-m-d", strtotime($row->la_to_date));  
  
	/*if($c==0)
	{
		$aDays[$c] = $sStartDate;
	}*/
	
  // Set a 'temp' variable, sCurrentDate, with  
  // the start date - before beginning the loop  
	$sCurrentDate = $sStartDate;  
  
  // While the current date is less than the end date  
	while($sCurrentDate <= $sEndDate){  
		// Add a day to the current date  
		$sCurrentDate = date("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));  
	  
		// Add this new day to the aDays array  
		$aDays[$c] = date("d",strtotime($sCurrentDate)); 
		$lvType[$c]=$row->lt_color;
		$c++;
	}  
} 

sort($aDays);
?>
	<br clear="all"/>
    <?php
	$count=mysqli_num_rows($recObj);
		/*if($count >0)
		{*/
	?>
	
	<div class="admin-dtls">
        <ul>
            <li class="row-clr">
                <div class="eID" style="width:5px"></div>	
                <div class="eID" style="width:600px;">
				
<style type="text/css">
table {    width:550px;    border:0px solid #888;        border-collapse:collapse;	}
td {    width:27px;	font-family: Arial, sans-serif;    border-collapse:collapse;    border:1px solid #888;    text-align:center;	}
.calblock{	float:none;}
.onecal{	vertical-align:top;    background-color: #E9ECEF; }
.cal{	border-style:solid;	border-width:thin;	border-color:#E9ECEF;	float:left;	padding:3px;}
.days{    background-color: #F1F3F5; }
.hasday{    background-color: #FFFFDF;}
.noday{    background-color: #E9ECEF;}
th {	font-family: Arial, sans-serif;    border-collapse:collapse;    border:1px solid #888;    background-color: #E9ECEF;}
.sunday{ background-color:#F6F; }
</style>
 
 
<?php
/*	showCalendar( $monthToshow = null, $yearToShow = null, $firstDayOfWeek = 0 ) 
 	Shows a single month calendar. All parameters use Day, Month and Year encodings specified by PHP GETDATE. 
	monthToShow: month of the calendar (relative to year ... typically 1 - 12, but 
					can be larger to extend into following year(s))
	yearToShow: year of the calendar (4 digits)
	If either monthToShow or yearToShow are not supplied, then assumes current month in current year.
	firstDayOfWeek: specifies the first day of each displayed week in the calendar, (0 - 6)
					default to Sunday (0)*/
					
function showCalendar( $monthToShow = NULL  , $yearToShow = NULL , $firstDayOfWeek = 0 , $aDays, $lvType){
	if (($monthToShow === NULL) OR ($yearToShow === NULL)) {
		$today = GETDATE();
		$monthToShow = $today['mon'];
		$yearToShow = $today['year'];
	}
	else {
		$today = GETDATE(MKTIME(0,0,0,$monthToShow,1,$yearToShow));
	}
	// get first and last days of the month
    $firstDay = GETDATE(MKTIME(0,0,0,$monthToShow,1,$yearToShow));
    $lastDay  = GETDATE(MKTIME(0,0,0,$monthToShow+1,0,$yearToShow)); //trick! day = 0
 
    // Create a table with the necessary header information
    echo '<div class = cal><table align="center">';
    echo '  <tr><th colspan="7">'.$today['month']."&nbsp;&nbsp;&nbsp;".$today['year']."</th></tr>";
    echo '<tr class="days">';
	$dayText = array ( // this array is used to simplify the display of calendar headers
		// the entries just specify the text to show for the days in the header
		0 => 'Sun','Mon','Tue','Wed','Thur','Fri','Sat',
		// duplicate the first 6 entries to simplify display of calendar headers in a loop
		'Sun','Mon','Tue','Wed','Thur','Fri'
	);
	for ($i=0;$i<7;$i++){ // put 7 days in header, starting at appropriate day ($firstDayOfWeek)
		echo '<td>'.$dayText[$firstDayOfWeek + $i].'</td>';
    }
	echo '</tr><tr>';
    // Display the first calendar row with correct start of week
	if ( $firstDayOfWeek <= $firstDay['wday'] ) {
		$blanks = $firstDay['wday'] - $firstDayOfWeek; 
	} 
	else {
		$blanks = $firstDay['wday'] - $firstDayOfWeek + 7; 
	}
	for($i=1;$i<=$blanks;$i++){
        echo '<td class="noday"> </td>';
    }
    $actday = 0; // used to count and represent each day
	// Note: loop below starts using the residual value of $i from loop above 
    for( /* use value of $i resulting from last loop*/ ;$i<=7;$i++){
		if($i==1){
			echo '<td class="sunday">'.++$actday.'</td>';
		}
		else
		{
			if(in_array(($actday+1),$aDays)){
				$key = array_search($actday+1, $aDays);
				echo '<td bgcolor="#'.$lvType[$key].'">'.++$actday.'</td>';
			}
			else
			{
        		echo '<td class=hasday>'.++$actday.'</td>';
			}
		}
    }
    echo '</tr>';
 
    // Get how many complete weeks are in the actual month
    $fullWeeks = floor(($lastDay['mday']-$actday)/7);
    for ($i=0;$i<$fullWeeks;$i++){
		echo '<tr>';
        for ($j=0;$j<7;$j++){
			if($j==0)
			{
				 echo '<td class="sunday">'.++$actday.'</font></td>';
			}
			else
			{
				if(in_array(($actday+1),$aDays))
				{
					$key = array_search($actday+1, $aDays);
					echo '<td bgcolor="#'.$lvType[$key].'">'.++$actday.'</td>';
				}
				else
				{
					echo '<td class=hasday>'.++$actday.'</td>';
				}
			}
        }
        echo '</tr>';
    }
 
    //Now display the partial last week of the month (if there is one)
    if ($actday < $lastDay['mday']){
        echo '<tr>';
        $actday++;
        for ($i=0;$i<7;$i++){
            if ($actday <= $lastDay['mday']){
				if($i==0){
					echo '<td class="sunday">'.$actday++.'</td>';
				}
				else
				{
					if(in_array($actday,$aDays))
					{
						$key = array_search($actday, $aDays);
						echo '<td bgcolor="#'.$lvType[$key].'">'.$actday++.'</td>';
					}
					else
					{
						echo '<td class=hasday>'.$actday++.'</td>';
					}
//	                echo '<td class=hasday>'.$actday++.'</td>';
				}
            }
            else {
                echo '<td class="noday"> </td>';
            }
        }
        echo '</tr>';
    }
    echo '</table></div>';
}
 
/*	demo execution starts here
	This demo example uses showCalendar to package 12 months into 3 rows.
	It defaults to start in the current month.
*/

	$useDefault = TRUE;  // to always start in a particular month, set this false and see (A) below
	
	showCalendar( $month,$year, 0 /* start all weeks on Sunday (0) */,$aDays, $lvType);  
//	if ($useDefault) { // start at current month
//		$thisDay    = GETDATE(); 
//		$startMonth = $thisDay['mon'];
//		$startYear = $thisDay['year'];
//	}
//	else {
//		$startMonth = 1;   // (A) start at specified month ... January, 2011 in this case
//		$startYear = 2011; 
//	}
// 
//	for ($block = 1; $block<=3; $block++) { // there are 3 blocks 
//		echo '<div class = "calblock"><table><tr>';
//		for ($calcount=1; $calcount <=4; $calcount++) { // each block holds 4 months
//			echo '<td class = "onecal">';
//			showCalendar( $startMonth++,$startYear, 0 /* start all weeks on Monday (1) */);  
//			echo '</td>';
//		}
//		echo '</tr></table></div>';
//	}
?> 
                
                </div>
                <div class="eID" style="width:200px;">
                <?php
				$sql2="select distinct(lt_id),lt_name,lt_color from leave_assign,employee,leave_type where la_emp_id=emp_id and emp_id='".$emp_id."' and lt_id=la_lt_id and ((year(la_from_date)='".$year."' and month(la_from_date)='".$month."') or (year(la_to_date)='".$year."' and month(la_to_date)='".$month."'))";
				$recObj2=mysqli_query($con, $sql2);
				?>
				<table style="width:180px;">
                <?php	while($row2=mysqli_fetch_object($recObj2)){	?>
					<tr>
				<td style="width:20px;" bgcolor="#<?php echo $row2->lt_color; ?>"></td><td><?php	echo ucfirst($row2->lt_name);	?></td>
                </tr>
				<?php	}	?>
                </table>
                </div>
                <div class="clr"></div>
            </li>
            
        </ul>   							
	</div> <!-- end admin-dtls -->
	<?php /*$j++; } }
		else
		{*/
	?>
	<!--<div class="admin-dtls" style="text-align:center; color:red; border-bottom:1px solid #767676; padding-bottom:5px; font-weight:bold; font-size:13px;">
<div class="clr"></div>No Records.</div>-->
	<?php /*}*/ ?>
						 <!-- end pagicon -->