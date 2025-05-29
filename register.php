<?php
require 'config/config.php'; // DB connection

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Photo upload
    $photo = $_FILES['photo']['name'];
    $target = "uploads/" . basename($photo);
    move_uploaded_file($_FILES['photo']['tmp_name'], $target);

    // Insert into DB
    $sql = "INSERT INTO voters (FirstName, LastName, Email, Password, Photo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $photo);
    
    if ($stmt->execute()) {
        $msg = "Registration successful. You can now <a href='login.php'>login</a>.";
    } else {
        $msg = "Error: " . $stmt->error;
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
        a {
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
        <p class="msg"><?= $msg ?></p>
    </form>
</body>
</html>
