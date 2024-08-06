<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit();
}

$productId = $_GET['id'];
$productApiUrl = "http://localhost:3000/api/products/$productId";
$checkoutApiUrl = 'http://localhost:3000/api/checkout';

// Hämta produktinformationen
$response = @file_get_contents($productApiUrl);
if ($response === FALSE) {
    die('Error: Unable to retrieve product information.');
}
$product = json_decode($response, true);

// Skapa Stripe checkout session
$data = array('productId' => $productId);
$options = array(
    'http' => array(
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
    ),
);
$context = stream_context_create($options);
$result = @file_get_contents($checkoutApiUrl, false, $context);
if ($result === FALSE) {
    $error = error_get_last();
    die('Error: Unable to create checkout session. ' . $error['message']);
}
$response = json_decode($result, true);
$sessionId = $response['id'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body>
    <h1>Checkout</h1>
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
    <img src="<?php echo htmlspecialchars($product['image']); ?>"
        alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 200px;">
    <button id="checkout-button">Checkout</button>

    <script type="text/javascript">
    var stripe = Stripe(
        'pk_test_51PItI7Rxxg2rxu6vkw4GVJS5IOlzaBoifIk6h5pRdH9V5E2p7qFq1DDkxtc5TfXqFmARiwpb76fFFdhM3jxaIXgI00FxsZQSqW'
    ); // Replace with your Stripe publishable key

    document.getElementById('checkout-button').addEventListener('click', function() {
        stripe.redirectToCheckout({
            sessionId: '<?php echo $sessionId; ?>'
        }).then(function(result) {
            if (result.error) {
                alert(result.error.message);
            }
        });
    });
    </script>
</body>

</html>