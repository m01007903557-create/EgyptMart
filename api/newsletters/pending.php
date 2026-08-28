<?php
// api/newsletters/pending.php
require_once __DIR__ . '/../includes/auth.php';
authenticate();

global $con;

$sql = "SELECT * FROM newsletter_content 
        WHERE nc_channel = 'whatsapp' 
        AND (nc_sent = 0 OR nc_sent IS NULL)
        ORDER BY nc_updated_date ASC
        LIMIT 10";

$result = mysqli_query($con, $sql);
$newsletters = [];
while ($row = mysqli_fetch_assoc($result)) {
    $newsletters[] = $row;
}

echo json_encode([
    'status' => 'success',
    'newsletters' => $newsletters,
    'total' => count($newsletters)
]);
?>