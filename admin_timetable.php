<?php
session_start();

// Admin only access
if (!isset($_SESSION['admin']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

// Create timetable table if it doesn't exist
$create_timetable_table = "CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_grade VARCHAR(255) NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    teacher VARCHAR(255) NOT NULL,
    room VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_class_grade (class_grade),
    INDEX idx_day (day_of_week),
    INDEX idx_teacher (teacher),
    UNIQUE KEY unique_class_day_time (class_grade, day_of_week, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $create_timetable_table);

$message = "";

// Helper function
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Handle add timetable entry
if (isset($_POST['add_timetable'])) {
    $class_grade = mysqli_real_escape_string($conn, $_POST['class_grade']);
    $day = mysqli_real_escape_string($conn, $_POST['day_of_week']);
    $time = mysqli_real_escape_string($conn, $_POST['time_slot']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $teacher = mysqli_real_escape_string($conn, $_POST['teacher']);
    $room = mysqli_real_escape_string($conn, $_POST['room'] ?? '');

    $stmt = mysqli_prepare($conn, "INSERT INTO timetable (class_grade, day_of_week, time_slot, subject, teacher, room) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $class_grade, $day, $time, $subject, $teacher, $room);
    if (mysqli_stmt_execute($stmt)) {
        $message = "✅ Timetable entry added successfully!";
    } else {
        $message = "❌ Failed to add timetable entry: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM timetable WHERE id=$id")) {
        $message = "✅ Timetable entry deleted successfully!";
    } else {
        $message = "❌ Failed to delete: " . mysqli_error($conn);
    }
}

// Fetch all classes for dropdown
$classes = mysqli_query($conn, "SELECT DISTINCT grade FROM classes ORDER BY grade ASC");

// Fetch all teachers for dropdown
$teachers = mysqli_query($conn, "SELECT fullname FROM teachers ORDER BY fullname ASC");

// Get selected class filter
$selected_class = $_GET['class'] ?? '';
$where_clause = $selected_class ? "WHERE class_grade = '" . mysqli_real_escape_string($conn, $selected_class) . "'" : "";

// Fetch timetable entries
$timetable_query = mysqli_query($conn, "SELECT * FROM timetable $where_clause ORDER BY class_grade, FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), time_slot ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Timetable - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: url('https://images.pexels.com/photos/12960389/pexels-photo-12960389.jpeg') no-repeat center center fixed;
    background-size: cover;
    padding: 20px;
    color: #111827;
}

h2 {
    text-align:center;
    font-size:28px;
    font-weight:700;
    color:white;
    margin-bottom:20px;
}

.message {
    background: #d1fae5;
    color: #065f46;
    padding: 12px;
    border-radius: 8px;
    margin: 20px auto;
    max-width: 800px;
    text-align: center;
}

.message.error {
    background: #fee2e2;
    color: #991b1b;
}

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
    font-family:inherit;
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

.filter-form {
    max-width: 800px;
    margin: 20px auto;
}

table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
}

table th, table td {
    padding:12px;
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
}

a.back-btn {
    display: inline-block;
    background:#6b7280;
    color:#fff;
    padding:10px 20px;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    margin-bottom:20px;
}

a.back-btn:hover {
    background:#4b5563;
}

@media(max-width:700px){
    form { flex-direction:column; }
    table { font-size:12px; }
}
</style>
</head>
<body>

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<h2>Manage Class Timetables</h2>

<?php if($message): ?>
    <div class="message <?= strpos($message,'❌')!==false ? 'error' : '' ?>"><?= h($message) ?></div>
<?php endif; ?>

<!-- Filter by Class -->
<form method="GET" class="filter-form">
    <select name="class">
        <option value="">All Classes</option>
        <?php 
        mysqli_data_seek($classes, 0);
        while($class = mysqli_fetch_assoc($classes)): 
            $selected = $selected_class == $class['grade'] ? 'selected' : '';
        ?>
            <option value="<?= h($class['grade']) ?>" <?= $selected ?>><?= h($class['grade']) ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Filter</button>
</form>

<!-- Add Timetable Entry Form -->
<form method="POST">
    <select name="class_grade" required>
        <option value="">Select Class</option>
        <?php 
        mysqli_data_seek($classes, 0);
        while($class = mysqli_fetch_assoc($classes)): 
        ?>
            <option value="<?= h($class['grade']) ?>"><?= h($class['grade']) ?></option>
        <?php endwhile; ?>
    </select>
    <select name="day_of_week" required>
        <option value="">Select Day</option>
        <option value="Monday">Monday</option>
        <option value="Tuesday">Tuesday</option>
        <option value="Wednesday">Wednesday</option>
        <option value="Thursday">Thursday</option>
        <option value="Friday">Friday</option>
        <option value="Saturday">Saturday</option>
    </select>
    <input name="time_slot" placeholder="Time (e.g., 8:00 AM - 9:00 AM)" required>
    <input name="subject" placeholder="Subject" required>
    <select name="teacher" required>
        <option value="">Select Teacher</option>
        <?php 
        mysqli_data_seek($teachers, 0);
        while($teacher = mysqli_fetch_assoc($teachers)): 
        ?>
            <option value="<?= h($teacher['fullname']) ?>"><?= h($teacher['fullname']) ?></option>
        <?php endwhile; ?>
    </select>
    <input name="room" placeholder="Room (Optional)">
    <button name="add_timetable">Add Timetable Entry</button>
</form>

<!-- Timetable Table -->
<table>
    <tr>
        <th>Class</th>
        <th>Day</th>
        <th>Time</th>
        <th>Subject</th>
        <th>Teacher</th>
        <th>Room</th>
        <th>Action</th>
    </tr>
    <?php if(mysqli_num_rows($timetable_query) > 0): ?>
        <?php 
        mysqli_data_seek($timetable_query, 0);
        while($row = mysqli_fetch_assoc($timetable_query)): 
        ?>
            <tr>
                <td><?= h($row['class_grade']) ?></td>
                <td><?= h($row['day_of_week']) ?></td>
                <td><?= h($row['time_slot']) ?></td>
                <td><?= h($row['subject']) ?></td>
                <td><?= h($row['teacher']) ?></td>
                <td><?= h($row['room'] ?? '-') ?></td>
                <td>
                    <a href="?delete=<?= $row['id'] ?>&class=<?= urlencode($selected_class) ?>" 
                       onclick="return confirm('Delete this timetable entry?')" 
                       class="delete-btn">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align:center;padding:20px;color:#6b7280;">
                No timetable entries found. <?= $selected_class ? "for " . h($selected_class) : "" ?>
            </td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>

