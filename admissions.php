<?php
session_start();
include 'connect.php'; // Make sure this connects to your DB

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Access form values (use quotes for "National Id" since it has space)
    $childName = $_POST['childName'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $entryClass = $_POST['entryClass'] ?? '';
    $term = $_POST['term'] ?? '';
    $parentName = $_POST['parentName'] ?? '';
    $nationalId = $_POST['National_Id'] ?? '';  // Matches your HTML
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Basic validation
    if ($childName && $dob && $entryClass && $term && $parentName && $nationalId && $phone && $email && $address) {

        // Prepare SQL insert
        $stmt = mysqli_prepare($conn, "INSERT INTO admissions 
            (child_name, dob, entry_class, term, parent_name, national_id, phone, email, address, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssssssss", $childName, $dob, $entryClass, $term, $parentName, $nationalId, $phone, $email, $address, $notes);

     if (mysqli_stmt_execute($stmt)) {
            $message = "✅ You have successfully submitted your application!";
        }  else {
            $message = "❌ Failed to submit: " . mysqli_error($conn);
        }

    } else {
        $message = "❌ Please fill in all required fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admissions - School portal</title>
  <style>
    :root{--accent:#ff7200; --overlay:rgba(0,0,0,0.35); --surface:rgba(255,255,255,0.10); --border:rgba(255,255,255,0.18);} 
    *{margin:0; padding:0; box-sizing:border-box;}
    body{font-family:'Poppins',sans-serif; color:#fff; background:url('https://images.pexels.com/photos/5905458/pexels-photo-5905458.jpeg') no-repeat center center fixed; background-size:cover; min-height:100vh; position:relative;}
    body::before{content:""; position:fixed; inset:0; background:var(--overlay); z-index:0;}
    .page{position:relative; z-index:1; padding:24px 14px;}
    header{display:flex; justify-content:space-between; align-items:center; padding:12px 8px; border-bottom:1px solid rgba(255,255,255,0.12);} 
    header a{color:#fff; text-decoration:none; margin-right:12px;}
    header .brand{color:var(--accent); font-weight:700;}

    .container{max-width:900px; margin:24px auto; background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px; box-shadow:0 6px 14px rgba(0,0,0,0.22); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);} 
    h1{margin-bottom:10px;}
    p.lead{opacity:.95; margin-bottom:16px;}

    form{display:grid; gap:12px;}
    label{font-weight:600;}
    input, select, textarea{width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--border); background:rgba(0,0,0,0.25); color:#fff;}
    textarea{min-height:90px;}
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
        <a href="payment.html">Payment</a>
      </nav>
    </header>

    <div class="container">
      <h1>Admission Application</h1>
      <p class="lead">Apply for admission by filling out the form below. Our team will contact you.</p>
      <form action="#" method="POST">
        <div class="row">
          <div>
            <label for="childName">Child's Full Name</label>
            <input id="childName" name="childName" type="text" placeholder="e.g. John Doe" required>
          </div>
          <div>
            <label for="dob">Date of Birth</label>
            <input id="dob" name="dob" type="date" required>
          </div>
        </div>
        <div class="row">
          <div>
            <label for="entryClass">Entry Class</label>
            <select id="entryClass" name="entryClass" required>
              <option value="">Select class</option>
              <option>Pre-Primary 1</option>
              <option>Pre-Primary 2</option>
              <option>Grade 1</option>
              <option>Grade 2</option>
              <option>Grade 3</option>
              <option>Grade 4</option>
              <option>Grade 5</option>
              <option>Grade 6</option>
              <option>Grade 7</option>
              <option>Grade 8</option>
              <option>Grade 9</option>
            </select>
          </div>
          <div>
            <label for="term">Term</label>
            <select id="term" name="term" required>
              <option value="">Select term</option>
              <option>Term 1</option>
              <option>Term 2</option>
              <option>Term 3</option>
            </select>
          </div>
        </div>
        <div class="row">
          <div>
            <label for="parentName">Parent/Guardian Name</label>
            <input id="parentName" name="parentName" type="text" placeholder="e.g. Jane Doe" required>
          </div>
          <div>
            <label for="National Id">National Id</label>
            <input id="National Id" name="National Id" type="integer" required>
          </div>
          <div>
            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="tel" placeholder="e.g. +254 712 345 678" required>
          </div>
        </div>
        <div class="row">
          <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="parent@example.com" required>
          </div>
          <div>
            <label for="address">Address</label>
            <input id="address" name="address" type="text" placeholder="Town, Street, House No." required>
          </div>
        </div>
        <div>
          <label for="notes">Additional Notes (optional)</label>
          <textarea id="notes" name="notes" placeholder="Allergies, special needs, or other information"></textarea>
        </div>
        <div class="actions">
          <button class="cta" type="submit">Submit Application</button>
          <a class="cta" href="home.php">Back to Home</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html> 