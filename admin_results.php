<?php
session_start();

// Restrict access to admins only
if (!isset($_SESSION['admin']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

$message = "";

// Helper to sanitize text output
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch students for select dropdown
$students = $conn->query("SELECT id, fullname, adm_no FROM students ORDER BY fullname ASC");

// Handle add
if (isset($_POST['add_result'])) {
    $student_id = $_POST['student_id'] ?? '';
    $exam_name  = trim($_POST['exam_name'] ?? '');
    $score      = trim($_POST['score'] ?? '');
    $grade      = trim($_POST['grade'] ?? '');

    if ($student_id && $exam_name && $score !== '' && $grade) {
        $stmt = $conn->prepare("INSERT INTO results (student_id, exam_name, score, result) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isis", $student_id, $exam_name, $score, $grade);
            if ($stmt->execute()) {
                $message = "✅ Result added successfully.";
            } else {
                $message = "❌ Failed to add result: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "❌ Failed to prepare statement.";
        }
    } else {
        $message = "❌ Please fill in all fields.";
    }
}

// Handle update
if (isset($_POST['update_result'])) {
    $result_id  = $_POST['result_id'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $exam_name  = trim($_POST['exam_name'] ?? '');
    $score      = trim($_POST['score'] ?? '');
    $grade      = trim($_POST['grade'] ?? '');

    if ($result_id && $student_id && $exam_name && $score !== '' && $grade) {
        $stmt = $conn->prepare("UPDATE results SET student_id=?, exam_name=?, score=?, result=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("isisi", $student_id, $exam_name, $score, $grade, $result_id);
            if ($stmt->execute()) {
                $message = "✅ Result updated successfully.";
            } else {
                $message = "❌ Failed to update result: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "❌ Failed to prepare statement.";
        }
    } else {
        $message = "❌ Please fill in all fields.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM results WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "✅ Result deleted.";
        } else {
            $message = "❌ Failed to delete result: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle search
$search = trim($_GET['search'] ?? '');
$results = null;

if ($search !== '') {
    $like = "%{$search}%";
    $stmt = $conn->prepare(
        "SELECT r.*, s.fullname, s.adm_no
         FROM results r
         LEFT JOIN students s ON r.student_id = s.id
         WHERE r.exam_name LIKE ? OR s.fullname LIKE ? OR s.adm_no LIKE ?
         ORDER BY r.exam_name ASC"
    );
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $results = $stmt->get_result();
    $stmt->close();
} else {
    $results = $conn->query(
        "SELECT r.*, s.fullname, s.adm_no
         FROM results r
         LEFT JOIN students s ON r.student_id = s.id
         ORDER BY r.exam_name ASC"
    );
}

// Fetch record if editing
$editData = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM results WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Manage Results</title>
  <style>
    :root{
      --accent:#ff7200;
      --overlay:rgba(0,0,0,0.55);
      --surface:rgba(255,255,255,0.08);
      --border:rgba(255,255,255,0.2);
      --muted:rgba(229,231,235,0.7);
    }
    *{box-sizing:border-box;font-family:'Poppins','Segoe UI',sans-serif;}
    body{
      margin:0;
      min-height:100vh;
      background:url('https://images.pexels.com/photos/8613327/pexels-photo-8613327.jpeg') no-repeat center/cover;
      position:relative;
      color:#fff;
      padding:32px 16px;
    }
    body::before{
      content:"";
      position:fixed;
      inset:0;
      background:var(--overlay);
      z-index:0;
    }
    .page{position:relative;z-index:1;max-width:1100px;margin:0 auto;}
    h1{margin-bottom:10px;color:var(--accent);}
    .message{
      margin:16px 0;
      padding:12px 16px;
      border-radius:10px;
      background:rgba(0,0,0,0.4);
      border:1px solid var(--border);
    }
    .grid{
      display:grid;
      gap:20px;
      grid-template-columns:300px 1fr;
    }
    .panel{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:14px;
      padding:20px;
      backdrop-filter: blur(10px);
      box-shadow:0 10px 24px rgba(0,0,0,0.35);
    }
    label{display:block;margin-bottom:6px;font-size:0.85rem;color:var(--muted);}
    input, select{
      width:100%;
      padding:10px 12px;
      border-radius:10px;
      border:1px solid var(--border);
      background:rgba(0,0,0,0.25);
      color:#fff;
      margin-bottom:14px;
    }
    button{
      width:100%;
      border:none;
      background:var(--accent);
      color:#000;
      font-weight:600;
      padding:12px;
      border-radius:10px;
      cursor:pointer;
      transition:background 0.2s ease;
    }
    button:hover{background:#ffa45c;}
    table{width:100%;border-collapse:collapse;color:#fff;}
    th,td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.15);text-align:left;}
    th{background:var(--accent);color:#000;}
    tr:nth-child(even){background:rgba(255,255,255,0.05);}
    tr:hover{background:rgba(255,255,255,0.12);}
    .actions a{
      color:#ffb347;
      margin-right:10px;
      text-decoration:none;
      font-weight:600;
    }
    .actions a.delete{color:#ff4d4f;}
    .search-bar{
      display:flex;
      gap:10px;
      margin-bottom:16px;
    }
    .search-bar input{
      flex:1;
      margin:0;
    }
    .secondary-btn{
      width:auto;
      padding:10px 14px;
      background:transparent;
      color:var(--accent);
      border:2px solid var(--accent);
    }
    .secondary-btn:hover{
      background:var(--accent);
      color:#000;
    }
    @media(max-width:900px){
      .grid{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>
  <div class="page">
    <a href="dashboard.php" class="secondary-btn" style="display:inline-block;margin-bottom:18px;">⬅ Back to Dashboard</a>
    <h1>Manage Exam Results</h1>
    <p style="color:var(--muted);margin-bottom:12px;">Only admins can add, edit, or delete results.</p>

    <?php if($message): ?>
      <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <div class="grid">
      <div class="panel">
        <h3 style="margin-top:0;color:#fff;"><?= $editData ? "Edit Result" : "Add Result"; ?></h3>
        <form method="POST">
          <?php if($editData): ?>
            <input type="hidden" name="result_id" value="<?= h($editData['id']) ?>">
          <?php endif; ?>
          <label for="student">Student</label>
          <select name="student_id" required>
            <option value="">Select student</option>
            <?php if($students && $students->num_rows): ?>
              <?php while($stu = $students->fetch_assoc()): ?>
                <option value="<?= $stu['id']; ?>" <?= $editData && $editData['student_id']==$stu['id'] ? 'selected' : '' ?>>
                  <?= h($stu['fullname']) ?> (<?= h($stu['adm_no']) ?>)
                </option>
              <?php endwhile; ?>
            <?php else: ?>
              <option disabled>No students found</option>
            <?php endif; ?>
          </select>

          <label>Exam Name</label>
          <input type="text" name="exam_name" value="<?= h($editData['exam_name'] ?? '') ?>" required>

          <label>Score (%)</label>
          <input type="number" name="score" min="0" max="100" value="<?= h($editData['score'] ?? '') ?>" required>

          <label>Result / Grade</label>
          <input type="text" name="grade" value="<?= h($editData['result'] ?? '') ?>" required>

          <?php if($editData): ?>
            <button type="submit" name="update_result">Update Result</button>
          <?php else: ?>
            <button type="submit" name="add_result">Add Result</button>
          <?php endif; ?>
        </form>
      </div>

      <div class="panel">
        <form method="GET" class="search-bar">
          <input type="text" name="search" placeholder="Search by student, admission no, or exam..." value="<?= h($search) ?>">
          <button type="submit">Search</button>
        </form>
        <div style="overflow-x:auto;">
          <table>
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Exam</th>
              <th>Score</th>
              <th>Result</th>
              <th>Actions</th>
            </tr>
            <?php if($results && $results->num_rows): ?>
              <?php while($row = $results->fetch_assoc()): ?>
                <tr>
                  <td><?= h($row['id']) ?></td>
                  <td><?= h($row['fullname'] ?? 'Unknown') ?><br><small><?= h($row['adm_no'] ?? '-') ?></small></td>
                  <td><?= h($row['exam_name']) ?></td>
                  <td><?= h($row['score']) ?>%</td>
                  <td><?= h($row['result']) ?></td>
                  <td class="actions">
                    <a href="?edit=<?= $row['id'] ?>">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this result?')">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center;color:var(--muted);">No results found.</td>
              </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

