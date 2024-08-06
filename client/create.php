<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image']
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents('http://localhost:3000/api/products', false, $context);

    if ($result === FALSE) {
        die('Error: Unable to create product.');
    }

    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Product</title>
</head>

<body>
    <h1>Create Product</h1>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" required>
        <label>Price:</label>
        <input type="number" name="price" required>
        <label>Image URL:</label>
        <input type="text" name="image">
        <button type="submit">Create</button>
    </form>
    <a href="index.php">Back to Products</a>
</body>

</html>