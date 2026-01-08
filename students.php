<?php
session_start();

// Protect the page - Admin only
if (!isset($_SESSION['admin']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "schoolportal");

// Initialize message
$message = "";

// Handle Add Student
if (isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $class = $_POST['class'];
    $admission_no = $_POST['admission_no'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];

    if ($name && $class && $admission_no && $gender && $age) {
        $sql = "INSERT INTO students (fullname, class, adm_no, gender, age)
                VALUES ('$name', '$class', '$admission_no', '$gender', '$age')";
        $conn->query($sql);
        $message = "✅ Student added successfully!";
    } else {
        $message = "❌ Please fill all fields!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
    $message = "✅ Student deleted successfully!";
}

// Handle Edit
if (isset($_POST['edit_student'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $admission_no = $_POST['admission_no'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];

    $conn->query("UPDATE students SET fullname='$name', class='$class', adm_no='$admission_no', gender='$gender', age='$age' WHERE id=$id");
    $message = "✅ Student updated successfully!";
}

// Handle Search
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM students WHERE fullname LIKE '%$search%' OR adm_no LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM students");
}

// Fetch student for editing if edit id is set
$edit = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM students WHERE id=$edit_id");
    $edit = $edit_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Students</title>
<style>
body { font-family: 'Poppins', sans-serif; background: #fffaf5; padding: 30px; }
h2 { color: #ff7200; margin-bottom: 20px; }
form { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 400px; margin-bottom: 20px; }
input, select { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 8px; }
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

<h2>Manage Students</h2>

<?php if($message): ?>
<div class="message <?= strpos($message,'❌') !== false ? 'error' : 'success' ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<!-- Search Form -->
<form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search by Name or Admission No" value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<!-- Add/Edit Form -->
<form method="POST">
    <?php if($edit): ?>
        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <input type="text" name="name" placeholder="Full Name" value="<?= $edit['fullname'] ?>" required>
        <input type="text" name="class" placeholder="Class" value="<?= $edit['class'] ?>" required>
        <input type="text" name="admission_no" placeholder="Admission No" value="<?= $edit['adm_no'] ?>" required>
        <select name="gender" required>
            <option value="">--Select Gender--</option>
            <option value="Male" <?= $edit['gender']=='Male'?'selected':'' ?>>Male</option>
            <option value="Female" <?= $edit['gender']=='Female'?'selected':'' ?>>Female</option>
        </select>
        <input type="number" name="age" placeholder="Age" value="<?= $edit['age'] ?>" required>
        <button type="submit" name="edit_student">Update Student</button>
    <?php else: ?>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="class" placeholder="Class" required>
        <input type="text" name="admission_no" placeholder="Admission No" required>
        <select name="gender" required>
            <option value="">--Select Gender--</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <input type="number" name="age" placeholder="Age" required>
        <button type="submit" name="add_student">Add Student</button>
    <?php endif; ?>
</form>

<table>
<tr>
<th>ID</th>
<th>Full Name</th>
<th>Class</th>
<th>Admission No</th>
<th>Gender</th>
<th>Age</th>
<th>Action</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= $row['fullname'] ?></td>
<td><?= $row['class'] ?></td>
<td><?= $row['adm_no'] ?></td>
<td><?= $row['gender'] ?></td>
<td><?= $row['age'] ?></td>
<td>
    <a href="?edit=<?= $row['id'] ?>" class="edit">Edit</a> | 
    <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this student?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
