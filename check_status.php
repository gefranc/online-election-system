<?php
require 'config/config.php';
$statusMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $stmt = $conn->prepare("SELECT is_active FROM church_members WHERE full_name = ?");
    $stmt->bind_param("s", $fullName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $statusMsg = $row['is_active']
            ? "✅ Your membership has been approved. You can now proceed to voter registration.<br><br><button onclick=\"window.location.href='register.php'\" style='background:#2b6cb0;color:#fff;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;'>Register</button>"
            : "⌛ Your application is still pending. Please check back later.";
    } else {
        $statusMsg = "❌ No matching record found. Please ensure your name is correct.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Approval Status</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 15px;
            padding: 10px;
            background: #2b6cb0;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .msg {
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>Check Your Membership Status</h2>
    <form method="POST">
        <input type="text" name="full_name" placeholder="Enter your full name" required>
        <button type="submit">Check Status</button>
        <button type="button" onclick="window.location.href='index.php';" style="background:#e53e3e;margin-top:10px;">Cancel</button>
    </form>
    <div class="msg"><?= $statusMsg ?></div>
</div>
</body>
</html>