<?php
require_once '../includes/config.php';
session_start();

$adminID = $_SESSION['admin_id'] ?? null;

if (!$adminID) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = $_POST['current_password'];
    $newPass = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];

    // Fetch current hashed password
    $stmt = $conn->prepare("SELECT Password FROM admin WHERE AdminID = ?");
    $stmt->bind_param("i", $adminID);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($currentPass, $hashedPassword)) {
        $error = "Current password is incorrect.";
    } elseif ($newPass !== $confirmPass) {
        $error = "New passwords do not match.";
    } elseif (strlen($newPass) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $newHashed = password_hash($newPass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE admin SET Password = ? WHERE AdminID = ?");
        $stmt->bind_param("si", $newHashed, $adminID);
        $stmt->execute();
        $success = "Password updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-lg rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Change Password</h4>
            <button class="btn btn-sm btn-outline-secondary" onclick="toggleTheme()">Toggle Theme</button>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
                <a href="admin_profile.php" class="btn btn-secondary ms-2">Back to Profile</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const theme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-bs-theme", theme);
    });

    function toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute("data-bs-theme");
        const next = current === "light" ? "dark" : "light";
        html.setAttribute("data-bs-theme", next);
        localStorage.setItem("theme", next);
    }
</script>
</body>
</html>
