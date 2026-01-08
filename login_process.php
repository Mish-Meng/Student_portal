<?php
session_start();
include 'connect.php'; // Make sure connect.php defines $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data safely
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "<script>alert('⚠️ Please enter both username and password.'); window.location='login.php';</script>";
        exit();
    }

    // Check user in the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            // ✅ Verify hashed password
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['adm_no'] = $row['adm_no'] ?? ''; // If you have student admission number

                // Redirect to home page
                header("Location: home.php");
                exit();
            } else {
                echo "<script>alert('❌ Wrong password!'); window.location='login.php';</script>";
            }
        } else {
            echo "<script>alert('⚠️ User not found!'); window.location='login.php';</script>";
        }

        mysqli_stmt_close($stmt);
    } else {
        die("❌ Database error: " . mysqli_error($conn));
    }

    mysqli_close($conn);
} else {
    // If the form was not submitted, redirect
    header("Location: login.php");
    exit();
}
?>
