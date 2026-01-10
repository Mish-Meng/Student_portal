<?php
include 'connect.php';

$message = "";

// 1️⃣ Check if token exists in URL
if (!isset($_GET['token'])) {
    die("❌ Invalid verification link.");
}

$token = $_GET['token'];

// 2️⃣ Find user with this token
$stmt = $conn->prepare(
    "SELECT id, is_verified FROM users WHERE verification_token = ?"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

// 3️⃣ If token not found
if ($result->num_rows === 0) {
    die("❌ Invalid or expired verification link.");
}

$user = $result->fetch_assoc();

// 4️⃣ If already verified
if ($user['is_verified'] == 1) {
    echo "✅ Your account is already verified. <a href='login.php'>Login</a>";
    exit();
}

// 5️⃣ Verify the user
$update = $conn->prepare(
    "UPDATE users 
     SET is_verified = 1, verification_token = NULL 
     WHERE id = ?"
);
$update->bind_param("i", $user['id']);
$update->execute();

// 6️⃣ Confirmation message
if ($update->affected_rows > 0) {
    echo "✅ Email verified successfully! <a href='login.php'>Login now</a>";
} else {
    echo "❌ Verification failed. Please try again.";
}
?>
