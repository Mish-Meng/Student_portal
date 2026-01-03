<?php
session_start();
include 'connect.php'; // Your DB connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Exams
$exam_sql = "SELECT * FROM exams ORDER BY exam_date DESC";
$exam_result = mysqli_query($conn, $exam_sql);

// Fetch Results for logged-in student
$result_sql = "SELECT * FROM results WHERE student_id = ? ORDER BY exam_name ASC";
$stmt = mysqli_prepare($conn, $result_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exams & Results</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --accent:#ff7200;
  --overlay:rgba(0,0,0,0.55);
  --surface:rgba(255,255,255,0.08);
  --border:rgba(255,255,255,0.2);
  --muted:rgba(229,231,235,0.8);
}

*{box-sizing:border-box;font-family:'Poppins','Segoe UI',sans-serif;}
body{
  margin:0;
  min-height:100vh;
  background:url('https://images.pexels.com/photos/8093039/pexels-photo-8093039.jpeg') no-repeat center/cover;
  color:#fff;
  padding:0;
  position:relative;
}
body::before{
  content:"";
  position:fixed;
  inset:0;
  background:var(--overlay);
  z-index:0;
}
.page{
  position:relative;
  z-index:1;
  padding:110px 16px 40px;
}
.container{
  max-width:1100px;
  margin:0 auto 26px;
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:18px;
  padding:28px 32px;
  backdrop-filter:blur(12px);
  box-shadow:0 14px 36px rgba(0,0,0,0.35);
}
.section-title{
  display:flex;
  align-items:center;
  gap:10px;
  margin:0 0 16px;
  font-size:24px;
  color:var(--accent);
}
table{
  width:100%;
  border-collapse:collapse;
  color:#fff;
  border:1px solid rgba(255,255,255,0.08);
}
th,td{
  padding:12px 14px;
  text-align:left;
  border-bottom:1px solid rgba(255,255,255,0.12);
}
th{
  background:rgba(255,114,0,0.9);
  color:#000;
  font-weight:600;
}
tr:nth-child(even){
  background:rgba(255,255,255,0.05);
}
tr:hover{
  background:rgba(255,255,255,0.12);
}
.btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:10px 18px;
  border-radius:10px;
  text-decoration:none;
  background:#fff;
  color:#4a2a7a;
  font-weight:600;
  border:1px solid transparent;
  transition:all .2s ease;
  cursor:pointer;
}
.btn:hover{
  background:transparent;
  color:#fff;
}
.download-btn{
  background:var(--accent);
  color:#000;
  border:1px solid var(--accent);
  box-shadow:0 6px 14px rgba(255,114,0,0.28);
}
.download-btn:hover{
  background:transparent;
  color:var(--accent);
  border-color:var(--accent);
}
.result-badge{
  display:inline-block;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid var(--border);
  background:rgba(0,0,0,0.25);
}

/* Navbar */
.navbar{
  position:fixed;
  top:0;
  left:0;
  width:100%;
  padding:16px 24px;
  z-index:10;
  background:rgba(0,0,0,0.35);
  backdrop-filter:blur(10px);
  border-bottom:1px solid rgba(255,255,255,0.18);
}
.nav-container{
  max-width:1200px;
  margin:0 auto;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.logo{
  font-size:22px;
  font-weight:600;
  color:#fff;
  text-decoration:none;
  letter-spacing:0.6px;
}
.nav-links{
  list-style:none;
  display:flex;
  gap:14px;
  margin:0;
  padding:0;
}
.btn-link{
  padding:8px 16px;
  border-radius:999px;
  text-decoration:none;
  color:#fff;
  border:1px solid transparent;
  transition:all .2s ease;
  font-weight:600;
}
.btn-link:hover{
  border-color:var(--accent);
  color:var(--accent);
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-container">
    <a href="home.php" class="logo">Exams & Results</a>
    <ul class="nav-links">
      <li><a href="home.php" class="btn-link">Home</a></li>
      <li><a href="javascript:history.back()" class="btn-link">Back</a></li>
      <li><a href="logout.php" class="btn-link" style="color:#ff7200;">Logout</a></li>
    </ul>
  </div>
</nav>

<div class="page">
  <div class="container">
    <!-- Exams Section -->
    <section>
    <h2 class="section-title">📘 Exams</h2>
    <table>
      <tr>
        <th>Exam Name</th>
        <th>Date</th>
        <th>Download</th>
      </tr>
      <?php while($exam = mysqli_fetch_assoc($exam_result)): ?>
      <tr>
        <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
        <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
        <td>
          <button
            type="button"
            class="btn download-btn"
            onclick="downloadExam('<?php echo htmlspecialchars($exam['file_path'], ENT_QUOTES); ?>')"
          >
            Download
          </button>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
    </section>

    <!-- Results Section -->
    <section style="margin-top:32px;">
    <h2 class="section-title">📊 Your Results</h2>
    <table>
      <tr>
        <th>Exam</th>
        <th>Score</th>
        <th>Result</th>
      </tr>
      <?php while($res = mysqli_fetch_assoc($result_result)): ?>
      <tr>
        <td><?php echo htmlspecialchars($res['exam_name']); ?></td>
        <td><?php echo htmlspecialchars($res['score']); ?>%</td>
        <td><span class="result-badge"><?php echo htmlspecialchars($res['result']); ?></span></td>
      </tr>
      <?php endwhile; ?>
    </table>
    </section>
  </div>
</div>

<script>
  function downloadExam(path){
    const link = document.createElement('a');
    link.href = path;
    link.setAttribute('download', '');
    document.body.appendChild(link);
    link.click();
    link.remove();
  }
</script>
</body>
</html>
