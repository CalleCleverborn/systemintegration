<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'phoneNumber' => $_POST['phoneNumber']
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
    $response = file_get_contents('https://server-myjvvqres-carl-cleverborns-projects.vercel.app/api/register', false, $context);

    if ($response === FALSE) {
        $error = 'Error: Unable to register user.';
    } else {
        $httpCode = explode(' ', $http_response_header[0])[1];

        if ($httpCode == 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['message']) && $responseData['message'] === 'User registered') {
                header('Location: login.php');
                exit();
            }
        } elseif ($httpCode == 400) {
            $responseData = json_decode($response, true);
            $error = $responseData['message'] ?? 'Invalid request';
        } else {
            $error = 'Error: Unable to register user. HTTP Status Code: ' . $httpCode;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <header>
        <h1>My Webshop - Register</h1>
    </header>

    <div class="container">
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <label>Phone Number:</label>
            <input type="text" name="phoneNumber" required>
            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Login here</a>
    </div>

    <footer>
        <p>&copy; 2024 My Webshop</p>
    </footer>
</body>

</html>