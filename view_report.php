<?php
session_start();

// Restrict access to admins only
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

include 'connect.php';

// Handle search
$search = $_GET['search'] ?? '';
$where = '';
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $where = "WHERE child_name LIKE '%$searchEscaped%' OR entry_class LIKE '%$searchEscaped%' OR parent_name LIKE '%$searchEscaped%'";
}

// Fetch admissions
$query = "SELECT * FROM admissions $where ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admission Reports - Admin Panel</title>
<style>
:root{--accent:#ff7200; --overlay:rgba(0,0,0,0.35); --surface:rgba(255,255,255,0.1); --border:rgba(255,255,255,0.18);}
*{margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif;}
body {
    background: linear-gradient(rgba(255,255,255,0.12), rgba(0,0,0,0.12)),
                url('https://images.pexels.com/photos/5905458/pexels-photo-5905458.jpeg') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
    min-height: 100vh;
    position: relative;
}
body::before{content:""; position:fixed; inset:0; background:var(--overlay); z-index:0;}
.page{position:relative; z-index:1; padding:24px 14px;}
.container{
    max-width:1100px; margin:24px auto;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    padding:25px 30px;
    box-shadow:0 6px 14px rgba(0,0,0,0.22);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
h2{color:var(--accent); margin-bottom:20px;}
.search-bar{display:flex; gap:10px; margin-bottom:20px;}
.search-bar input{
    flex:1;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid var(--border);
    background:rgba(0,0,0,0.25);
    color:#fff;
}
.search-bar button{
    background:var(--accent);
    border:none;
    color:#000;
    font-weight:600;
    padding:10px 18px;
    border-radius:10px;
    cursor:pointer;
}
.search-bar button:hover{background:transparent; color:var(--accent);}
table{width:100%; border-collapse:collapse; color:#fff;}
th, td{padding:12px 10px; border-bottom:1px solid rgba(255,255,255,0.15); text-align:left;}
th{background:var(--accent); color:#000;}
tr:nth-child(even){background:rgba(255,255,255,0.05);}
tr:hover{background:rgba(255,255,255,0.15);}
.cta{display:inline-block; background:var(--accent); color:#000; font-weight:700; text-decoration:none; padding:10px 18px; border-radius:10px; border:2px solid var(--accent);}
.cta:hover{background:transparent; color:var(--accent);}
</style>
</head>
<body>
<div class="page">
  <div class="container">
    <h2>📋 Admission Applications Report</h2>
    <form method="GET" class="search-bar">
      <input type="text" name="search" placeholder="Search by child, class, or parent..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Search</button>
    </form>

    <table>
      <tr>
        <th>ID</th>
        <th>Child Name</th>
        <th>Entry Class</th>
        <th>Term</th>
        <th>Parent</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Date</th>
      </tr>
      <?php if($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['child_name']) ?></td>
            <td><?= htmlspecialchars($row['entry_class']) ?></td>
            <td><?= htmlspecialchars($row['term']) ?></td>
            <td><?= htmlspecialchars($row['parent_name']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center; color:#ccc;">No applications found.</td></tr>
      <?php endif; ?>
    </table>

    <div style="margin-top:20px; text-align:right;">
      <a href="dashboard.php" class="cta">⬅ Back to Dashboard</a>
    </div>
  </div>
</div>
</body>
</html>
