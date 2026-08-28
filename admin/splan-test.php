<?php
define('IN_ADMIN_PANEL', true);
define('IN_SITE', true);
session_start();
$_SESSION['admin_logged_in'] = true;

include "splan-badd.php";