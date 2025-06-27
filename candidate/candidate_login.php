<?php
session_start();
require '../config/config.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "❌ Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM candidates WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['Password'])) {
                $_SESSION['candidate_id'] = $user['CandidateID'];
                $_SESSION['full_name'] = $user['FirstName'] . ' ' . $user['LastName'];
                header("Location: candidate_dashboard.php");
                exit;
            } else {
                $msg = "❌ Incorrect password.";
            }
        } else {
            $msg = "❌ Email not found in candidate records.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Candidate Login</title>
    <style>
        :root {
            --bg-color: #edf2f7;
            --card-bg: #ffffff;
            --text-color: #2d3748;
            --input-bg: #ffffff;
        }

        body.dark {
            --bg-color: #1a202c;
            --card-bg: #2d3748;
            --text-color: #e2e8f0;
            --input-bg: #4a5568;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', sans-serif;
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            transition: background 0.3s, color 0.3s;
        }

        .login-box {
            background: var(--card-bg);
            padding: 30px 40px;
            border-radius: 10px;
            width: 380px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            margin-top: 15px;
            display: block;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: var(--input-bg);
            color: var(--text-color);
            font-size: 15px;
        }

        input[type="submit"] {
            background-color: #3182ce;
            color: white;
            font-weight: bold;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 6px;
            margin-top: 25px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #2b6cb0;
        }

        .msg {
            margin-top: 15px;
            text-align: center;
            color: crimson;
        }

        .dark-toggle {
            position: absolute;
            top: 20px;
            right: 30px;
            background: none;
            border: 2px solid #ccc;
            border-radius: 20px;
            padding: 5px 15px;
            cursor: pointer;
            font-size: 14px;
            color: var(--text-color);
        }
    </style>
</head>
<body>
    <button class="dark-toggle" onclick="toggleTheme()">🌓</button>
    <form class="login-box" method="POST">
        <h2>Candidate Login</h2>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
        <button type="button" onclick="window.location.href='../index.php';" style="width:100%;margin-top:10px;background:#e53e3e;color:#fff;font-weight:bold;padding:10px;border:none;border-radius:6px;cursor:pointer;">
            Cancel
        </button>
        <div class="msg"><?= $msg ?></div>
    </form>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
        }

        function toggleTheme() {
            document.body.classList.toggle('dark');
            localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
        }
    </script>
</body>
</html>
