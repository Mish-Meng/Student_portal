<?php
$consumerKey = "ubH9aydLnUdtG55fF1Fo4eb2CfNT2Ra3Fk3a9BZPM7hcGVsi";
$consumerSecret = "HnYAv3rzcqD9KQ1wykbjKij05t0Nb7JsCeR9K0SFCE80yhJzcEgGZ6vAuZWMCPa5";

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
