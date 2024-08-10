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
            'ignore_errors' => true,
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents('https://server-myjvvqres-carl-cleverborns-projects.vercel.app/api/login', false, $context);

    if ($response === FALSE) {
        $error = 'Error: Unable to login user.';
    } else {
        $httpCode = explode(' ', $http_response_header[0])[1];

        if ($httpCode == 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['token'])) {
                $_SESSION['token'] = $responseData['token'];
                $_SESSION['user_id'] = $responseData['userId'];
                header('Location: index.php');
                exit();
            }
        } elseif ($httpCode == 400) {
            $responseData = json_decode($response, true);
            $error = $responseData['message'] ?? 'Invalid request';
        } else {
            $error = 'Error: Unable to login user. HTTP Status Code: ' . $httpCode;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <header>
        <h1>My Webshop - Login</h1>
    </header>

    <div class="container">
        <h2>Login</h2>
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
    </div>

    <footer>
        <p>&copy; 2024 My Webshop</p>
    </footer>
</body>

</html>