<?php
session_start();
if (!isset($_SESSION['candidate_id'])) {
    header("Location: candidate_login.php");
    exit();
}
include '../config/config.php';

$candidateID = $_SESSION['candidate_id'];
$candidate = $conn->query("SELECT * FROM candidates WHERE CandidateID = '$candidateID'")->fetch_assoc();
$profilePhoto = $candidate['Photo'] ?? 'default.png';
$fullName = $candidate['FirstName'] . ' ' . $candidate['LastName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Candidate Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    :root {
      --bg-dark: #121212;
      --bg-light: #f9f9f9;
      --card-dark: #1e1e1e;
      --card-light: #fff;
      --text-light: #ffffff;
      --text-dark: #000000;
      --accent: #00bfff;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-dark);
      color: var(--text-light);
      transition: 0.3s ease;
      min-height: 100vh;
      overflow-x: hidden;
    }

    body.light-mode {
      background-color: var(--bg-light);
      color: var(--text-dark);
    }

    nav {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 35px 30px;
      background-color: var(--card-dark);
    }

    /* Logo on left */
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      color: white !important;
      flex-shrink: 0;
      z-index: 10;
    }

    /* Profile container - center horizontally & vertically */
    .profile {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      cursor: pointer;
      width: 80px;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 11; /* above nav background */
    }

    /* Profile image */
    .profile img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 2px solid #fff;
      object-fit: cover;
      display: block;
      transition: 0.3s ease;
    }

    /* Dropdown menu */
    .dropdown {
      position: absolute;
      top: 90px;
      right: 50%;
      transform: translateX(50%);
      background-color: #fff;
      color: #000;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      display: none;
      flex-direction: column;
      z-index: 1000;
      min-width: 160px;
      overflow: hidden;
    }

    body.light-mode .dropdown {
      background-color: #fff;
      color: #000;
    }

    body:not(.light-mode) .dropdown {
      background-color: #222;
      color: #fff;
    }

    .dropdown a {
      padding: 10px 20px;
      text-decoration: none;
      color: inherit;
      display: block;
      transition: background-color 0.2s;
    }

    .dropdown a:hover {
      background-color: #eee;
      color: #000;
    }

    body:not(.light-mode) .dropdown a:hover {
      background-color: #333;
      color: #fff;
    }

    /* Theme toggle button on the right */
    .theme-toggle {
      font-size: 1.4rem;
      background: none;
      border: 2px solid #fff;
      padding: 5px 10px;
      border-radius: 6px;
      color: white;
      cursor: pointer;
      flex-shrink: 0;
      z-index: 10;
      transition: 0.3s ease;
    }

    body.light-mode .theme-toggle {
      border-color: #000;
      color: #000;
    }

    main {
      padding: 20px;
      max-width: 1000px;
      margin: auto;
    }

    h2 {
      text-align: center;
      margin-top: 0;
    }

    .main-content {
      padding: 20px 0;
    }

    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .card {
      background-color: var(--card-dark);
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      cursor: pointer;
      transition: 0.3s ease;
      color: var(--text-light);
    }

    body.light-mode .card {
      background-color: var(--card-light);
      border: 2px solid #ccc;
      color: var(--text-dark);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 191, 255, 0.3);
    }

    .card h4 {
      margin: 10px 0;
      height: 50px;
    }

    .bar {
      height: 10px;
      width: 100%;
      background-color: var(--accent);
      margin-top: 10px;
      border-radius: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 600px) {
      nav {
        padding: 15px 15px;
      }

      .profile {
        width: 60px;
        height: 60px;
        top: 50%;
        transform: translate(-50%, -50%);
      }

      .profile img {
        width: 60px;
        height: 60px;
      }

      .dropdown {
        top: 70px;
        right: 50%;
        transform: translateX(50%);
        min-width: 140px;
      }

      .theme-toggle {
        padding: 4px 8px;
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>

  <nav>
    <div class="logo">🛡️ ChurchVote</div>

    <div class="profile" onclick="toggleDropdown()" aria-haspopup="true" aria-expanded="false">
      <img src="../admin/uploads/candidates/<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile" />
      <div class="dropdown" id="profileDropdown" role="menu" aria-label="Profile menu">
        <a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>

    <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
  </nav>

  <main>
    <h2>Welcome, <?php echo htmlspecialchars($fullName); ?></h2>
    <h4 style="text-align:center;">Church Board Election 2025</h4>

    <div class="main-content">
      <div class="card-container">
        <div class="card" onclick="location.href='tally.php'">
          <h4>Election Tally</h4>
          <div class="bar"></div>
        </div>

        <div class="card" onclick="location.href='result_position.php'">
          <h4>My Results</h4>
          <div class="bar"></div>
        </div>

        <div class="card" onclick="location.href='edit_profile.php'">
          <h4>Profile Settings</h4>
          <div class="bar"></div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Theme toggle with persistent state
    function toggleTheme() {
      const isLight = document.body.classList.toggle('light-mode');
      localStorage.setItem('theme', isLight ? 'light' : 'dark');
    }

    // Load theme on page load
    (function() {
      const theme = localStorage.getItem('theme');
      if (theme === 'light') {
        document.body.classList.add('light-mode');
      }
    })();

    // Dropdown toggle
    function toggleDropdown() {
      const drop = document.getElementById('profileDropdown');
      drop.style.display = drop.style.display === 'flex' ? 'none' : 'flex';
    }

    // Close dropdown if clicking outside
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.profile')) {
        document.getElementById('profileDropdown').style.display = 'none';
      }
    });
  </script>

</body>
</html>
