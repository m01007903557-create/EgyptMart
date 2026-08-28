<?php
session_start();
if (isset($_POST['image_path'])) {
    $_SESSION['temp_selected_image'] = $_POST['image_path'];
    echo json_encode(['success' => true]);
}
?>