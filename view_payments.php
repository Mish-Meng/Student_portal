<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost","root","","schoolportal");

// Pagination
$perPage = 10;
$page = isset($_GET['page_num']) ? max(1,intval($_GET['page_num'])) : 1;
$offset = ($page-1)*$perPage;

// Sorting
$validSorts = ['student_name','amount','fee_type','created_at'];
$sort = in_array($_GET['sort'] ?? '', $validSorts) ? $_GET['sort'] : 'created_at';
$order = ($_GET['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

// Search and filter
$search = $_GET['search'] ?? '';
$filterFeeType = $_GET['feeType'] ?? '';
$filterMethod = $_GET['method'] ?? '';

$whereClauses = [];
if($search) {
    $searchEscaped = $conn->real_escape_string($search);
    $whereClauses[] = "(student_name LIKE '%$searchEscaped%' OR adm_no LIKE '%$searchEscaped%' OR class LIKE '%$searchEscaped%')";
}
if($filterFeeType) $whereClauses[] = "fee_type='$filterFeeType'";
if($filterMethod) $whereClauses[] = "method='$filterMethod'";

$whereSQL = $whereClauses ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Total rows
$totalResult = $conn->query("SELECT COUNT(*) as total FROM payments $whereSQL");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows/$perPage);

// Fetch current page
$result = $conn->query("SELECT * FROM payments $whereSQL ORDER BY $sort $order LIMIT $perPage OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parent Payments</title>
<style>
:root{--accent:#ff7200; --overlay:rgba(0,0,0,0.4); --surface:rgba(255,255,255,0.1); --border:rgba(255,255,255,0.18);}
*{margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif;}
body {
    background: linear-gradient(rgba(255,255,255,0.12), rgba(0,0,0,0.12)), 
                url('https://images.pexels.com/photos/5561923/pexels-photo-5561923.jpeg') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    position: relative;
    color: #fff;
}
body::before{content:""; position:fixed; inset:0; background:var(--overlay); z-index:0;}
.page {position:relative; z-index:1; padding:24px 14px;}
.container {
    max-width:1200px;
    margin:auto;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    padding:25px 30px;
    box-shadow:0 6px 14px rgba(0,0,0,0.22);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
h2 {color:var(--accent); margin-bottom:20px;}
.back-btn {
    display:inline-block; margin-bottom:20px;
    background:var(--accent); color:#000; font-weight:700;
    text-decoration:none; padding:10px 20px; border-radius:10px;
    border:2px solid var(--accent);
}
.back-btn:hover {background:transparent; color:var(--accent);}
.summary {display:flex; gap:20px; margin-bottom:25px; flex-wrap:wrap;}
.summary div {
    flex:1; min-width:180px;
    background:rgba(255,255,255,0.15); color:#fff; 
    padding:20px; border-radius:12px; text-align:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}
.summary div h3 {font-size:20px; margin-bottom:10px;}
.summary div p {font-size:16px;}
.search-form {display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px;}
.search-form input, .search-form select {
    padding:10px 12px; border-radius:10px; border:1px solid var(--border);
    background:rgba(0,0,0,0.25); color:#fff; flex:1; min-width:150px;
}
.search-form button {
    background:var(--accent); color:#000; border:none; padding:10px 15px;
    border-radius:10px; cursor:pointer; font-weight:700;
}
.search-form button:hover {background:transparent; color:var(--accent);}
table {width:100%; border-collapse:collapse; margin-bottom:30px; color:#fff;}
th, td {padding:12px 15px; text-align:left; border-bottom:1px solid rgba(255,255,255,0.18);}
th {background:var(--accent); color:#000; font-weight:600; cursor:pointer;}
tr:nth-child(even){background:rgba(255,255,255,0.05);}
tr:hover{background:rgba(255,255,255,0.15);}
.amount{font-weight:bold; color:var(--accent);}
.method {
    padding:5px 8px; border-radius:5px; color:#000; font-size:13px; font-weight:600;
}
.method.Card {background:#4caf50;}
.method.MobileMoney {background:#2196f3;}
.method.BankTransfer {background:#9c27b0;}
.pagination {display:flex; justify-content:center; gap:10px; margin-top:20px;}
.pagination a {
    background:var(--accent); color:#000; padding:8px 14px; border-radius:8px; text-decoration:none; font-weight:600;
}
.pagination a.active { background:#fff; color:#ff7200; }
.pagination a:hover { background:transparent; color:var(--accent); }
</style>
</head>
<body>
<div class="page">
<div class="container">
<h2>Parent Payments</h2>
<a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<!-- Summary Section -->
<?php
// Total Payments / Students / Most Paid
$totalPaymentsResult = $conn->query("SELECT SUM(amount) as total FROM payments $whereSQL");
$totalPaymentsRow = $totalPaymentsResult->fetch_assoc();
$totalPayments = $totalPaymentsRow['total'] ?? 0;

$totalStudentsResult = $conn->query("SELECT COUNT(DISTINCT adm_no) as total_students FROM payments $whereSQL");
$totalStudentsRow = $totalStudentsResult->fetch_assoc();
$totalStudents = $totalStudentsRow['total_students'] ?? 0;

$mostPaidTypeResult = $conn->query("SELECT fee_type, COUNT(*) as count FROM payments $whereSQL GROUP BY fee_type ORDER BY count DESC LIMIT 1");
$mostPaidTypeRow = $mostPaidTypeResult->fetch_assoc();
$mostPaidType = $mostPaidTypeRow['fee_type'] ?? 'N/A';
?>
<div class="summary">
    <div><h3>Total Payments</h3><p>KES <?= number_format($totalPayments) ?></p></div>
    <div><h3>Students Paid</h3><p><?= $totalStudents ?></p></div>
    <div><h3>Most Paid Fee Type</h3><p><?= $mostPaidType ?></p></div>
</div>

<!-- Search & Filter -->
<form class="search-form" method="GET">
    <input type="text" name="search" placeholder="Search student, adm no, class" value="<?= htmlspecialchars($search) ?>">
    <select name="feeType">
        <option value="">All Fee Types</option>
        <option <?= $filterFeeType=="Tuition"?"selected":"" ?>>Tuition</option>
        <option <?= $filterFeeType=="Library"?"selected":"" ?>>Library</option>
        <option <?= $filterFeeType=="Exam"?"selected":"" ?>>Exam</option>
        <option <?= $filterFeeType=="Transport"?"selected":"" ?>>Transport</option>
        <option <?= $filterFeeType=="Other"?"selected":"" ?>>Other</option>
    </select>
    <select name="method">
        <option value="">All Methods</option>
        <option <?= $filterMethod=="Card"?"selected":"" ?>>Card</option>
        <option <?= $filterMethod=="Mobile Money"?"selected":"" ?>>Mobile Money</option>
        <option <?= $filterMethod=="Bank Transfer"?"selected":"" ?>>Bank Transfer</option>
    </select>
    <button type="submit">Filter</button>
</form>

<!-- Payments Table -->
<table>
<tr>
    <th><a href="?<?= http_build_query(array_merge($_GET,['sort'=>'id','order'=>$sort=='id' && $order=='asc'?'desc':'asc'])) ?>">ID</a></th>
    <th><a href="?<?= http_build_query(array_merge($_GET,['sort'=>'student_name','order'=>$sort=='student_name' && $order=='asc'?'desc':'asc'])) ?>">Student</a></th>
    <th>Adm No</th>
    <th>Class</th>
    <th><a href="?<?= http_build_query(array_merge($_GET,['sort'=>'fee_type','order'=>$sort=='fee_type' && $order=='asc'?'desc':'asc'])) ?>">Fee Type</a></th>
    <th><a href="?<?= http_build_query(array_merge($_GET,['sort'=>'amount','order'=>$sort=='amount' && $order=='asc'?'desc':'asc'])) ?>">Amount</a></th>
    <th>Method</th>
    <th>Reference</th>
    <th><a href="?<?= http_build_query(array_merge($_GET,['sort'=>'created_at','order'=>$sort=='created_at' && $order=='asc'?'desc':'asc'])) ?>">Date</a></th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['student_name'] ?></td>
    <td><?= $row['adm_no'] ?></td>
    <td><?= $row['class'] ?></td>
    <td><?= $row['fee_type'] ?></td>
    <td class="amount"><?= number_format($row['amount']) ?></td>
    <td><span class="method <?= str_replace(' ', '', $row['method']); ?>"><?= $row['method'] ?></span></td>
    <td><?= $row['ref'] ?></td>
    <td><?= $row['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- Pagination -->
<div class="pagination">
<?php if($page>1): ?>
<a href="?<?= http_build_query(array_merge($_GET,['page_num'=>$page-1])) ?>">Previous</a>
<?php endif; ?>
<?php for($i=1;$i<=$totalPages;$i++): ?>
<a href="?<?= http_build_query(array_merge($_GET,['page_num'=>$i])) ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
<?php endfor; ?>
<?php if($page<$totalPages): ?>
<a href="?<?= http_build_query(array_merge($_GET,['page_num'=>$page+1])) ?>">Next</a>
<?php endif; ?>
</div>

</div>
</div>
</body>
</html>
