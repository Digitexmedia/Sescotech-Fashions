<?php
session_start();
require_once '../config/config.php';

// Get checkout form data
$amount = $_SESSION['checkout_amount'];
$desc = $_SESSION['checkout_description'];
$first_name = $_SESSION['customer_name'];
$email = $_SESSION['customer_email'];
$phone_number = $_SESSION['customer_phone'];

$callback_url = 'https://yourdomain.com/pesapal/ipn_listener.php'; // Replace with your live domain

$amount = number_format($amount, 2);

// Construct XML for PesaPal
$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<PesapalDirectOrderInfo 
xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
Amount=\"$amount\" 
Description=\"$desc\" 
Type=\"MERCHANT\" 
Reference=\"".uniqid()."\" 
FirstName=\"$first_name\" 
LastName=\"\" 
Email=\"$email\" 
PhoneNumber=\"$phone_number\" 
xmlns=\"http://www.pesapal.com\" />";

$consumer_key = PESAPAL_CONSUMER_KEY;
$consumer_secret = PESAPAL_CONSUMER_SECRET;

$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
$consumer = new OAuthConsumer($consumer_key, $consumer_secret);

// Use sandbox or live API endpoint
$iframe_src = 'https://www.pesapal.com/API/PostPesapalDirectOrderV4'; // Use sandbox endpoint for testing

$token = $params = null;

// Encode post XML
$post_xml = htmlentities($post_xml);

// Create OAuth Request
$request = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframe_src, $params);
$request->set_parameter("oauth_callback", $callback_url);
$request->set_parameter("pesapal_request_data", $post_xml);
$request->sign_request($signature_method, $consumer, $token);

// Load iframe
?>

<!DOCTYPE html>
<html>
<head>
  <title>Pay with PesaPal</title>
</head>
<body>
  <h2>Redirecting to PesaPal...</h2>
  <iframe src="<?php echo $request; ?>" width="100%" height="700px" scrolling="no" frameBorder="0">
    <p>Browser unable to load iFrame</p>
  </iframe>
</body>
</html>
