<?php
include "../common.php";

$year=$_POST['y'];

$firstDayOfYear = mktime(0, 0, 0, 1, 1, $year);
//echo "<br/>".date("l d-m-Y",$firstDayOfYear)."<br/>";
$nextMonday = strtotime('monday', $firstDayOfYear);
//echo "<br/>".date("d-m-Y",$nextMonday)."<br/>";
$nextFriday     = strtotime('friday', $nextMonday);

$val='<option value="">- Select - </option>';
while (date('Y', $nextMonday) == $year) {
    $val.='<option value="'.date("Y-m-d", $nextMonday).'">'.date("d-M-Y", $nextMonday).' - '.date("d-M-Y", $nextFriday).'</option>';

    $nextMonday = strtotime('+1 week', $nextMonday);
    $nextFriday = strtotime('+1 week', $nextFriday);
}
echo $val;
?>