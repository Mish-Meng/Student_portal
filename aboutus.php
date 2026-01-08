<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$fullname = $isLoggedIn ? ($_SESSION['fullname'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Langata Road Primary & Junior School</title>
  <style>
    :root{
      --accent:#ff7200;
      --overlay:rgba(0,0,0,0.55);
      --surface:rgba(255,255,255,0.12);
      --border:rgba(255,255,255,0.18);
      --muted:rgba(255,255,255,0.85);
    }
    *{margin:0;padding:0;box-sizing:border-box;}
    body{
      font-family:'Poppins','Segoe UI',sans-serif;
      color:#fff;
      background:url('https://images.pexels.com/photos/5905708/pexels-photo-5905708.jpeg') no-repeat center center fixed;
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
    .page{position:relative;z-index:1;}

    header{
      background:rgba(0,0,0,0.55);
      padding:16px 20px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      border-bottom:1px solid rgba(255,255,255,0.12);
    }
    header h2{color:var(--accent);letter-spacing:0.5px;}
    nav a{
      margin:0 12px;
      text-decoration:none;
      color:#fff;
      font-weight:600;
      transition:color .2s ease;
    }
    nav a:hover{color:var(--accent);}
    nav a.active{color:var(--accent);border-bottom:2px solid var(--accent);padding-bottom:2px;}

    .about-section{
      padding:60px 20px;
      max-width:1200px;
      margin:0 auto;
    }

    .header{
      text-align:center;
      margin-bottom:50px;
    }
    .header h1{
      font-size:48px;
      color:var(--accent);
      margin-bottom:20px;
      font-weight:700;
    }
    .header .intro{
      max-width:800px;
      margin:0 auto;
      font-size:18px;
      line-height:1.7;
      color:var(--muted);
    }
    .header .intro strong{
      color:var(--accent);
    }

    .about-boxes{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
      gap:24px;
      margin-top:40px;
    }

    .box{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:14px;
      padding:28px;
      backdrop-filter:blur(10px);
      box-shadow:0 10px 24px rgba(0,0,0,0.35);
      transition:transform .2s ease,box-shadow .2s ease;
    }
    .box:hover{
      transform:translateY(-4px);
      box-shadow:0 14px 32px rgba(0,0,0,0.4);
    }
    .box h2{
      font-size:24px;
      color:var(--accent);
      margin-bottom:16px;
      font-weight:700;
    }
    .box p{
      color:var(--muted);
      line-height:1.7;
      font-size:15px;
    }

    @media(max-width:768px){
      .header h1{font-size:36px;}
      .about-boxes{grid-template-columns:1fr;}
      header{flex-direction:column;gap:12px;}
      nav{display:flex;flex-wrap:wrap;justify-content:center;}
    }
  </style>
</head>
<body>
  <div class="page">
    <header>
      <h2>School Portal</h2>
      <nav>
        <a href="home.php">Home</a>
        <a href="aboutus.php" class="active">About Us</a>
        <a href="events.php">Events</a>
        <a href="profile.php">Profile</a>
        <a href="contactus.php">Contact</a>
        <?php if($isLoggedIn): ?>
          <a href="logout.php" style="color:var(--accent);">Logout</a>
        <?php endif; ?>
      </nav>
    </header>

    <section class="about-section">
      <div class="header">
        <h1>About Us</h1>
        <p class="intro">
          At <strong>Langata Road Primary &amp; Junior School</strong> we nurture curious,
          confident learners through quality teaching, strong values and community partnership.
        </p>
      </div>

      <div class="about-boxes">
        <article class="box">
          <h2>🏆 Achievements</h2>
          <p>
            Consistent top performers in county exams, regional sports champions,
            and winners in art & science fairs — proud results from hard work and teamwork.
          </p>
        </article>

        <article class="box">
          <h2>👁️ Vision</h2>
          <p>
            To be a leading primary school that develops responsible, creative and resilient
            learners prepared for the 21st century.
          </p>
        </article>

        <article class="box">
          <h2>🎯 Mission</h2>
          <p>
            Provide a safe, inclusive environment that encourages academic excellence,
            good character and community service.
          </p>
        </article>
      </div>
    </section>
  </div>
</body>
</html>
