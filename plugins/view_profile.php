<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['voter_id'])) {
    header("Location: ../login.php");
    exit();
}

$voter_id = $_SESSION['voter_id'];

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
    <title>View Profile</title>
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
        .profile-box {
            background-color: #2d3748;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f6ad55;
            margin-bottom: 15px;
        }
        .profile-detail {
            margin: 10px 0;
            text-align: left;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #90cdf4;
        }
    </style>
</head>
<body>
    <div class="profile-box">
        <h2>Your Profile</h2>
        <?php if (!empty($voter['Photo']) && file_exists("../uploads/" . $voter['Photo'])): ?>
            <img src="../uploads/<?= htmlspecialchars($voter['Photo']) ?>" alt="Profile Picture" class="profile-img">
        <?php else: ?>
            <img src="../uploads/default.png" alt="Default Picture" class="profile-img">
        <?php endif; ?>
        <div class="profile-detail"><strong>Name:</strong> <?= htmlspecialchars($voter['FirstName']) ?> <?= htmlspecialchars($voter['LastName']) ?></div>
        <div class="profile-detail"><strong>Email:</strong> <?= htmlspecialchars($voter['Email']) ?></div>
        <div class="profile-detail"><strong>Status:</strong> <?= htmlspecialchars($voter['Status']) ?></div>
        <a href="edit_profile.php">Edit Profile</a> | <a href="../dashboard.php">Back to Dashboard</a>
    </div>
    
</body>
</html>
