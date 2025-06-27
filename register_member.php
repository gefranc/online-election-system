<?php
require 'config/config.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $ministry = trim($_POST['ministry']);
    $membership_date = date('Y-m-d'); // Default to today

    // Validate full name
    if (!preg_match("/^[a-zA-Z ]{2,}$/", $fullName)) {
        $msg = "❌ Full name must be letters only and at least 2 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "❌ Invalid email format.";
    } else {
        // Photo upload
        $photo = $_FILES['photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $photoName = time() . "_" . basename($photo['name']);
        $target = "uploads/" . $photoName;

        if (!in_array($photo['type'], $allowedTypes)) {
            $msg = "❌ Photo must be JPG or PNG.";
        } elseif ($photo['size'] > 2 * 1024 * 1024) {
            $msg = "❌ Photo must be less than 2MB.";
        } elseif (!move_uploaded_file($photo['tmp_name'], $target)) {
            $msg = "❌ Failed to upload photo.";
        } else {
            // Check if already exists
            $check = $conn->prepare("SELECT * FROM church_members WHERE full_name = ?");
            $check->bind_param("s", $fullName);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $msg = "❌ Member already exists.";
            } else {
                $stmt = $conn->prepare("INSERT INTO church_members (full_name, email, phone_number, membership_date, ministry, photo, is_active) VALUES (?, ?, ?, ?, ?, ?, 0)");
                $stmt->bind_param("ssssss", $fullName, $email, $phone, $membership_date, $ministry, $photoName);
                if ($stmt->execute()) {
                    $msg = "✅ Member registered successfully. Awaiting approval.";
                } else {
                    $msg = "❌ Database error: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Church Member</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #edf2f7, #cbd5e0);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            width: 450px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2d3748;
        }
        label {
            display: block;
            margin-top: 15px;
            color: #2d3748;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background: #2b6cb0;
            color: #fff;
            font-weight: bold;
            margin-top: 20px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #2c5282;
        }
        .msg {
            text-align: center;
            margin-top: 15px;
            color: #e53e3e;
        }
    </style>
</head>
<body>
    <form class="form-box" method="POST" enctype="multipart/form-data">
        <h2>Register New Member</h2>
        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Ministry</label>
        <input type="text" name="ministry" placeholder="e.g. Youth, Choir, Ushers" required>

        <label>Upload Photo</label>
        <input type="file" name="photo" accept="image/*" required>

        <input type="submit" value="Register">
        <button type="button" onclick="window.location.href='index.php';" style="width:100%;margin-top:10px;background:#e53e3e;color:#fff;font-weight:bold;padding:10px;border:none;border-radius:6px;cursor:pointer;">
            Cancel
        </button>
        <button type="button" onclick="window.location.href='check_status.php';" style="width:100%;margin-top:10px;background:#38a169;color:#fff;font-weight:bold;padding:10px;border:none;border-radius:6px;cursor:pointer;">
            Check Status
        </button>
        <div class="msg"><?= $msg ?></div>
    </form>
</body>
</html>
