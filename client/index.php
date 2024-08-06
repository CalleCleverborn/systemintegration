<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$apiUrl = 'http://localhost:3000/api/products';
$response = @file_get_contents($apiUrl);

if ($response === FALSE) {
    die('Error: Unable to retrieve products.');
}

$products = json_decode($response, true);
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