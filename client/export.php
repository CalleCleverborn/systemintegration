<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$exportType = $_GET['type'];
$apiUrl = "https://server-56o3wjrc6-carl-cleverborns-projects.vercel.app/api/export/$exportType";

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