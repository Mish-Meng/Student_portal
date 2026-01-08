<?php
session_start(); // MUST be first

// Admin only access
if (!isset($_SESSION['admin']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "schoolportal");
if ($conn->connect_error) die("Connection Failed: " . $conn->connect_error);

$message = "";

// Add new class
if (isset($_POST['add_class'])) {
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $teacher = mysqli_real_escape_string($conn, $_POST['teacher']);
    $subjects = mysqli_real_escape_string($conn, $_POST['subjects']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);

    $stmt = mysqli_prepare($conn, "INSERT INTO classes (grade, teacher, subjects, time) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $grade, $teacher, $subjects, $time);
    if (mysqli_stmt_execute($stmt)) {
        $message = "✅ Class added successfully!";
    } else {
        $message = "❌ Error adding class: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Edit class
if (isset($_POST['edit_class'])) {
    $id = (int)$_POST['id'];
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $teacher = mysqli_real_escape_string($conn, $_POST['teacher']);
    $subjects = mysqli_real_escape_string($conn, $_POST['subjects']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);

    $stmt = mysqli_prepare($conn, "UPDATE classes SET grade=?, teacher=?, subjects=?, time=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $grade, $teacher, $subjects, $time, $id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "✅ Class updated successfully!";
    } else {
        $message = "❌ Error updating class: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Delete class
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM classes WHERE id=$id")) {
        $message = "✅ Class deleted successfully!";
    } else {
        $message = "❌ Error deleting class: " . mysqli_error($conn);
    }
}

// Fetch all teachers for dropdown
$teachers_result = mysqli_query($conn, "SELECT id, fullname FROM teachers ORDER BY fullname ASC");

// Fetch all classes
$result = mysqli_query($conn, "SELECT * FROM classes ORDER BY id ASC");

// Fetch class for editing
$edit_class = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM classes WHERE id=$edit_id");
    $edit_class = mysqli_fetch_assoc($edit_result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Classes</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: url('https://images.pexels.com/photos/12960389/pexels-photo-12960389.jpeg') no-repeat center center fixed;
    background-size: cover; /* Makes the image cover the entire screen */
    padding: 20px;
    color: #111827;
}


/* Header */
h2 {
    text-align:center;
    font-size:28px;
    font-weight:700;
    color:white;
    margin-bottom:30px;
}

/* Form */
form {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
    justify-content:center;
    margin-bottom:30px;
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
}

form input, form select {
    padding:12px 15px;
    border-radius:8px;
    border:1px solid #d1d5db;
    font-size:14px;
    flex:1 1 200px;
    background:#fff;
}

form select {
    cursor:pointer;
}

form button {
    background:#ff7200;
    color:#fff;
    padding:12px 25px;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

form button:hover {
    background:#e66000;
}

/* Table */
table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
}

table th, table td {
    padding:15px 12px;
    text-align:left;
}

table th {
    background:#ff7200;
    color:#fff;
    font-weight:600;
}

table tr:nth-child(even) {
    background:#f9fafb;
}

table tr:hover {
    background:#f3f4f6;
}

a.delete-btn {
    background:#ef4444;
    color:#fff;
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
    transition:0.3s;
}

a.delete-btn:hover {
    background:#dc2626;
}

/* Responsive */
@media(max-width:700px){
    form { flex-direction:column; }
    table th, table td { font-size:13px; padding:10px; }
}
</style>
</head>
<body>

<div style="text-align:center; margin-bottom:20px;">
    <a href="dashboard.php" style="background:#6b7280; color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-block; margin-bottom:20px;">← Back to Dashboard</a>
</div>

<h2>Manage Classes</h2>

<?php if($message): ?>
    <div style="background: <?= strpos($message,'❌')!==false ? '#fee2e2' : '#d1fae5' ?>; color: <?= strpos($message,'❌')!==false ? '#991b1b' : '#065f46' ?>; padding: 12px; border-radius: 8px; margin: 0 auto 20px; max-width: 800px; text-align: center;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Class Form -->
<form method="POST">
    <?php if($edit_class): ?>
        <input type="hidden" name="id" value="<?= $edit_class['id'] ?>">
    <?php endif; ?>
    <input name="grade" placeholder="Grade (e.g., Grade 1, Form 2)" value="<?= $edit_class ? htmlspecialchars($edit_class['grade']) : '' ?>" required>
    <select name="teacher" required>
        <option value="">Select Teacher</option>
        <?php 
        mysqli_data_seek($teachers_result, 0);
        while($teacher = mysqli_fetch_assoc($teachers_result)): 
            $selected = ($edit_class && $edit_class['teacher'] == $teacher['fullname']) ? 'selected' : '';
        ?>
            <option value="<?= htmlspecialchars($teacher['fullname']) ?>" <?= $selected ?>>
                <?= htmlspecialchars($teacher['fullname']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <input name="subjects" placeholder="Subjects (comma-separated)" value="<?= $edit_class ? htmlspecialchars($edit_class['subjects']) : '' ?>" required>
    <input name="time" placeholder="Time (e.g., 8:00 AM - 10:00 AM)" value="<?= $edit_class ? htmlspecialchars($edit_class['time']) : '' ?>" required>
    <button name="<?= $edit_class ? 'edit_class' : 'add_class' ?>">
        <?= $edit_class ? 'Update Class' : 'Add Class' ?>
    </button>
    <?php if($edit_class): ?>
        <a href="classes.php" style="background:#6b7280; color:#fff; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-block;">Cancel</a>
    <?php endif; ?>
</form>

<!-- Classes Table -->
<table>
    <tr>
        <th>Grade</th>
        <th>Teacher</th>
        <th>Subjects</th>
        <th>Time</th>
        <th>Action</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= htmlspecialchars($row['grade']) ?></td>
        <td><?= htmlspecialchars($row['teacher']) ?></td>
        <td><?= htmlspecialchars($row['subjects']) ?></td>
        <td><?= htmlspecialchars($row['time']) ?></td>
        <td style="display:flex; gap:8px; align-items:center;">
            <a href="?edit=<?= $row['id'] ?>" style="background:#3b82f6; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:13px;">Edit</a>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this class?')" class="delete-btn">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
