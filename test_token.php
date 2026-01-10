<?php
$consumerKey = "PASTE_YOUR_SANDBOX_CONSUMER_KEY";
$consumerSecret = "PASTE_YOUR_SANDBOX_CONSUMER_SECRET";

$credentials = base64_encode($consumerKey . ":" . $consumerSecret);
$url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);

if ($response === false) {
    die("cURL Error: " . curl_error($curl));
}

curl_close($curl);
echo $response;
