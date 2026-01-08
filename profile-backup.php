<!DOCTYPE php>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Langata Road Primary & Junior School</title>

  <style>
    /* ======== GENERAL ======== */
    body {
      font-family: "Poppins", Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: url('https://images.pexels.com/photos/3401403/pexels-photo-3401403.jpeg') no-repeat center center/cover;
      color: #333;
    }

    .container {
      width: 95%;
      max-width: 900px;
      margin: 40px auto;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      border-radius: 16px;
      box-shadow: 0 6px 25px rgba(0,0,0,0.2);
      padding: 30px;
    }

    h1 {
      text-align: center;
      color: #ff7200;
      letter-spacing: 1px;
    }

    /* ======== HEADER ======== */
    .profile-header {
      display: flex;
      align-items: center;
      gap: 20px;
      border-bottom: 2px solid #eee;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }

    .profile-header img {
      width: 110px;
      height: 110px;
      border-radius: 50%;
      border: 3px solid #2ecc71;
      object-fit: cover;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .profile-header img:hover {
      transform: scale(1.05);
    }

    .profile-info h2 {
      margin: 0;
      color: #005ea6;
    }

    .profile-info p {
      margin: 4px 0;
      font-size: 15px;
    }

    /* ======== SECTION ======== */
    .section {
      margin-bottom: 30px;
    }

    .section h3 {
      background: linear-gradient(90deg, #ff7200, #ff9e3d);
      color: #fff;
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 10px;
      font-size: 18px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255,255,255,0.8);
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: left;
      font-size: 15px;
    }

    th {
      background: #f4f4f4;
      color: #333;
    }

    .balance {
      color: #ff7200;
      font-weight: bold;
    }

    /* ======== FOOTER ======== */
    footer {
      text-align: center;
      padding: 15px;
      color: white;
      background: rgba(0,0,0,0.6);
      margin-top: 30px;
      border-radius: 0 0 16px 16px;
      font-size: 14px;
    }

    /* ======== RESPONSIVE ======== */
    @media (max-width: 600px) {
      .profile-header {
        flex-direction: column;
        text-align: center;
      }

      .profile-header img {
        width: 90px;
        height: 90px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>My Profile</h1>

    <!-- PROFILE HEADER -->
    <div class="profile-header">
      <img id="profilePic" src="image/student.jpg" alt="Student Photo">
      <div class="profile-info">
        <h2 id="studentName">John Doe</h2>
        <p>Admission No: <b>PS1234</b></p>
        <p>Class: <b>Grade 5</b></p>
      </div>
    </div>

    <!-- PERSONAL DETAILS -->
    <div class="section">
      <h3>Personal Details</h3>
      <table>
        <tr><th>Full Name</th><td>John Doe</td></tr>
        <tr><th>Date of Birth</th><td>12th Jan 2012</td></tr>
        <tr><th>Gender</th><td>Male</td></tr>
        <tr><th>Parent/Guardian</th><td>Mr. & Mrs. Doe</td></tr>
        <tr><th>Contact</th><td>+254 712 345 678</td></tr>
      </table>
    </div>

    <!-- FEE BALANCE -->
    <div class="section">
      <h3>Fee Balance</h3>
      <table>
        <tr><th>Total Fees</th><td>KES 50,000</td></tr>
        <tr><th>Amount Paid</th><td>KES 40,000</td></tr>
        <tr><th>Balance</th><td class="balance">KES 10,000</td></tr>
      </table>
    </div>

    <!-- ACADEMIC RECORDS -->
    <div class="section">
      <h3>Academic Records</h3>
      <table>
        <tr>
          <th>Subject</th>
          <th>Term 1</th>
          <th>Term 2</th>
          <th>Term 3</th>
        </tr>
        <tr><td>Mathematics</td><td>80%</td><td>85%</td><td>82%</td></tr>
        <tr><td>English</td><td>78%</td><td>74%</td><td>80%</td></tr>
        <tr><td>Science</td><td>88%</td><td>90%</td><td>87%</td></tr>
        <tr><td>Social Studies</td><td>75%</td><td>72%</td><td>78%</td></tr>
      </table>
    </div>

    <!-- FOOTER -->
    <footer>
      &copy; <?php echo date('Y'); ?> Langata Road Primary & Junior School | All Rights Reserved.
    </footer>
  </div>

  <!-- ======== JAVASCRIPT INTERACTIVITY ======== -->
  <script>
    // Profile picture change (live preview)
    const profilePic = document.getElementById('profilePic');

    profilePic.addEventListener('click', () => {
      const input = document.createElement('input');
      input.type = 'file';
      input.accept = 'image/*';
      input.click();

      input.onchange = () => {
        const file = input.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            profilePic.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      };
    });

    // Greeting Message
    window.onload = () => {
      const name = document.getElementById('studentName').innerText;
      console.log(`Welcome back, ${name}!`);
    };
  </script>

</body>
</html>
