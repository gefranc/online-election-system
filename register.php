<?php
require 'config/config.php';

$msg = "";

function is_valid_name($name) {
    return preg_match("/^[a-zA-Z ]{2,}$/", $name);
}

function is_valid_password($pass) {
    return preg_match("/^[a-zA-Z0-9]{6,}$/", $pass);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $photo = $_FILES['photo'];

    $fullName = $firstName . ' ' . $lastName;

    // Validate names
    if (!is_valid_name($firstName) || !is_valid_name($lastName)) {
        $msg = "❌ First and Last Name must be at least 2 letters long and contain only letters.";
    }
    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "❌ Invalid email format.";
    }
    // Validate password
    elseif (!is_valid_password($password)) {
        $msg = "❌ Password must be at least 6 characters long and contain only letters and numbers.";
    }
    // Check if member exists
    else {
        $checkMember = $conn->prepare("SELECT * FROM church_members WHERE full_name = ? AND is_active = 1");
        $checkMember->bind_param("s", $fullName);
        $checkMember->execute();
        $result = $checkMember->get_result();

        if ($result->num_rows === 0) {
            $msg = "❌ Only active church members can register as voters.";
        } else {
            // Check if email already used
            $checkEmail = $conn->prepare("SELECT * FROM voters WHERE Email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            if ($checkEmail->get_result()->num_rows > 0) {
                $msg = "❌ This email is already registered.";
            } else {
                // Handle photo upload
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($photo['type'], $allowedTypes)) {
                    $msg = "❌ Photo must be a JPG or PNG image.";
                } elseif ($photo['size'] > 2 * 1024 * 1024) { // 2MB max
                    $msg = "❌ Photo must be less than 2MB.";
                } else {
                    $photoName = time() . "_" . basename($photo['name']);
                    $target = "uploads/" . $photoName;
                    move_uploaded_file($photo['tmp_name'], $target);

                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $sql = "INSERT INTO voters (FirstName, LastName, Email, Password, Photo) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $photoName);
                    
                    if ($stmt->execute()) {
                        $msg = "<span style='color: #90ee90;'>✅ Registration successful. You can now <a href='login.php'>login</a>.</span>";
                    } else {
                        $msg = "❌ Database error: " . $stmt->error;
                    }
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voter Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background-color: #2d3748;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            width: 400px;
        }
        .form-box h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        input, label {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            border: none;
            border-radius: 6px;
        }
        input[type="submit"] {
            background-color: #4fd1c5;
            color: #1a202c;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        input[type="submit"]:hover {
            background-color: #38b2ac;
        }
        .msg {
            margin-top: 15px;
            color: #fbb6ce;
            text-align: center;
        }
        .msg a {
            color: #90cdf4;
        }
    </style>
</head>
<body>
    <form class="form-box" method="post" enctype="multipart/form-data">
        <h2>Voter Registration</h2>
        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Photo</label>
        <input type="file" name="photo" accept="image/*" required>

        <input type="submit" value="Register">
        <button type="button" onclick="window.location.href='index.php';" style="width:100%;margin-top:10px;background:#e53e3e;color:#fff;font-weight:bold;padding:10px;border:none;border-radius:6px;cursor:pointer;">
            Cancel
        </button>
        <p class="msg"><?= $msg ?></p>
    </form>
</body>
</html>
