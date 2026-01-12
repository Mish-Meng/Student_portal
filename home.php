<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "schoolportal");
if ($conn->connect_error) die("Connection Failed: " . $conn->connect_error);

// Fetch latest 3 upcoming events
$events_result = $conn->query("SELECT * FROM events ORDER BY date ASC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Portal</title>
  <style>
    :root{--accent:#ff7200; --overlay:rgba(0,0,0,0.55); --surface:rgba(255,255,255,0.12); --border:rgba(255,255,255,0.2);}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{
      font-family: 'Poppins', sans-serif;
      color:#fff;
      background:url('https://images.pexels.com/photos/3762806/pexels-photo-3762806.jpeg') no-repeat center center fixed;
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
    .page{position:relative; z-index:1;}

    header{
      background: rgba(0,0,0,0.55);
      padding:16px 20px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      border-bottom:1px solid rgba(255,255,255,0.12);
    }
    header h2{color:var(--accent);letter-spacing:0.5px;}
    nav a{margin:0 12px;text-decoration:none;color:#fff;font-weight:600;transition:color .2s ease;}
    nav a:hover{color:var(--accent);}

    .hero{text-align:center;padding:70px 20px 40px;}
    .hero h1{font-size:44px;line-height:1.2;margin-bottom:12px;}
    .hero p{opacity:0.95;margin-bottom:20px;}

    /* Slideshow */
    .slideshow{position:relative;max-width:1300px;margin:10px auto 18px;aspect-ratio:15/7;border-radius:14px;overflow:hidden;box-shadow:0 10px 24px rgba(0,0,0,0.35);border:1px solid rgba(255,255,255,0.18);}
    .slide{position:absolute;inset:0;opacity:0;transition:opacity .7s ease;}
    .slide.active{opacity:1;}
    .slide img{width:100%;height:100%;object-fit:cover;display:block;}
    .slide::after{content:"";position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.55), rgba(0,0,0,0.15));}
    .caption{position:absolute;left:50%;bottom:10%;transform:translateX(-50%);background:rgba(0,0,0,0.45);padding:10px 16px;border-radius:10px;border:1px solid var(--border);color:#fff;font-weight:600;letter-spacing:.3px;max-width:90%;}
    .caption span{color:var(--accent);}

    .indicators{display:flex;justify-content:center;gap:8px;margin-top:10px;}
    .dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.5);}
    .dot.active{background:var(--accent);}

    .cta{display:inline-block;background:var(--accent);color:#fff;text-decoration:none;padding:12px 24px;margin:8px;font-size:16px;border-radius:10px;transition:all .2s ease;border:2px solid var(--accent);}
    .cta:hover{background:transparent;color:var(--accent);}

    /* Events Section */
    .events{max-width:1200px;margin:24px auto 10px;padding:0 14px;display:grid;grid-template-columns:280px 1fr;gap:16px;}
    .events-banner {
    background: url("https://images.pexels.com/photos/159607/basketball-player-girls-basketball-girl-159607.jpeg") no-repeat center center;
    background-size: cover;      /* Makes the image cover the entire banner */
    background-position: center; /* Keeps image centered */
    background-repeat: no-repeat; /* Prevents tiling */
    color: #fff;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 6px 14px rgba(0,0,0,0.25);
}

    .events-banner h2{font-size:36px; line-height:1.1;}
    .events-banner p{opacity:.9;margin-top:8px;}
    .events-banner .view-btn{display:inline-flex;align-items:center;gap:8px;margin-top:18px;background:#fff;color:#4a2a7a;font-weight:700;text-decoration:none;padding:10px 14px;border-radius:8px;}

    .events-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px;box-shadow:0 6px 14px rgba(0,0,0,0.22);}
    .event-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px;box-shadow:0 6px 14px rgba(0,0,0,0.22);backdrop-filter:blur(10px);}
    .event-date{color:var(--accent); font-weight:700; font-size:0.9rem; margin-bottom:8px;}
    .event-title{font-size:1.15rem; font-weight:700;margin-bottom:8px;}
    .event-desc{opacity:0.9;line-height:1.5;margin-bottom:12px;}
    .event-meta{display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;opacity:0.85;}

    /* Quick cards Section */
    .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;padding:30px 20px 50px;max-width:1100px;margin:0 auto;}
    .card{display:block;color:inherit;text-decoration:none;background:var(--surface);padding:28px;border-radius:14px;text-align:center;box-shadow:0 6px 14px rgba(0,0,0,0.25);border:1px solid var(--border);backdrop-filter:blur(6px);}
    .card:hover{text-decoration:none;}
    .card h3{margin-top:8px;color:#fff;}

    footer{background: rgba(0,0,0,0.75); color: #fff; text-align: center; padding: 18px 12px; border-top:1px solid rgba(255,255,255,0.12);}
  </style>
</head>
<body>
  <div class="page">
    <header>
      <h2>School Portal</h2>
      <nav>
        <a href="home.php">Home</a>
        <a href="aboutus.php">About Us</a>
        <a href="events.php">Events</a>
        <a href="student_timetable.php">📅 Timetable</a>
        <a href="exams_results.php">📊 Results</a>
        <a href="profile.php">Profile</a>
        <a href="Contactus.php">Contact</a>
        <a href="logout.php" style="color:var(--accent);">Logout</a>
      </nav>
    </header>

    <section class="hero">
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?> 👋</h1>
      <p>You are now logged in to your school portal.</p>

      <div class="slideshow">
        <div class="slide active">
          <img src="https://images.pexels.com/photos/8613327/pexels-photo-8613327.jpeg" alt="Students learning">
          <div class="caption">Inspiring young minds every day</div>
        </div>
        <div class="slide">
          <img src="https://images.pexels.com/photos/8617899/pexels-photo-8617899.jpeg" alt="Teacher guiding students">
          <div class="caption">A <span>safe</span> and happy place to learn</div>
        </div>
        <div class="slide">
          <img src="https://images.pexels.com/photos/5905451/pexels-photo-5905451.jpeg" alt="Children collaborating">
          <div class="caption">Where curiosity grows into <span>confidence</span></div>
        </div>
      </div>
      <div class="indicators" aria-hidden="true">
        <span class="dot active"></span>
        <span class="dot"></span>
        <span class="dot"></span>
      </div>
      <a class="cta" href="payment.php">Payment</a>
      <a class="cta" href="admissions.php">Admission</a>
      <a class="cta" href="student_timetable.php">📅 My Timetable</a>
      <a class="cta" href="exams_results.php">📊 Results</a>
    </section>

    <!-- Events Section -->
<section class="events" id="events">
  <div class="events-banner">
    <div>
      <h2>EVENTS</h2>
      <p>School events and activities</p>
    </div>
    <a class="view-btn" href="events.php">VIEW ALL EVENTS →</a>
  </div>
  <div>
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
        <p style="color:#fff; text-align:center;">No upcoming events yet.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

    <!-- Quick cards Section -->
    <section class="cards">
      <a class="card" href="clss.php">📚 <h3>Classes</h3></a>
      <a class="card" href="profile.php">👩‍🏫 <h3>Student</h3></a>
      <a class="card" href="exams_results.php">📝 <h3>Exam_Results</h3></a>
      <a class="card" href="events.php">🎉 <h3>Events</h3></a>
      
    </section>

    <footer>
      <p>© 2025 School Portal | All Rights Reserved</p>
    </footer>
  </div>

  <script>
    // Slideshow functionality
    (function(){
      const slides = Array.from(document.querySelectorAll('.slide'));
      const dots = Array.from(document.querySelectorAll('.dot'));
      let i = 0;
      function show(index){
        slides.forEach((s,idx)=>s.classList.toggle('active', idx===index));
        dots.forEach((d,idx)=>d.classList.toggle('active', idx===index));
      }
      setInterval(()=>{ i=(i+1)%slides.length; show(i); }, 3500);
    })();
  </script>
</body>
</html>
