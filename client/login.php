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
    $result = file_get_contents('https://server-56o3wjrc6-carl-cleverborns-projects.vercel.app/api/login', false, $context);
    if ($result === FALSE) {
        die('Error: Unable to login user.');
    }
    $response = json_decode($result, true);
    if (isset($response['token'])) {
        $_SESSION['token'] = $response['token'];
        $_SESSION['user_id'] = $response['userId'];
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