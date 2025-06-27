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
        $fileName = uniqid('photo_', true) . "_" . basename($_FILES["photo"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        $allowedExts = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExts)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
                $updateFields .= ", Photo='$fileName'";
            } else {
                $message = "❌ Failed to upload new photo.";
            }
        } else {
            $message = "❌ Invalid file format. Only JPG, JPEG, PNG allowed.";
        }
    }

    // Perform update
    if ($conn->query("UPDATE candidates SET $updateFields WHERE CandidateID='$candidateID'")) {
        $message = "✅ Profile updated successfully!";
        // Refresh candidate data
        $candidate = $conn->query("SELECT * FROM candidates WHERE CandidateID = '$candidateID'")->fetch_assoc();
    } else {
        $message = "❌ Update failed. Please try again.";
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

    .message {
      text-align: center;
      font-weight: bold;
      padding: 10px;
      border-radius: 5px;
    }

    .success { background: #204d20; color: #c8f7c5; }
    .error { background: #5a1a1a; color: #ffb3b3; }

    .profile-pic, #preview {
      display: block;
      margin: 10px auto;
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
    }

    #preview { display: none; }
  </style>
</head>
<body>

  <div class="container">
    <h2>Edit Profile</h2>

    <?php if ($message): ?>
      <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <?php
      $photoFile = (!empty($candidate['Photo']) && $candidate['Photo'] !== 'default.png')
        ? '../admin/uploads/candidates/' . $candidate['Photo']
        : '../admin/uploads/candidates/default.png';
    ?>
    <img class="profile-pic" id="currentPhoto" src="<?= htmlspecialchars($photoFile) ?>" alt="Current Photo">
    <img id="preview" alt="Preview Photo">

    <form method="POST" enctype="multipart/form-data">
      <label for="first_name">First Name</label>
      <input type="text" name="first_name" value="<?= htmlspecialchars($candidate['FirstName']) ?>" required>

      <label for="last_name">Last Name</label>
      <input type="text" name="last_name" value="<?= htmlspecialchars($candidate['LastName']) ?>" required>

      <label for="password">New Password (optional)</label>
      <input type="password" name="password" placeholder="Leave blank to keep current password">

      <label for="photo">Profile Photo</label>
      <input type="file" name="photo" accept=".jpg,.jpeg,.png" onchange="previewPhoto(event)">

      <button type="submit">Update Profile</button>
      <button type="button" onclick="window.location.href='candidate_dashboard.php'" style="background:#888; margin-bottom:10px;">Close</button>
    </form>
  </div>

  <script>
    function previewPhoto(event) {
      const preview = document.getElementById('preview');
      const file = event.target.files[0];
      const current = document.getElementById('currentPhoto');

      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
          if (current) current.style.display = 'none';
        };
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
      }
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
