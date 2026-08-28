<?php
include "../common.php";

$usr=$_POST['usr'];

$sql_tma="select * from temp_msg_attachment where tma_usr_id='".$usr."' order by tma_id desc";
$res_tma=mysqli_query($con, $sql_tma);
if(mysqli_num_rows($res_tma)>0)
{
	?>
    <div style="margin-bottom:5px;">
<?php
	while($row_tma=mysqli_fetch_object($res_tma))
	{
		?>
        <div><span style="float:left;"><?php echo $row_tma->tma_file; ?></span><span style="padding-left:10px;vertical-align:middle"><a onclick="delAttachment('<?php echo $row_tma->tma_id; ?>','<?php echo $usr; ?>');"><img src="./images/del-attachment.png" /></a></span></div>
        <?php
	}
	?>
    </div>
    <?php
}
?>