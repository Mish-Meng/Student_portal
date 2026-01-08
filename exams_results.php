<?php
session_start();
include 'connect.php'; // Your DB connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student_id - try multiple methods to find the correct student
$student_id = null;

// Method 1: If adm_no is in session, find student by admission number
if (isset($_SESSION['adm_no']) && !empty($_SESSION['adm_no'])) {
    $adm_no = mysqli_real_escape_string($conn, $_SESSION['adm_no']);
    $student_query = mysqli_query($conn, "SELECT id FROM students WHERE adm_no = '$adm_no'");
    if ($student_row = mysqli_fetch_assoc($student_query)) {
        $student_id = $student_row['id'];
    }
}

// Method 2: If not found, try to match user_id with student id directly
if (!$student_id) {
    $student_query = mysqli_query($conn, "SELECT id FROM students WHERE id = $user_id");
    if ($student_row = mysqli_fetch_assoc($student_query)) {
        $student_id = $student_row['id'];
    }
}

// Method 3: If still not found, try matching by username/fullname
if (!$student_id && isset($_SESSION['fullname'])) {
    $fullname = mysqli_real_escape_string($conn, $_SESSION['fullname']);
    $student_query = mysqli_query($conn, "SELECT id FROM students WHERE fullname = '$fullname' LIMIT 1");
    if ($student_row = mysqli_fetch_assoc($student_query)) {
        $student_id = $student_row['id'];
    }
}

// Fetch Exams
$exam_sql = "SELECT * FROM exams ORDER BY exam_date DESC";
$exam_result = mysqli_query($conn, $exam_sql);

// Fetch Results for logged-in student
if ($student_id) {
    $result_sql = "SELECT * FROM results WHERE student_id = ? ORDER BY exam_name ASC";
    $stmt = mysqli_prepare($conn, $result_sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result_result = mysqli_stmt_get_result($stmt);
    
    // Calculate totals
    mysqli_data_seek($result_result, 0); // Reset pointer
    $total_marks = 0;
    $total_exams = 0;
    $results_array = [];
    while ($res = mysqli_fetch_assoc($result_result)) {
        $total_marks += (int)$res['score'];
        $total_exams++;
        $results_array[] = $res;
    }
    $average_score = $total_exams > 0 ? round($total_marks / $total_exams, 2) : 0;
    
    // Reset pointer again for display
    $result_result = mysqli_query($conn, "SELECT * FROM results WHERE student_id = $student_id ORDER BY exam_name ASC");
} else {
    $result_result = null;
    $total_marks = 0;
    $total_exams = 0;
    $average_score = 0;
    $results_array = [];
}
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

    <!-- Results Summary Section -->
    <?php if($total_exams > 0): ?>
    <section style="margin-top:32px;">
      <h2 class="section-title">📊 Your Results Summary</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: rgba(255,114,0,0.15); border: 2px solid var(--accent); border-radius: 12px; padding: 20px; text-align: center;">
          <div style="font-size: 14px; color: var(--muted); margin-bottom: 8px;">Total Exams</div>
          <div style="font-size: 32px; font-weight: 700; color: var(--accent);"><?= $total_exams ?></div>
        </div>
        <div style="background: rgba(255,114,0,0.15); border: 2px solid var(--accent); border-radius: 12px; padding: 20px; text-align: center;">
          <div style="font-size: 14px; color: var(--muted); margin-bottom: 8px;">Total Marks</div>
          <div style="font-size: 32px; font-weight: 700; color: var(--accent);"><?= $total_marks ?>%</div>
        </div>
        <div style="background: rgba(255,114,0,0.15); border: 2px solid var(--accent); border-radius: 12px; padding: 20px; text-align: center;">
          <div style="font-size: 14px; color: var(--muted); margin-bottom: 8px;">Average Score</div>
          <div style="font-size: 32px; font-weight: 700; color: var(--accent);"><?= $average_score ?>%</div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Results Section -->
    <section style="margin-top:32px;">
    <h2 class="section-title">📋 Detailed Results</h2>
    <?php if($result_result && mysqli_num_rows($result_result) > 0): ?>
      <table>
        <tr>
          <th>Exam Name</th>
          <th>Score (%)</th>
          <th>Grade/Result</th>
        </tr>
        <?php while($res = mysqli_fetch_assoc($result_result)): ?>
        <tr>
          <td><?php echo htmlspecialchars($res['exam_name']); ?></td>
          <td style="font-weight: 600; font-size: 16px;"><?php echo htmlspecialchars($res['score']); ?>%</td>
          <td><span class="result-badge" style="font-weight: 600;"><?php echo htmlspecialchars($res['result']); ?></span></td>
        </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <div style="text-align: center; padding: 40px; color: var(--muted);">
        <p style="font-size: 18px; margin-bottom: 10px;">No results available yet.</p>
        <p style="font-size: 14px;">Your teacher will add your exam results here.</p>
      </div>
    <?php endif; ?>
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
