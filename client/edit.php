<?php //edit.php ?>

<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'];
$apiUrl = "http://localhost:3000/api/products/$id";

// Hämta produktinformationen
$response = @file_get_contents($apiUrl);

if ($response === FALSE) {
    die('Error: Unable to retrieve product. Product may not exist or there was an error with the request.');
}

$product = json_decode($response, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image']
    ];
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'PUT',
            'content' => json_encode($data),
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);
    if ($result === FALSE) {
        die('Error: Unable to update product.');
    }
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Product</title>
</head>

<body>
    <h1>Edit Product</h1>
    <?php if ($product): ?>
        <form method="post">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            <label>Price:</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            <label>Image URL:</label>
            <input type="text" name="image" value="<?php echo htmlspecialchars($product['image']); ?>">
            <button type="submit">Update</button>
        </form>
    <?php else: ?>
        <p>Product not found</p>
    <?php endif; ?>
    <a href="index.php">Back to Products</a>
</body>

</html>