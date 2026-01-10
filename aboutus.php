<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
$fullname = $isLoggedIn ? $_SESSION['fullname'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>About Us - Langata Road Primary & Junior School</title>
  <link rel="stylesheet" href="about.css" />
</head>
<body>
  <nav>
    <a href="home.php">Home</a>
    <a href="aboutus.php" class="active">About Us</a>
    <a href="events.php">Events</a>
    <a href="profile.php">Profile</a>
    <a href="contactus.php">Contact</a>
    <?php if($isLoggedIn): ?>
      <a href="logout.php" style="color:#ff7200;">Logout</a>
    <?php endif; ?>
  </nav>

  <section class="about-section">
    <div class="container">
      <header class="header">
        <h1>About Us</h1>
        <p class="intro">
          At <strong>Langata Road Primary &amp; Junior School</strong> we nurture curious,
          confident learners through quality teaching, strong values and community partnership.
        </p>
      </header>

      <div class="about-boxes">
        <article class="box">
          <h2>Achievements</h2>
          <p>
            Consistent top performers in county exams, regional sports champions,
            and winners in art & science fairs — proud results from hard work and teamwork.
          </p>
        </article>

        <article class="box">
          <h2>Vision</h2>
          <p>
            To be a leading primary school that develops responsible, creative and resilient
            learners prepared for the 21st century.
          </p>
        </article>

        <article class="box">
          <h2>Mission</h2>
          <p>
            Provide a safe, inclusive environment that encourages academic excellence,
            good character and community service.
          </p>
        </article>
      </div>
    </div>
  </section>
</body>
</html>
