<?php
include "../common.php";

mysqli_query($con, "delete from company_contact where comp_cnt_id='".$_GET['id']."'");
?>