<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'],
        'password' => $_POST['password']
    ];
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents('https://server-covye87re-carl-cleverborns-projects.vercel.app/api/login', false, $context);

    if ($result === FALSE) {
        $error = error_get_last();
        echo '<pre>';
        print_r($error);
        echo '</pre>';
        die('Error: Unable to login user.');
    }

    $response = json_decode($result, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error: Unable to decode JSON response. ' . json_last_error_msg());
    }

    if (isset($response['token'])) {
        $_SESSION['token'] = $response['token'];
        header('Location: index.php');
        exit();
    } else {
        $error = $response['message'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
    <a href="register.php">Don't have an account? Register here</a>
</body>

</html>