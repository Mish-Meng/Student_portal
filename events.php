<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "schoolportal");
if ($conn->connect_error) die("Connection Failed: " . $conn->connect_error);

// Fetch all events
$events_result = $conn->query("SELECT * FROM events ORDER BY date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Events</title>
  <style>
    :root{
      --accent:#ff7200;
      --overlay:rgba(0,0,0,0.55);
      --surface:rgba(255,255,255,0.12);
      --border:rgba(255,255,255,0.2);
    }
    *{margin:0;padding:0;box-sizing:border-box;}
    body{
      font-family:'Poppins',sans-serif;
      color:#fff;
      background:url('https://images.pexels.com/photos/33410489/pexels-photo-33410489.jpeg') no-repeat center center fixed;
      background-size:cover;
      min-height:100vh;
      position:relative;
    }
    body::before{
      content:"";
      position:fixed;
      inset:0;
      background:var(--overlay);
      z-index:0;
    }
    .page{position:relative; z-index:1; padding:20px;}

    header{
      background: rgba(0,0,0,0.55);
      padding:16px 20px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      border-bottom:1px solid rgba(255,255,255,0.12);
    }
    header h2{color:var(--accent);}
    nav a{margin:0 12px;text-decoration:none;color:#fff;font-weight:600;transition:color .2s;}
    nav a:hover{color:var(--accent);}

    h1{text-align:center;margin:24px 0;color:var(--accent);}

    .events-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
      gap:16px;
      max-width:1200px;
      margin:0 auto 50px;
    }
    .event-card{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:14px;
      padding:18px;
      box-shadow:0 6px 14px rgba(0,0,0,0.22);
      backdrop-filter:blur(10px);
    }
    .event-date{color:var(--accent); font-weight:700; font-size:0.9rem; margin-bottom:8px;}
    .event-title{font-size:1.2rem; font-weight:700; margin-bottom:8px;}
    .event-desc{opacity:0.9; line-height:1.5; margin-bottom:12px;}
    .event-meta{display:flex; justify-content:space-between; font-size:0.9rem; opacity:0.85;}

    footer{background: rgba(0,0,0,0.75); color: #fff; text-align: center; padding: 18px 12px; border-top:1px solid rgba(255,255,255,0.12);}
  </style>
</head>
<body>
  <div class="page">
    <header>
      <h2>School Portal</h2>
      <nav>
        <a href="home.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" style="color:var(--accent);">Logout</a>
      </nav>
    </header>

    <h1>Upcoming School Events</h1>

    <div class="events-grid">
      <?php if($events_result->num_rows > 0): ?>
        <?php while($event = $events_result->fetch_assoc()): ?>
          <div class="event-card">
            <div class="event-date">📅 <?= htmlspecialchars(date("F j, Y", strtotime($event['date']))) ?></div>
            <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
            <div class="event-desc"><?= htmlspecialchars($event['description']) ?></div>
            <div class="event-meta">
              <span>⏰ <?= htmlspecialchars($event['time']) ?></span>
              <span>📍 <?= htmlspecialchars($event['location']) ?></span>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="text-align:center; color:#fff;">No upcoming events yet.</p>
      <?php endif; ?>
    </div>

    <footer>
      <p>© 2025 School Portal | All Rights Reserved</p>
    </footer>
  </div>
</body>
</html>
