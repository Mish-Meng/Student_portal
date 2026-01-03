<?php
session_start();
include('db.php');

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['student_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['adm_no'] = $row['adm_no'];
            $_SESSION['class'] = $row['class'];
            header("Location: payment.php");
            exit();
        } else {
            $error = "❌ Wrong password!";
        }
    } else {
        $error = "⚠️ No account found with that email!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>School Portal</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="main">
        <div class="navbar">
            <div class="icon">
                <h2 class="logo">Portal</h2>
            </div>

            <div class="search">
                <input class="srch" type="search" name="" placeholder="Type To text">
                <a href="#"> <button class="btn">Search</button></a>
            </div>
        </div>

        <div class="content">
            <h1>School Payments & <br><span>Development</span> <br>Portal</h1>
            <p class="par">
                Welcome to our School Payments and Development Portal. This <br>
                platform is designed to make it easier for parents, guardians, and students to <br>
                manage school payments, track development projects, and stay updated with school progress.<br>
                Convenient, transparent, and secure – all in one place.
            </p>
            <button class="cn"><a href="signup.php">JOIN US</a></button>

            <div class="form">
                <h2>Login Here</h2>

                <form method="POST" action="">
                    <input type="email" name="email" placeholder="Enter Email Here" required>
                    <input type="password" name="password" placeholder="Enter Password Here" required>
                    <button class="btnn" type="submit">Login</button>
                </form>

                <p class="link">Don't have an account?<br>
                <a href="signup.php">Sign up</a> here</p>

                <p class="liw">Log in with</p>

                <div class="icon">
                    <a href="#"><ion-icon name="logo-facebook"></ion-icon></a>
                    <a href="#"><ion-icon name="logo-instagram"></ion-icon></a>
                    <a href="#"><ion-icon name="logo-google"></ion-icon></a>
                    <a href="#"><ion-icon name="logo-twitter"></ion-icon></a>
                </div>

                <?php if ($error): ?>
                    <p style="color: red; text-align:center;"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/ionicons@5.4.0/dist/ionicons.js"></script>
</body>
</html>
