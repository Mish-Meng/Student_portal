<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: admin_login.php"); exit(); }
include 'connect.php';

$id = $_GET['id'];
$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id=$id"));

if(isset($_POST['update_event'])){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];

    $stmt = mysqli_prepare($conn, "UPDATE events SET title=?, description=?, date=?, time=?, location=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $date, $time, $location, $id);
    mysqli_stmt_execute($stmt);
    header("Location: events.php"); exit();
}
?>

<h2>Edit Event</h2>
<form method="POST">
    <input name="title" value="<?= htmlspecialchars($event['title']) ?>" required><br><br>
    <textarea name="description" required><?= htmlspecialchars($event['description']) ?></textarea><br><br>
    <input type="date" name="date" value="<?= $event['date'] ?>" required>
    <input type="text" name="time" value="<?= htmlspecialchars($event['time']) ?>" required>
    <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" required><br><br>
    <button name="update_event">Update Event</button>
</form>
