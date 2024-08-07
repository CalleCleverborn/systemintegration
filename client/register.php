<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'phoneNumber' => $_POST['phoneNumber']  // Include phone number in the registration data
    ];
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents('https://server-he5tclb49-carl-cleverborns-projects.vercel.app/api/register', false, $context);
    if ($result === FALSE) {
        die('Error: Unable to register user.');
    }
    $response = json_decode($result, true);
    if (isset($response['message']) && $response['message'] === 'User registered') {
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
</head>

<body>
    <h1>Register</h1>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <label>Phone Number:</label> <!-- Add phone number field -->
        <input type="text" name="phoneNumber" required>
        <button type="submit">Register</button>
    </form>
    <a href="login.php">Already have an account? Login here</a>
</body>

</html>