<?php
session_start();

// Protect teacher pages
if (!isset($_SESSION['teacher']) || $_SESSION['role'] != 'teacher') {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$message = "";

// Helper to sanitize text output
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Get teacher's assigned class grades
$teacher_classes_query = mysqli_query($conn, "SELECT grade FROM classes WHERE teacher LIKE '%$teacher_name%'");
$teacher_class_grades = [];
while ($row = mysqli_fetch_assoc($teacher_classes_query)) {
    $teacher_class_grades[] = mysqli_real_escape_string($conn, $row['grade']);
}

// Build WHERE clause for class filtering (for student dropdown)
$class_filter_students = '';
if (!empty($teacher_class_grades)) {
    $class_list = "'" . implode("','", $teacher_class_grades) . "'";
    $class_filter_students = "AND class IN ($class_list)";
}

// Fetch students for select dropdown (only teacher's students)
$students = mysqli_query($conn, "SELECT id, fullname, adm_no FROM students WHERE 1=1 $class_filter_students ORDER BY fullname ASC");

// Handle add
if (isset($_POST['add_result'])) {
    $student_id = $_POST['student_id'] ?? '';
    $exam_name  = trim($_POST['exam_name'] ?? '');
    $score      = trim($_POST['score'] ?? '');
    $grade      = trim($_POST['grade'] ?? '');

    if ($student_id && $exam_name && $score !== '' && $grade) {
        // Verify student belongs to teacher's classes
        $student_check = mysqli_query($conn, "SELECT class FROM students WHERE id = $student_id");
        if (mysqli_num_rows($student_check) > 0) {
            $student = mysqli_fetch_assoc($student_check);
            if (in_array($student['class'], $teacher_class_grades)) {
                $stmt = mysqli_prepare($conn, "INSERT INTO results (student_id, exam_name, score, result) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "isis", $student_id, $exam_name, $score, $grade);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "✅ Result added successfully.";
                    } else {
                        $message = "❌ Failed to add result: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "❌ Failed to prepare statement.";
                }
            } else {
                $message = "❌ You can only add results for students in your assigned classes.";
            }
        } else {
            $message = "❌ Student not found.";
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
        // Verify student belongs to teacher's classes
        $student_check = mysqli_query($conn, "SELECT class FROM students WHERE id = $student_id");
        if (mysqli_num_rows($student_check) > 0) {
            $student = mysqli_fetch_assoc($student_check);
            if (in_array($student['class'], $teacher_class_grades)) {
                $stmt = mysqli_prepare($conn, "UPDATE results SET student_id=?, exam_name=?, score=?, result=? WHERE id=?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "isisi", $student_id, $exam_name, $score, $grade, $result_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "✅ Result updated successfully.";
                    } else {
                        $message = "❌ Failed to update result: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "❌ Failed to prepare statement.";
                }
            } else {
                $message = "❌ You can only update results for students in your assigned classes.";
            }
        } else {
            $message = "❌ Student not found.";
        }
    } else {
        $message = "❌ Please fill in all fields.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    
    // Verify result belongs to teacher's student
    $result_check = mysqli_query($conn, 
        "SELECT s.class FROM results r 
         LEFT JOIN students s ON r.student_id = s.id 
         WHERE r.id = $delete_id"
    );
    if (mysqli_num_rows($result_check) > 0) {
        $result_data = mysqli_fetch_assoc($result_check);
        if (in_array($result_data['class'], $teacher_class_grades)) {
            $stmt = mysqli_prepare($conn, "DELETE FROM results WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $delete_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "✅ Result deleted.";
                } else {
                    $message = "❌ Failed to delete result: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $message = "❌ You can only delete results for students in your assigned classes.";
        }
    } else {
        $message = "❌ Result not found.";
    }
}

// Handle search
$search = trim($_GET['search'] ?? '');
$results = null;

// Build class filter for WHERE clause (for results query)
$class_filter_results = '';
if (!empty($teacher_class_grades)) {
    $class_list_results = "'" . implode("','", $teacher_class_grades) . "'";
    $class_filter_results = "AND s.class IN ($class_list_results)";
}

if ($search !== '') {
    $like = "%{$search}%";
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $results = mysqli_query($conn,
        "SELECT r.*, s.fullname, s.adm_no
         FROM results r
         LEFT JOIN students s ON r.student_id = s.id
         WHERE (r.exam_name LIKE '%$search_escaped%' OR s.fullname LIKE '%$search_escaped%' OR s.adm_no LIKE '%$search_escaped%') $class_filter_results
         ORDER BY r.exam_name ASC"
    );
} else {
    $results = mysqli_query($conn,
        "SELECT r.*, s.fullname, s.adm_no
         FROM results r
         LEFT JOIN students s ON r.student_id = s.id
         WHERE 1=1 $class_filter_results
         ORDER BY r.exam_name ASC"
    );
}

// Fetch record if editing
$editData = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM results WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher | Manage Marks</title>
  <link rel="stylesheet" href="teacher_dashboard.css">
  <style>
    :root{
      --accent:#ff7200;
      --overlay:rgba(0,0,0,0.55);
      --surface:rgba(255,255,255,0.12);
      --border:rgba(255,255,255,0.18);
      --muted:rgba(229,231,235,0.7);
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
    .message.success{
      background:rgba(34,197,94,0.2);
      border-color:rgba(34,197,94,0.5);
      color:#86efac;
    }
    .message.error{
      background:rgba(239,68,68,0.2);
      border-color:rgba(239,68,68,0.5);
      color:#fca5a5;
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
      text-decoration:none;
      display:inline-block;
      margin-bottom:18px;
      border-radius:10px;
      font-weight:600;
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
    <a href="teacher_dashboard.php" class="secondary-btn">⬅ Back to Dashboard</a>
    <h1>Manage Student Marks</h1>
    <p style="color:var(--muted);margin-bottom:12px;">Add, edit, or delete student exam results for your assigned classes.</p>
    <?php if(!empty($teacher_class_grades)): ?>
      <div style="background: rgba(255,114,0,0.15); border: 1px solid rgba(255,114,0,0.3); border-radius: 8px; padding: 12px; margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
        <span style="color: var(--muted); font-size: 13px; margin-right: 8px;">📚 Your Classes:</span>
        <?php foreach($teacher_class_grades as $grade): ?>
          <span style="background: rgba(255,114,0,0.25); border: 1px solid var(--accent); border-radius: 6px; padding: 4px 10px; font-size: 12px; color: var(--accent); font-weight: 600;">
            <?= htmlspecialchars($grade) ?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if($message): ?>
      <div class="message <?= strpos($message,'❌')!==false?'error':'success' ?>"><?= h($message) ?></div>
    <?php endif; ?>

    <div class="grid">
      <div class="panel">
        <h3 style="margin-top:0;color:#fff;"><?= $editData ? "Edit Result" : "Add Result"; ?></h3>
        <form method="POST">
          <?php if($editData): ?>
            <input type="hidden" name="result_id" value="<?= h($editData['id']) ?>">
          <?php endif; ?>
          <label for="student">Student</label>
          <select name="student_id" id="student" required>
            <option value="">Select student</option>
            <?php if($students && mysqli_num_rows($students) > 0): ?>
              <?php 
              mysqli_data_seek($students, 0); // Reset pointer
              while($stu = mysqli_fetch_assoc($students)): ?>
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
            <?php if($results && mysqli_num_rows($results) > 0): ?>
              <?php while($row = mysqli_fetch_assoc($results)): ?>
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

