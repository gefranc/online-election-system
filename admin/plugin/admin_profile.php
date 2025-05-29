<?php
session_start();
require_once '../includes/config.php';

$adminID = $_SESSION['admin_id'] ?? null;

if (!$adminID) {
    header("Location: ../admin_login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM admin WHERE AdminID = ?");
$stmt->bind_param("i", $adminID);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile View</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ccc;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Admin Profile</h4>
            <button onclick="location.href='../dashboard.php'" class="btn btn-sm btn-outline-secondary">← Dashboard</button>
        </div>
        <div class="card-body text-center">
            <img src="../uploads/admin_photos/<?= htmlspecialchars($admin['Photo'] ?? 'default.png') ?>" alt="Admin Photo" class="profile-img mb-3">
            <h5 class="card-title"><?= htmlspecialchars($admin['Username']) ?></h5>
            <p class="text-muted">Admin ID: <?= $admin['AdminID'] ?></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const theme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-bs-theme", theme);
    });
</script>
</body>
</html>
