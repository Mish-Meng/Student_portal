<?php
session_start();
include 'connect.php';

date_default_timezone_set('Africa/Nairobi');

/* ---------------- FORM DATA ---------------- */
$studentName = $_POST['studentName'] ?? '';
$admNo       = $_POST['admNo'] ?? '';
$class       = $_POST['class'] ?? '';
$feeType     = $_POST['feeType'] ?? '';
$amount      = (int)($_POST['amount'] ?? 0);
$email       = $_POST['email'] ?? '';
$method      = "M-Pesa";
$phone       = $_POST['phone'] ?? '';

/* ---------------- PHONE FORMAT FIX ---------------- */
$phone = preg_replace('/\D/', '', $phone); // remove non-digits
if (substr($phone, 0, 1) === '0') {
    $phone = '254' . substr($phone, 1);
}

/* ---------------- BASIC VALIDATION ---------------- */
if ($amount < 1 || strlen($phone) < 12) {
    die("Invalid phone number or amount");
}

/* ---------------- DARAJA SANDBOX CREDENTIALS ---------------- */
$consumerKey    = "h6STtTGUBAMM5xjcZ0XzYmGjRkuuk19TfVm2112NFbrfHE9A";
$consumerSecret = "lUUzp0drjiAffx3z7Rka3LJNj0xVEUYjA6rgbb8Uk7AzxDpfpveznIM5x6Jekf2q";
$shortcode      = "174379"; // sandbox shortcode
$passkey        = "PASTE_YOUR_SANDBOX_PASSKEY_HERE";
$callbackURL    = "https://yourdomain.com/callback.php"; // use ngrok for local testing

/* ---------------- ACCESS TOKEN ---------------- */
$credentials = base64_encode($consumerKey . ":" . $consumerSecret);
$token_url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

$curl = curl_init($token_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$token_response = curl_exec($curl);
curl_close($curl);

$token_data = json_decode($token_response);
if (!isset($token_data->access_token)) {
    die("Daraja Token Error: " . $token_response);
}

$access_token = $token_data->access_token;

/* ---------------- STK PASSWORD ---------------- */
$timestamp = date("YmdHis");
$password = base64_encode($shortcode . $passkey . $timestamp);

/* ---------------- STK PUSH ---------------- */
$stk_url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

$stk_data = [
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackURL,
    "AccountReference" => $admNo,
    "TransactionDesc" => "School Fees Payment"
];

$curl = curl_init($stk_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
]);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stk_data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$stk_response = curl_exec($curl);
curl_close($curl);

$stk_result = json_decode($stk_response);
if (!isset($stk_result->CheckoutRequestID)) {
    die("STK Push Error: " . $stk_response);
}

/* ---------------- SAVE PENDING PAYMENT ---------------- */
$stmt = $conn->prepare("
    INSERT INTO payments
    (student_name, adm_no, class, fee_type, amount, parent_email, method, phone, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')
");

$stmt->bind_param(
    "ssssdsss",
    $studentName,
    $admNo,
    $class,
    $feeType,
    $amount,
    $email,
    $method,
    $phone
);

$stmt->execute();
$stmt->close();

echo "<script>
alert('📲 M-Pesa prompt sent. Enter your PIN on phone.');
window.location='home.php';
</script>";
