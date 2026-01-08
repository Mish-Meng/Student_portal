<?php
session_start();
// Admin only access
if (!isset($_SESSION['admin']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "schoolportal");
$message = "";

// Add Teacher
if (isset($_POST['add_teacher'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if ($fullname && $email && $phone && $subject && $username && $password) {
        $conn->query("INSERT INTO teachers (fullname,email,phone,subject,username,password) VALUES ('$fullname','$email','$phone','$subject','$username','$password')");
        $message = "✅ Teacher added successfully!";
    } else {
        $message = "❌ Please fill all fields!";
    }
}

// Edit Teacher
if (isset($_POST['edit_teacher'])) {
    $id = $_POST['id'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!empty($password)) {
        $conn->query("UPDATE teachers SET fullname='$fullname', email='$email', phone='$phone', subject='$subject', username='$username', password='$password' WHERE id=$id");
    } else {
        $conn->query("UPDATE teachers SET fullname='$fullname', email='$email', phone='$phone', subject='$subject', username='$username' WHERE id=$id");
    }
    $message = "✅ Teacher updated successfully!";
}

// Delete Teacher
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM teachers WHERE id=$id");
    $message = "✅ Teacher deleted successfully!";
}

// Search
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM teachers WHERE fullname LIKE '%$search%' OR email LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM teachers");
}

// Fetch for edit
$edit = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM teachers WHERE id=$edit_id");
    $edit = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Teachers</title>
<style>
body { font-family: 'Poppins', sans-serif; background: #fffaf5; padding: 30px; }
h2 { color: #ff7200; margin-bottom: 20px; }
form { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 400px; margin-bottom: 20px; }
input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 8px; }
button { background: #ff7200; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; width: 100%; }
table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
th { background: #ff7200; color: white; }
a.delete { color: red; text-decoration: none; }
a.edit { color: #007bff; text-decoration: none; }
.message { padding: 12px; margin-bottom: 20px; border-radius: 10px; }
.success { background: #d4edda; color: #155724; }
.error { background: #f8d7da; color: #721c24; }
.search-bar { margin-bottom: 20px; }
</style>
</head>
<body>
<div class="nav-btns">
  <button onclick="history.back()"> Back</button>
</div>
<h2>Manage Teachers</h2>

<?php if($message): ?>
<div class="message <?= strpos($message,'❌')!==false?'error':'success' ?>"><?= $message ?></div>
<?php endif; ?>

<!-- Search -->
<form method="GET" class="search-bar">
<input type="text" name="search" placeholder="Search by Name or Email" value="<?= htmlspecialchars($search) ?>">
<button type="submit">Search</button>
</form>

<!-- Add/Edit Form -->
<form method="POST">
<?php if($edit): ?>
<input type="hidden" name="id" value="<?= $edit['id'] ?>">
<input type="text" name="fullname" placeholder="Full Name" value="<?= $edit['fullname'] ?>" required>
<input type="email" name="email" placeholder="Email" value="<?= $edit['email'] ?>" required>
<input type="text" name="phone" placeholder="Phone" value="<?= $edit['phone'] ?>" required>
<input type="text" name="subject" placeholder="Subject" value="<?= $edit['subject'] ?>" required>
<input type="text" name="username" placeholder="Username" value="<?= $edit['username'] ?? '' ?>" required>
<input type="password" name="password" placeholder="Password (leave blank to keep current)">
<button type="submit" name="edit_teacher">Update Teacher</button>
<?php else: ?>
<input type="text" name="fullname" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="phone" placeholder="Phone" required>
<input type="text" name="subject" placeholder="Subject" required>
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit" name="add_teacher">Add Teacher</button>
<?php endif; ?>
</form>

<table>
<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Subject</th><th>Username</th><th>Action</th></tr>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= $row['fullname'] ?></td>
<td><?= $row['email'] ?></td>
<td><?= $row['phone'] ?></td>
<td><?= $row['subject'] ?></td>
<td><?= $row['username'] ?? '-' ?></td>
<td>
<a href="?edit=<?= $row['id'] ?>" class="edit">Edit</a> | 
<a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this teacher?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
