<?php
session_start();
if (isset($_POST['page'])) {
    $_SESSION['redirect_after_login'] = $_POST['page'];
    echo "success";
} else {
    echo "error";
}
?>