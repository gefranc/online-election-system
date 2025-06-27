<?php
session_start();
require 'config/config.php'; // DB connection

$msg = "";

if (isset($_SESSION['voter_id']) && !empty($_SESSION['voter_id'])) {
    header("Location: dashboard.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM voters WHERE Email = ? AND Status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            $_SESSION['voter_id'] = $row['VoterID'];
            $_SESSION['voter_name'] = $row['FirstName'];
            header("Location: dashboard.php");
            exit();
        } else {
            $msg = "Incorrect password.";
        }
    } else {
        $msg = "Account not found or inactive.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voter Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #ed64a6, #805ad5);
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
            background-color: #f6ad55;
            color: #1a202c;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        input[type="submit"]:hover {
            background-color: #dd6b20;
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
    <form class="form-box" method="post">
        <h2>Voter Login</h2>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <input type="submit" value="Login">
        <p class="msg"><?= $msg ?></p>
        <p style="text-align:center;">Don't have an account? <a href="register.php">Register here</a></p>
        <p style="text-align:center;margin-top:20px;">
        <a href="candidate/candidate_login.php">Login as Candidate?</a>
        </p>
        
    </form>
</body>
</html>
