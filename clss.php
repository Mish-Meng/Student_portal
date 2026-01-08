<?php
include 'connect.php'; // Database connection
$result = mysqli_query($conn, "SELECT * FROM classes ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Classes</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family: 'Poppins', sans-serif; padding-top:60px; }
.navbar {
  background: rgba(55,65,81,0.85);
  padding:14px 25px;
  position:fixed; top:0; width:100%; z-index:1000;
  backdrop-filter: blur(6px); box-shadow:0 4px 12px rgba(0,0,0,0.15);
}
.nav-container { max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
.logo { font-size:22px; font-weight:600; color:#fff; text-decoration:none; }
.nav-links { list-style:none; display:flex; gap:20px; margin:0; padding:0; }
.btn-link { background:#ff7200; padding:8px 18px; border-radius:6px; color:#fff; text-decoration:none; font-weight:500; font-size:14px; transition:0.3s; }
.btn-link:hover { background:transparent; }
.class-section { padding:20px; background:url("https://images.pexels.com/photos/5905448/pexels-photo-5905448.jpeg") center/cover no-repeat fixed; }
.class-header { text-align:center; margin-bottom:50px; }
.class-header h2 { font-size:36px; font-weight:700; color:#ff7200; margin-bottom:12px; }
.class-header .subtitle { font-size:20px; color:#ff7200; max-width:650px; margin:0 auto; }
.class-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:30px; max-width:1100px; margin:0 auto; }
.class-card { background:rgba(255,255,255,0.85); padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:0.3s; }
.class-card:hover { transform:translateY(-6px); box-shadow:0 12px 28px rgba(0,0,0,0.12); }
.class-card .card-header { background:#ff7200; color:#fff; padding:18px; text-align:center; }
.class-card .card-header h3 { margin:0; font-size:22px; font-weight:600; }
.class-card .card-body { padding:20px 0; text-align:left; }
.class-card .card-body p { font-size:15px; margin:10px 0; color:#374151; }
.class-card .card-body .label { font-weight:600; color:#111827; }
</style>
</head>
<body>

<nav class="navbar">
  <div class="nav-container">
    <a href="index.php" class="logo">Our Classes</a>
    <ul class="nav-links">
      <li><a href="home.php" class="btn-link">Home</a></li>
      <li><a href="javascript:history.back()" class="btn-link">Back</a></li>
    </ul>
  </div>
</nav>

<section class="class-section">
  <div class="class-header">
    <h2>Our Classes</h2>
    <p class="subtitle">Well-structured classes with dedicated teachers to ensure quality education.</p>
  </div>

  <div class="class-grid">
    <?php while($row = mysqli_fetch_assoc($result)): ?>
      <div class="class-card">
        <div class="card-header"><h3><?= htmlspecialchars($row['grade']) ?></h3></div>
        <div class="card-body">
          <p><span class="label">👨‍🏫 Teacher:</span> <?= htmlspecialchars($row['teacher']) ?></p>
          <p><span class="label">📘 Subjects:</span> <?= htmlspecialchars($row['subjects']) ?></p>
          <p><span class="label">⏰ Time:</span> <?= htmlspecialchars($row['time']) ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>

</body>
</html>
