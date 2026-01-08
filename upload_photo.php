<?php
session_start();
include 'connect.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle image upload
if (isset($_POST['upload_photo']) && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $filename = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;

    // Basic validation: check file type
    $allowed = ['jpg','jpeg','png','gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET photo = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $target_file, $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
        } else {
            echo "<script>alert('Failed to upload image.');</script>";
        }
    } else {
        echo "<script>alert('Invalid file type.');</script>";
    }
}

// Fetch user data
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Default image if none
$photo = !empty($user['photo']) ? $user['photo'] : 'image/student.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile</title>
<style>
/* ===== GENERAL ===== */
body {font-family:"Poppins",Arial,sans-serif;margin:0;padding:0;background:url('https://images.pexels.com/photos/3401403/pexels-photo-3401403.jpeg') no-repeat center/cover;color:#333;}
.container {width:95%; max-width:900px; margin:40px auto; background:rgba(255,255,255,0.15); backdrop-filter:blur(15px); border-radius:16px; box-shadow:0 6px 25px rgba(0,0,0,0.2); padding:30px;}
h1 {text-align:center;color:#ff7200;letter-spacing:1px;}
.profile-header {display:flex;align-items:center;gap:20px;border-bottom:2px solid #eee;padding-bottom:15px;margin-bottom:25px;}
.profile-header img {width:110px;height:110px;border-radius:50%;border:3px solid #2ecc71;object-fit:cover;cursor:pointer;transition:transform 0.3s ease;}
.profile-header img:hover {transform: scale(1.05);}
.profile-info h2 {margin:0;color:#005ea6;}
.profile-info p {margin:4px 0;font-size:15px;}
.section {margin-bottom:30px;}
.section h3 {background:linear-gradient(90deg,#ff7200,#ff9e3d);color:#fff;padding:10px 15px;border-radius:8px;margin-bottom:10px;font-size:18px;}
table {width:100%;border-collapse:collapse;background:rgba(255,255,255,0.8);border-radius:8px;overflow:hidden;}
th,td {padding:10px;border:1px solid #ddd;text-align:left;font-size:15px;}
th {background:#f4f4f4;color:#333;}
.balance {color:#ff7200;font-weight:bold;}
footer {text-align:center;padding:15px;color:white;background:rgba(0,0,0,0.6);margin-top:30px;border-radius:0 0 16px 16px;font-size:14px;}
@media(max-width:600px){.profile-header{flex-direction:column;text-align:center;}.profile-header img{width:90px;height:90px;}}
</style>
</head>
<body>
<div class="container">
<h1>My Profile</h1>

<!-- PROFILE HEADER -->
<div class="profile-header">
    <form method="post" enctype="multipart/form-data">
        <label for="profile_photo">
            <img id="profilePic" src="<?php echo htmlspecialchars($photo); ?>" alt="Profile Photo">
        </label>
        <input type="file" name="profile_photo" id="profile_photo" style="display:none;" onchange="this.form.submit()">
        <noscript><input type="submit" name="upload_photo" value="Upload"></noscript>
    </form>
    <div class="profile-info">
        <h2 id="studentName"><?php echo htmlspecialchars($user['fullname']); ?></h2>
        <p>Admission No: <b><?php echo htmlspecialchars($user['id']); ?></b></p>
        <p>Class: <b><?php echo htmlspecialchars($user['class']); ?></b></p>
    </div>
</div>

<!-- PERSONAL DETAILS -->
<div class="section">
<h3>Personal Details</h3>
<table>
<tr><th>Full Name</th><td><?php echo htmlspecialchars($user['fullname']); ?></td></tr>
<tr><th>Date of Birth</th><td><?php echo htmlspecialchars($user['dob']); ?></td></tr>
<tr><th>Gender</th><td><?php echo htmlspecialchars($user['gender']); ?></td></tr>
<tr><th>Parent/Guardian</th><td><?php echo htmlspecialchars($user['parent_name']); ?></td></tr>
<tr><th>Contact</th><td><?php echo htmlspecialchars($user['contact']); ?></td></tr>
</table>
</div>

<!-- FEE BALANCE -->
<div class="section">
<h3>Fee Balance</h3>
<table>
<tr><th>Total Fees</th><td>KES <?php echo number_format($user['total_fees']); ?></td></tr>
<tr><th>Amount Paid</th><td>KES <?php echo number_format($user['amount_paid']); ?></td></tr>
<tr><th>Balance</th><td class="balance">KES <?php echo number_format($user['balance']); ?></td></tr>
</table>
</div>

<footer>
&copy; <?php echo date('Y'); ?> Langata Road Primary & Junior School | All Rights Reserved.
</footer>
</div>

<script>
// Greeting
window.onload = () => {
    const name = document.getElementById('studentName').innerText;
    console.log(`Welcome back, ${name}!`);
};
</script>
</body>
</html>
