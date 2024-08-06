<?php //export.php ?>

<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$exportType = $_GET['type'];
$apiUrl = "http://localhost:3000/api/export/$exportType";
$response = @file_get_contents($apiUrl);

if ($response === FALSE) {
    die('Error: Unable to export data.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=products.' . $exportType);
header('Content-Transfer-Encoding: binary');
echo $response;
exit();
?>