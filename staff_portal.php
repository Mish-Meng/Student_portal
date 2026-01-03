<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Portal - Langata Road Primary & Junior School</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .choice-container {
      display: flex;
      gap: 20px;
      margin-top: 30px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .choice-card {
      background: var(--surface);
      border: 2px solid var(--border);
      border-radius: 14px;
      padding: 40px 30px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 250px;
      flex: 1;
      max-width: 350px;
    }
    .choice-card:hover {
      transform: translateY(-5px);
      border-color: var(--accent);
      box-shadow: 0 15px 35px rgba(255,114,0,0.3);
    }
    .choice-card h3 {
      color: var(--accent);
      margin: 15px 0 10px 0;
      font-size: 24px;
    }
    .choice-card p {
      color: var(--muted);
      font-size: 14px;
      margin-bottom: 20px;
    }
    .choice-card .icon {
      font-size: 48px;
      margin-bottom: 10px;
    }
    .choice-btn {
      background: var(--accent);
      color: #000;
      border: none;
      padding: 12px 24px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: background 0.2s ease;
    }
    .choice-btn:hover {
      background: var(--accent-hover);
    }
  </style>
</head>
<body>

<div class="admin-container" style="max-width: 800px;">
  <div class="admin-header">
    <h2>Staff Portal</h2>
    <p>Choose your access type</p>
  </div>

  <div class="choice-container">
    <div class="choice-card">
      <div class="icon">👨‍🏫</div>
      <h3>Staff/Teacher</h3>
      <p>New staff members need to sign up and verify their email</p>
      <a href="teacher_signup.php" class="choice-btn">Sign Up / Login</a>
    </div>

    <div class="choice-card">
      <div class="icon">👨‍💼</div>
      <h3>Admin</h3>
      <p>Administrator access to manage the entire system</p>
      <a href="admin_login.php" class="choice-btn">Admin Login</a>
    </div>
  </div>

  <div class="footer-note" style="margin-top: 30px;">
    Langata Road Primary & Junior School
  </div>
</div>

</body>
</html>

