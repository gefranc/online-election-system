<?php
session_start();
if (!isset($_SESSION['candidate_id'])) {
    header("Location: candidate_login.php");
    exit();
}
include '../config/config.php';

$candidateID = $_SESSION['candidate_id'];
$message = '';

// Fetch candidate info
$query = $conn->query("SELECT * FROM candidates WHERE CandidateID = '$candidateID'");
$candidate = $query->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $updateFields = "FirstName='$firstName', LastName='$lastName'";

    // Update password only if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $updateFields .= ", Password='$password'";
    }

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../admin/uploads/candidates/";
        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
            // Delete old photo if it's not default
            if (!empty($candidate['Photo']) && $candidate['Photo'] !== 'default.png') {
                $oldPath = $targetDir . $candidate['Photo'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Save only file name
            $updateFields .= ", Photo='$fileName'";
        }
    }

    // Perform update
    if ($conn->query("UPDATE candidates SET $updateFields WHERE CandidateID='$candidateID'")) {
        $message = "Profile updated successfully. Redirecting...";
        echo "<script>setTimeout(function(){ window.location.href='candidate_dashboard.php'; }, 2000);</script>";
    } else {
        $message = "Update failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --bg-dark: #121212;
      --bg-light: #f9f9f9;
      --card-dark: #1e1e1e;
      --card-light: #ffffff;
      --text-light: #ffffff;
      --text-dark: #000000;
      --accent: #00bfff;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background-color: var(--bg-dark);
      color: var(--text-light);
      transition: 0.3s ease;
    }

    body.light-mode {
      background-color: var(--bg-light);
      color: var(--text-dark);
    }

    .container {
      max-width: 500px;
      margin: 40px auto;
      padding: 20px;
      background: var(--card-dark);
      border-radius: 10px;
    }

    body.light-mode .container {
      background-color: var(--card-light);
    }

    h2 {
      text-align: center;
    }

    label {
      display: block;
      margin: 15px 0 5px;
    }

    input, button {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: none;
      margin-bottom: 15px;
    }

    input[type="file"] {
      padding: 5px;
    }

    button {
      background-color: var(--accent);
      color: #fff;
      font-weight: bold;
      cursor: pointer;
    }

    .theme-toggle {
      position: fixed;
      top: 20px;
      right: 20px;
      font-size: 1.2rem;
      background: none;
      border: 2px solid white;
      padding: 5px 10px;
      border-radius: 6px;
      color: white;
      cursor: pointer;
    }

    body.light-mode .theme-toggle {
      border-color: black;
      color: black;
    }

    .message {
      text-align: center;
      font-weight: bold;
      color: limegreen;
    }

    .profile-pic {
      display: block;
      margin: 10px auto;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
    }
  </style>
</head>
<body>

  <!-- <button class="theme-toggle" onclick="toggleTheme()">🌙</button> -->

  <div class="container">
    <h2>Edit Profile</h2>

    <?php if ($message): ?>
      <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php
      $photoPath = (!empty($candidate['Photo']) && $candidate['Photo'] !== 'default.png')
        ? '../admin/uploads/candidates/' . $candidate['Photo']
        : '../admin/uploads/candidates/default.png';
    ?>
    <img class="profile-pic" src="<?php echo htmlspecialchars($photoPath); ?>" alt="Profile Photo">

    <form method="POST" enctype="multipart/form-data">
      <label for="first_name">First Name</label>
      <input type="text" name="first_name" value="<?php echo htmlspecialchars($candidate['FirstName']); ?>" required>

      <label for="last_name">Last Name</label>
      <input type="text" name="last_name" value="<?php echo htmlspecialchars($candidate['LastName']); ?>" required>

      <label for="password">New Password (optional)</label>
      <input type="password" name="password" placeholder="Leave blank to keep current password">

      <label for="photo">Profile Photo</label>
      <input type="file" name="photo" accept="image/*">

      <button type="submit">Update Profile</button>
    </form>
  </div>

  <script>
    function toggleTheme() {
      const isLight = document.body.classList.toggle('light-mode');
      localStorage.setItem('theme', isLight ? 'light' : 'dark');
    }

    (function() {
      const theme = localStorage.getItem('theme');
      if (theme === 'light') {
        document.body.classList.add('light-mode');
      }
    })();
  </script>

</body>
</html>
