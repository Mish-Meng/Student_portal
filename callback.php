<?php
include 'connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$resultCode = $data['Body']['stkCallback']['ResultCode'];
$resultDesc = $data['Body']['stkCallback']['ResultDesc'];
$checkoutID = $data['Body']['stkCallback']['CheckoutRequestID'];

if ($resultCode == 0) {
    $conn->query("UPDATE payments SET status='PAID' WHERE status='PENDING' ORDER BY id DESC LIMIT 1");
} else {
    $conn->query("UPDATE payments SET status='FAILED' WHERE status='PENDING' ORDER BY id DESC LIMIT 1");
}
