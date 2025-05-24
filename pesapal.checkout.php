<?php
require_once 'config.php';

$callback_url = 'https://sescotechsolutions.site/thankyou.php'; // replace with your actual callback URL

$consumer_key = DvXS8AdJsMiVQXIc6O0c8bAhwT8GXGWM;
$consumer_secret = JIislJTT+AcAiPWTF408vvPO6Tw=;
$is_live = PESAPAL_IS_LIVE;

$api_url = $is_live ? 'https://pay.pesapal.com/v3/api/PostPesapalDirectOrder' : 'https://sandbox.pesapal.com/v3/api/PostPesapalDirectOrder';

$order_tracking_url = $is_live ? 'https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus' : 'https://sandbox.pesapal.com/v3/api/Transactions/GetTransactionStatus';

$auth_url = $is_live ? 'https://pay.pesapal.com/v3/api/Auth/RequestToken' : 'https://sandbox.pesapal.com/v3/api/Auth/RequestToken';

// 1. Authenticate and get token
$headers = [
    'Accept: application/json',
    'Content-Type: application/json'
];

$curl = curl_init($auth_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret
]));

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
$token = $data['token'] ?? null;

if (!$token) {
    die('Authentication with PesaPal failed.');
}

// 2. Prepare order details (can be dynamic from your site)
$order_data = [
    'id' => uniqid(),
    'currency' => 'KES',
    'amount' => '1500.00',
    'description' => 'Order for T-Shirts',
    'callback_url' => $callback_url,
    'notification_id' => '',
    'billing_address' => [
        'email_address' => 'customer@example.com',
        'phone_number' => '0700111222',
        'country_code' => 'KE',
        'first_name' => 'John',
        'last_name' => 'Doe'
    ]
];

// 3. Post order
$headers[] = "Authorization: Bearer $token";

$curl = curl_init($api_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($order_data));

$response = curl_exec($curl);
curl_close($curl);

$order_response = json_decode($response, true);

if (isset($order_response['redirect_url'])) {
    header('Location: ' . $order_response['redirect_url']);
    exit;
} else {
    echo 'Error initiating payment: ' . $response;
}
?>
