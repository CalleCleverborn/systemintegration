<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$apiUrl = 'https://server-myjvvqres-carl-cleverborns-projects.vercel.app/api/products';
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
    <header>
        <h1>My Webshop</h1>
        <link rel="stylesheet" type="text/css" href="style.css">
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="create.php">Add New Product</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="container">
        <h2>Products</h2>
        <ul class="product-list">
            <?php foreach ($products as $product): ?>
            <li>
                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                <a href="edit.php?id=<?php echo $product['_id']; ?>">Edit</a>
                <a href="delete.php?id=<?php echo $product['_id']; ?>">Delete</a>
                <a href="checkout.php?id=<?php echo $product['_id']; ?>">Buy</a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <footer>
        <p>&copy; 2024 My Webshop</p>
    </footer>
</body>

</html>