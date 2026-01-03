<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: admin_login.php"); exit(); }
include 'connect.php';

if(isset($_POST['add_event'])){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];

    $stmt = mysqli_prepare($conn, "INSERT INTO events (title, description, date, time, location) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $title, $description, $date, $time, $location);
    mysqli_stmt_execute($stmt);
    header("Location: events.php"); exit();
}
?>

<h2>Add New Event</h2>
<form method="POST">
    <input name="title" placeholder="Event Title" required><br><br>
    <textarea name="description" placeholder="Event Description" required></textarea><br><br>
    <input type="date" name="date" required>
    <input type="text" name="time" placeholder="Time" required>
    <input type="text" name="location" placeholder="Location" required><br><br>
    <button name="add_event">Add Event</button>
</form>
