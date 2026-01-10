<?php 
session_start();
include 'connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

// Photo upload
if (isset($_POST['upload_photo']) && isset($_FILES['photo'])) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES["photo"]["name"]);
    $targetFile = $targetDir . time() . "_" . $fileName;
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif'];
    if (in_array($ext,$allowed)){
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)){
            $update = mysqli_prepare($conn, "UPDATE users SET photo=? WHERE id=?");
            mysqli_stmt_bind_param($update,"si",$targetFile,$_SESSION['user_id']);
            mysqli_stmt_execute($update);
            $user['photo']=$targetFile;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Profile | Langata Road Primary & Junior School</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ===== PROFESSIONAL STYLE ===== */
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:#f0f2f5;color:#333;}
header{display:flex;justify-content:space-between;align-items:center;padding:15px 30px;background:#1c6aa6;color:#fff;box-shadow:0 3px 6px rgba(0,0,0,0.2);}
header h1{font-weight:600;font-size:20px;}
header a{color:#fff;text-decoration:none;background:#ff7200;padding:8px 15px;border-radius:5px;transition:.3s;}
header a:hover{background:#e06600;}
.container{max-width:1100px;margin:30px auto;padding:0 15px;display:grid;grid-template-columns:1fr 2fr;gap:25px;}
.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 5px 20px rgba(0,0,0,0.1);transition:.3s;}
.card:hover{box-shadow:0 8px 25px rgba(0,0,0,0.15);}
.profile-photo{text-align:center;}
.profile-photo img{width:150px;height:150px;border-radius:50%;border:4px solid #1c6aa6;object-fit:cover;cursor:pointer;transition:transform .3s;}
.profile-photo img:hover{transform:scale(1.05);}
.profile-photo button{margin-top:10px;padding:8px 15px;border:none;background:#ff7200;color:#fff;border-radius:5px;cursor:pointer;transition:.3s;}
.profile-photo button:hover{background:#e06600;}
h2,h3{color:#1c6aa6;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:10px;text-align:left;border-bottom:1px solid #eee;font-size:14px;}
th{background:#f7f9fc;}
.balance{color:#ff7200;font-weight:600;}
@media(max-width:900px){.container{grid-template-columns:1fr;}}
</style>
</head>
<body>

<header>
<h1>Langata Road Primary & Junior School</h1>
<div> 
<a href="Home.php">Home</a>
<a href="logout.php">Logout</a>
</div>
</header>

<div class="container">

<!-- LEFT: PHOTO & BASIC INFO -->
<div class="card profile-photo">
    <form method="POST" enctype="multipart/form-data" id="photoForm">
        <label for="photoInput">
            <img id="profilePic" src="<?= htmlspecialchars($user['photo'] ?? 'uploads/default.jpg') ?>" alt="Profile Photo">
        </label>
        <input type="file" name="photo" id="photoInput" style="display:none">
        <input type="hidden" name="upload_photo" value="1">
        <br>
        <button type="button" onclick="photoInput.click()">Change Photo</button>
        <button type="submit">Upload Photo</button>
    </form>
    <h2><?= htmlspecialchars($user['fullname']) ?></h2>
    <p>ID: <?= $user['id'] ?></p>
    <p>Username: <?= htmlspecialchars($user['username']) ?></p>
</div>

<!-- RIGHT: DETAILS -->
<div class="card">
    <h3>Personal Details</h3>
    <table>
        <tr><th>Full Name</th><td><?= htmlspecialchars($user['fullname']) ?></td></tr>
        <tr><th>Username</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($user['email'] ?? '-') ?></td></tr>
        <tr><th>Phone</th><td><?= htmlspecialchars($user['phone'] ?? '-') ?></td></tr>
    </table>
</div>

<div class="card">
    <h3>Fee Balance</h3>
    <table>
        <tr><th>Total Fees</th><td>KES <?= number_format($user['total_fees'] ?? 0) ?></td></tr>
        <tr><th>Amount Paid</th><td>KES <?= number_format($user['amount_paid'] ?? 0) ?></td></tr>
        <tr><th>Balance</th><td class="balance">KES <?= number_format($user['balance'] ?? 0) ?></td></tr>
    </table>
</div>

<div class="card">
    <h3>Academic Records</h3>
    <table>
        <tr><th>Subject</th><th>Term 1</th><th>Term 2</th><th>Term 3</th></tr>
        <tr><td>Mathematics</td><td>80%</td><td>85%</td><td>82%</td></tr>
        <tr><td>English</td><td>78%</td><td>74%</td><td>80%</td></tr>
        <tr><td>Science</td><td>88%</td><td>90%</td><td>87%</td></tr>
        <tr><td>Social Studies</td><td>75%</td><td>72%</td><td>78%</td></tr>
    </table>
</div>

</div>

<script>
// Profile photo preview
const profilePic = document.getElementById('profilePic');
const photoInput = document.getElementById('photoInput');

profilePic.addEventListener('click', ()=>photoInput.click());
photoInput.addEventListener('change',function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){ profilePic.src=e.target.result; }
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
