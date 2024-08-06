<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$apiUrl = 'https://server-covye87re-carl-cleverborns-projects.vercel.app/api/products';
$options = [
    'http' => [
        'header' => "Authorization: Bearer " . $_SESSION['token'],
        'method' => 'GET'
    ]
];
$context = stream_context_create($options);
$response = @file_get_contents($apiUrl, false, $context);

if ($response === FALSE) {
    die('Error: Unable to retrieve products.');
}

$products = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error: Unable to decode JSON response.');
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Product List</title>
</head>

<body>
    <h1>Products</h1>
    <ul>
        <?php foreach ($products as $product): ?>
            <li>
                <?php echo htmlspecialchars($product['name']); ?> -
                <?php echo htmlspecialchars($product['price']); ?>
                <a href="edit.php?id=<?php echo $product['_id']; ?>">Edit</a>
                <a href="delete.php?id=<?php echo $product['_id']; ?>">Delete</a>
                <a href="checkout.php?id=<?php echo $product['_id']; ?>">Buy</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="create.php">Add New Product</a>
    <br>
    <form action="export.php" method="get">
        <input type="hidden" name="type" value="csv">
        <button type="submit">Export to CSV</button>
    </form>
    <form action="export.php" method="get">
        <input type="hidden" name="type" value="xml">
        <button type="submit">Export to XML</button>
    </form>
    <br>
    <a href="logout.php">Logout</a>
</body>

</html>