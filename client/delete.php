<?php //delete.php ?>

<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'];
$apiUrl = "http://localhost:3000/api/products/$id";

$options = [
    'http' => [
        'header' => "Authorization: Bearer " . $_SESSION['token'] . "\r\n",
        'method' => 'DELETE',
    ],
];
$context = stream_context_create($options);
$response = @file_get_contents($apiUrl, false, $context);

if ($response === FALSE) {
    die('Error: Unable to delete product.');
}

header('Location: index.php');
exit();
?>