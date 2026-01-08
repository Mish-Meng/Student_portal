<?php
session_start();

// 🔒 Only admin access
if (!isset($_SESSION['admin']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "schoolportal");
if ($conn->connect_error) die("Connection Failed: " . $conn->connect_error);

// Add new event
if (isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];  // Ensure your table column is 'date'
    $time = $_POST['time'];  // Ensure your table column is 'time'
    $location = $_POST['location'];

    $stmt = $conn->prepare("INSERT INTO events (title, description, date, time, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $description, $date, $time, $location);
    $stmt->execute();
    $stmt->close();
}

// Delete event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM events WHERE id=$id");
}

// Fetch events
$result = $conn->query("SELECT * FROM events ORDER BY date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Events - School Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family:'Poppins',sans-serif; padding:20px; background:#f0f2f5; color:#111827; }
h2 { text-align:center; color:#ff7200; margin-bottom:20px; }
form { display:flex; flex-wrap:wrap; gap:10px; background:#fff; padding:20px; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.08); margin-bottom:30px; }
form input, form textarea { flex:1 1 200px; padding:12px; border-radius:8px; border:1px solid #d1d5db; font-size:14px; }
form button { background:#ff7200; color:#fff; padding:12px 25px; border:none; border-radius:8px; font-weight:600; cursor:pointer; transition:0.3s; }
form button:hover { background:#e66000; }

table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08); }
table th, table td { padding:12px 10px; text-align:left; }
table th { background:#ff7200; color:#fff; }
table tr:nth-child(even) { background:#f9fafb; }
table tr:hover { background:#f3f4f6; }
a.delete-btn { background:#ef4444; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:13px; }
a.delete-btn:hover { background:#dc2626; }
</style>
</head>
<body>

<h2>Manage Events (Admin Only)</h2>

<!-- Add Event Form -->
<form method="POST">
    <input type="text" name="title" placeholder="Event Title" required>
    <textarea name="description" placeholder="Event Description" required></textarea>
    <input type="date" name="date" required>
    <input type="time" name="time" required>
    <input type="text" name="location" placeholder="Location" required>
    <button name="add_event">Add Event</button>
</form>

<!-- Events Table -->
<table border="1">
<tr>
    <th>Title</th>
    <th>Description</th>
    <th>Date</th>
    <th>Time</th>
    <th>Location</th>
    <th>Action</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td><?= htmlspecialchars($row['description']) ?></td>
    <td><?= htmlspecialchars($row['date']) ?></td>
    <td><?= htmlspecialchars($row['time']) ?></td>
    <td><?= htmlspecialchars($row['location']) ?></td>
    <td><a class="delete-btn" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this event?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
