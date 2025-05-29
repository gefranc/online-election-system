<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['voter_id'])) {
    header("Location: ../login.php");
    exit();
}

$voter_id = $_SESSION['voter_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName  = $_POST['last_name'];
    $email     = $_POST['email'];

    // Handle profile photo upload
    $photoName = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['photo']['tmp_name'];
        $fileName = basename($_FILES['photo']['name']);
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowed)) {
            $photoName = uniqid('voter_', true) . '.' . $fileExt;
            move_uploaded_file($fileTmp, "../uploads/" . $photoName);
        }
    }

    // Update query
    if ($photoName) {
        $stmt = $conn->prepare("UPDATE voters SET FirstName = ?, LastName = ?, Email = ?, Photo = ? WHERE VoterID = ?");
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $photoName, $voter_id);
    } else {
        $stmt = $conn->prepare("UPDATE voters SET FirstName = ?, LastName = ?, Email = ? WHERE VoterID = ?");
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $voter_id);
    }

    if ($stmt->execute()) {
        $msg = "Profile updated successfully!";
    } else {
        $msg = "Error updating profile.";
    }
}

// Fetch voter data
$stmt = $conn->prepare("SELECT * FROM voters WHERE VoterID = ?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$result = $stmt->get_result();
$voter = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a202c;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .edit-box {
            background-color: #2d3748;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 6px;
            border: none;
        }
        .btn {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background-color: #38b2ac;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
        .message {
            text-align: center;
            margin-top: 10px;
            color: #f6e05e;
        }
        .a {
            display: block;
            text-align: center;
            margin-top: 10px;
            text-decoration: none;
            background-color: #e53e3e;
        }       
    </style>
</head>
<body>
    <div class="edit-box">
        <h2>Edit Your Profile</h2>
        <?php if ($msg): ?>
            <div class="message"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($voter['FirstName']) ?>" required>

            <label>Last Name</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($voter['LastName']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($voter['Email']) ?>" required>

            <label>Profile Picture</label>
            <input type="file" name="photo" accept="image/*">

            <button type="submit" class="btn">Update Profile</button> <br> 
            <a href="../dashboard.php"">
    <button type="button" class="btn">Cancel</button>
</a>
        </form>
    </div>
</body>
</html>
