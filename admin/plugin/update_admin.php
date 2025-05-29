<?php
require_once '../../admin/includes/config.php';
session_start();

// Fetch admin data
$adminID = $_SESSION['admin_id'] ?? null;

if (!$adminID) {
    header('Location: ../admin_login.php');
    exit();
}

// Fetch current admin info
$stmt = $conn->prepare("SELECT * FROM admin WHERE AdminID = ?");
$stmt->bind_param("i", $adminID);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    // Profile picture upload
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../uploads/admin_photos/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // create dir if missing
        }
        $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $photoName;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            $stmt = $conn->prepare("UPDATE admin SET Username = ?, Photo = ? WHERE AdminID = ?");
            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }
            $stmt->bind_param("ssi", $name, $photoName, $adminID);
        } else {
            die("Failed to upload photo.");
        }
    } else {
        $stmt = $conn->prepare("UPDATE admin SET Username = ? WHERE AdminID = ?");
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }
        $stmt->bind_param("si", $name, $adminID);
    }

    $stmt->execute();
    header("Location: admin_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-lg rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Admin Profile</h4>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="text-center mb-3">
                    <img src="../uploads/admin_photos/<?= htmlspecialchars($admin['Photo'] ?? 'default.png') ?>" alt="Profile" class="profile-img mb-2">
                    <div>
                        <input type="file" name="photo" class="form-control form-control-sm mt-2" accept="image/*">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($admin['Username']) ?>" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="change_password.php" class="btn btn-outline-warning ms-2">Change Password</a>
                <a href="../dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
    // Theme handling from localStorage
    document.addEventListener("DOMContentLoaded", () => {
        const theme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-bs-theme", theme);
    });

    function toggleTheme() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute("data-bs-theme");
        const newTheme = currentTheme === "light" ? "dark" : "light";
        html.setAttribute("data-bs-theme", newTheme);
        localStorage.setItem("theme", newTheme);
    }
</script>
</body>
</html>
