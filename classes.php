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

// Add new class
if (isset($_POST['add_class'])) {
    $grade = $_POST['grade'];
    $teacher = $_POST['teacher'];
    $subjects = $_POST['subjects'];
    $time = $_POST['time'];

    $stmt = mysqli_prepare($conn, "INSERT INTO classes (grade, teacher, subjects, time) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $grade, $teacher, $subjects, $time);
    mysqli_stmt_execute($stmt);
}

// Delete class
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM classes WHERE id=$id");
}

// Fetch all classes
$result = mysqli_query($conn, "SELECT * FROM classes ORDER BY id ASC");
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

form input {
    padding:12px 15px;
    border-radius:8px;
    border:1px solid #d1d5db;
    font-size:14px;
    flex:1 1 200px;
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

<h2>Manage Classes</h2>

<!-- Add Class Form -->
<form method="POST">
    <input name="grade" placeholder="Grade" required>
    <input name="teacher" placeholder="Teacher" required>
    <input name="subjects" placeholder="Subjects (comma-separated)" required>
    <input name="time" placeholder="Time" required>
    <button name="add_class">Add Class</button>
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
            <a href="admin_timetable.php?class=<?= urlencode($row['grade']) ?>" style="background:#3b82f6; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:13px;">Timetable</a>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this class?')" class="delete-btn">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
