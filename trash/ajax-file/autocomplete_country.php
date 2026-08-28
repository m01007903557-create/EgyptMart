<?php
include "../common.php";
	$q=$_GET['q'];
	$my_data=mysql_real_escape_string($q);
	$sql="select * from country where cn_status = '1' and cn_name LIKE '$my_data%' order by cn_id asc";
	$result = mysqli_query($con, $sql);
	if($result)
	{
		while($row=mysqli_fetch_object($result))
		{
			echo '<img src="images/country_flag/'.$row->cn_flag.'"> '. ucfirst($row->cn_name)."|".$row->cn_id."|".$row->cn_ph."\n"; 
		}
	}
?>
<style>
#country_name{  
background-image:url('images/country_flag/<?php echo $row->cn_flag; ?>');   
background-position:right;   
background-repeat:no-repeat;   
padding-left:17px;
}
</style>