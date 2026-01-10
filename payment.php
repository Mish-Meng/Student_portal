<?php
session_start();
include 'connect.php';

// Protect the page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read data directly from the form (not from session)
    $studentName = $_POST['studentName'];
    $admNo = $_POST['admNo'];
    $class = $_POST['class'];
    $feeType = $_POST['feeType'];
    $amount = $_POST['amount'];
    $parentEmail = $_POST['email'];
    $method = $_POST['method'];
    $ref = $_POST['ref'];

    // Insert to database
    $stmt = $conn->prepare("INSERT INTO payments (student_name, adm_no, class, fee_type, amount, parent_email, method, ref)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdsss", $studentName, $admNo, $class, $feeType, $amount, $parentEmail, $method, $ref);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Payment recorded successfully!'); window.location='home.php';</script>";
    } else {
        echo "<script>alert('❌ Error saving payment: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment - School portal</title>
  <style>
    :root{--accent:#ff7200; --overlay:rgba(0,0,0,0.40); --surface:rgba(255,255,255,0.10); --border:rgba(255,255,255,0.18);} 
    *{margin:0; padding:0; box-sizing:border-box;}
    body{font-family:'Poppins',sans-serif; color:#fff; background:linear-gradient(rgba(255,255,255,0.12), rgba(0,0,0,0.12)), url('https://images.pexels.com/photos/5561923/pexels-photo-5561923.jpeg') no-repeat center center fixed; background-size:cover; min-height:100vh; position:relative;}
    body::before{content:""; position:fixed; inset:0; background:var(--overlay); z-index:0;}
    .page{position:relative; z-index:1; padding:24px 14px;}
    header{display:flex; justify-content:space-between; align-items:center; padding:12px 8px; border-bottom:1px solid rgba(255,255,255,0.12);} 
    header a{color:#fff; text-decoration:none; margin-right:12px;}
    header .brand{color:var(--accent); font-weight:700;}

    .container{max-width:800px; margin:24px auto; background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px; box-shadow:0 6px 14px rgba(0,0,0,0.22); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);} 
    h1{margin-bottom:10px;}
    p.lead{opacity:.95; margin-bottom:16px;}

    form{display:grid; gap:12px;}
    label{font-weight:600;}
    input, select{width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--border); background:rgba(0,0,0,0.25); color:#fff;}
    .row{display:grid; grid-template-columns:1fr 1fr; gap:12px;}

    .cta{display:inline-block; background:var(--accent); color:#000; font-weight:700; text-decoration:none; padding:12px 20px; border-radius:10px; border:2px solid var(--accent);} 
    .cta:hover{background:transparent; color:var(--accent);} 
    .actions{display:flex; gap:10px; margin-top:12px;}
  </style>
</head>
<body>
  <div class="page">
    <header>
      <div class="brand">School portal</div>
      <nav>
        <a href="home.php">Home</a>
        <a href="admissions.html">Admission</a>
      </nav>
    </header>

    <div class="container">
      <h1>School Fee Payment</h1>
      <p class="lead">Pay tuition and other fees securely. You will receive a receipt via email.</p>
      <form action="stk_push.php" method="POST">
        <div class="row">
          <div>
            <label for="studentName">Student Name</label>
            <input id="studentName" name="studentName" type="text" placeholder="e.g. Jane Doe" required>
          </div>
          <div>
            <label for="admNo">Admission/Student No.</label>
            <input id="admNo" name="admNo" type="text" placeholder="e.g. PS1234" required>
          </div>
        </div>
        <div class="row">
          <div>
            <label for="class">Class</label>
            <select id="class" name="class" required>
              <option value="">Select class</option>
              <option>Grade 1</option>
              <option>Grade 2</option>
              <option>Grade 3</option>
              <option>Grade 4</option>
              <option>Grade 5</option>
              <option>Grade 6</option>
              <option>Grade 7</option>
              <option>Grade 8</option>
            </select>
          </div>
          <div>
            <label for="amount">Amount (KES)</label>
            <input id="amount" name="amount" type="number" min="100" step="50" placeholder="e.g. 5000" required>
          </div>
        </div>
        <div class="row">
          <div>
            <label for="feeType">Fee Type</label>
            <select id="feeType" name="feeType" required>
              <option value="">Select type</option>
              <option>Tuition</option>
              <option>Library</option>
              <option>Exam</option>
              <option>Transport</option>
              <option>Other</option>
            </select>
          </div>
          <div>
            <label for="email">Parent Email</label>
            <input id="email" name="email" type="email" placeholder="parent@example.com" required>
          </div>
          <div>
  <label for="phone">M-Pesa Phone</label>
  <input id="phone" name="phone" type="text" placeholder="2547XXXXXXXX" required>
</div>

        </div>
        <div class="row">
          <div>
            <label for="method">Payment Method</label>
            <select id="method" name="method" required>
              <option value="">Select method</option>
              <option>Mpesa</option>
              <option>Mobile Money</option>
              <option>Bank Transfer</option>
            </select>
          </div>
          <div>
            <label for="ref">Reference (optional)</label>
            <input id="ref" name="ref" type="text" placeholder="Transaction reference">
          </div>
        </div>
        <div class="actions">
          <button class="cta" type="submit">Pay Now</button>
          <a class="cta" href="home.php">Back to Home</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
