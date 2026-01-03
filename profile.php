<?php
session_start();
include 'connect.php';

// ✅ Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch logged-in user details
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "<h2 style='color:red;text-align:center;'>User not found.</h2>";
    exit;
}

// Handle profile photo upload
if (isset($_POST['upload_photo']) && isset($_FILES['photo'])) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES["photo"]["name"]);
    $targetFile = $targetDir . time() . "_" . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg','jpeg','png','gif'];
    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            $update = mysqli_prepare($conn, "UPDATE users SET photo = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $targetFile, $_SESSION['user_id']);
            mysqli_stmt_execute($update);
            $user['photo'] = $targetFile;
        } else {
            echo "<script>alert('❌ Failed to upload photo.');</script>";
        }
    } else {
        echo "<script>alert('⚠️ Only JPG, PNG, GIF allowed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile - Langata Road Primary & Junior School</title>

<style>
/* ======== ORIGINAL PROFILE STYLES ======== */
body {
    font-family: "Poppins", Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: url('https://images.pexels.com/photos/3401403/pexels-photo-3401403.jpeg') no-repeat center center/cover;
    color: #333;
}

.container {
    width: 95%;
    max-width: 900px;
    margin: 40px auto;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(15px);
    border-radius: 16px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.2);
    padding: 30px;
}

h1 {
    text-align: center;
    color: #ff7200;
    letter-spacing: 1px;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    border-bottom: 2px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.profile-header img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px solid #f0faf4ff;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.profile-header img:hover { transform: scale(1.05); }

.profile-info h2 { margin: 0; color: #ff7200; }
.profile-info p { margin: 4px 0; font-size: 15px; }

.section { margin-bottom: 30px; }
.section h3 {
    background: linear-gradient(90deg, #ff7200, #ff9e3d);
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 18px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(255,255,255,0.8);
    border-radius: 8px;
    overflow: hidden;
}

th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 15px; }
th { background: #f4f4f4; color: #333; }
.balance { color: #ff7200; font-weight: bold; }

footer {
    text-align: center;
    padding: 15px;
    color: white;
    background: rgba(0,0,0,0.6);
    margin-top: 30px;
    border-radius: 0 0 16px 16px;
    font-size: 14px;
}

@media (max-width:600px) {
    .profile-header { flex-direction: column; text-align: center; }
    .profile-header img { width: 90px; height: 90px; }
}
</style>
</head>
<body>

<div class="container">
<h1>My Profile</h1>

<!-- PROFILE HEADER -->
<div class="profile-header">
    <form method="POST" enctype="multipart/form-data">
        <label for="photoInput">
            <img id="profilePic" src="<?php echo htmlspecialchars($user['photo'] ?? 'uploads/default.jpg'); ?>" alt="Profile Photo">
        </label>
        <input type="file" name="photo" id="photoInput" style="display:none" onchange="this.form.submit()">
        <input type="hidden" name="upload_photo" value="1">
    </form>
    <div class="profile-info">
        <h2 id="studentName"><?php echo htmlspecialchars($user['fullname']); ?></h2>
        <p>ID: <b><?php echo $user['id']; ?></b></p>
        <p>Username: <b><?php echo htmlspecialchars($user['username']); ?></b></p>
    </div>
</div>

<!-- PERSONAL DETAILS -->
<div class="section">
<h3>Personal Details</h3>
<table>
<tr><th>Full Name</th><td><?php echo htmlspecialchars($user['fullname']); ?></td></tr>
<tr><th>Username</th><td><?php echo htmlspecialchars($user['username']); ?></td></tr>
<!-- Add more fields here if needed -->
</table>
</div>

<!-- FEE BALANCE -->
<div class="section">
<h3>Fee Balance</h3>
<table>
<tr><th>Total Fees</th><td>KES <?php echo number_format($user['total_fees'] ?? 0); ?></td></tr>
<tr><th>Amount Paid</th><td>KES <?php echo number_format($user['amount_paid'] ?? 0); ?></td></tr>
<tr><th>Balance</th><td class="balance">KES <?php echo number_format($user['balance'] ?? 0); ?></td></tr>
</table>
</div>

<!-- ACADEMIC RECORDS -->
<div class="section">
<h3>Academic Records</h3>
<table>
<tr>
<th>Subject</th>
<th>Term 1</th>
<th>Term 2</th>
<th>Term 3</th>
</tr>
<tr><td>Mathematics</td><td>80%</td><td>85%</td><td>82%</td></tr>
<tr><td>English</td><td>78%</td><td>74%</td><td>80%</td></tr>
<tr><td>Science</td><td>88%</td><td>90%</td><td>87%</td></tr>
<tr><td>Social Studies</td><td>75%</td><td>72%</td><td>78%</td></tr>
</table>
</div>

<footer>
&copy; <?php echo date('Y'); ?> Langata Road Primary & Junior School | All Rights Reserved.
</footer>
</div>

<script>
const profilePic = document.getElementById('profilePic');
profilePic.addEventListener('click', () => {
    document.getElementById('photoInput').click();
});
</script>

</body>
</html>
