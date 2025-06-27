<?php
require '../config/config.php';
$statusMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';

    if (!empty($fullName)) {
        $stmt = $conn->prepare("SELECT status FROM candidate_applications WHERE full_name = ?");
        $stmt->bind_param("s", $fullName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $status = strtolower($row['status']);

            if ($status === 'approved') {
                $statusMsg = "✅ Your application has been approved. Please await further instructions.";
            } elseif ($status === 'pending') {
                $statusMsg = "⌛ Your candidate application is still pending. Please check back later.";
            } elseif ($status === 'rejected') {
                $statusMsg = "❌ Unfortunately, your application was not approved.";
            } else {
                $statusMsg = "ℹ️ Your application status: " . htmlspecialchars($row['status']);
            }
        } else {
            $statusMsg = "❌ No matching record found. Please ensure your name is correct.";
        }

        $stmt->close();
    } else {
        $statusMsg = "❌ Please enter your full name.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Application Status</title>
    <style>
        :root {
            --bg-color: #f7fafc;
            --text-color: #333;
            --card-bg: #fff;
            --border-color: #ccc;
            --button-bg: #2b6cb0;
            --button-hover: #2c5282;
            --success-bg: #d4edda;
            --success-text: #155724;
            --error-bg: #f8d7da;
            --error-text: #721c24;
        }

        [data-theme='dark'] {
            --bg-color: #1a202c;
            --text-color: #f0f0f0;
            --card-bg: #2d3748;
            --border-color: #444;
            --button-bg: #4a90e2;
            --button-hover: #357ab8;
            --success-bg: #204d20;
            --success-text: #c8f7c5;
            --error-bg: #5a1a1a;
            --error-text: #ffb3b3;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        .box {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: background-color 0.3s, color 0.3s;
        }

        h2 {
            color: var(--button-bg);
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-color);
            font-size: 16px;
        }

        button {
            margin-top: 15px;
            padding: 10px;
            background: var(--button-bg);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
        }

        button:hover {
            background: var(--button-hover);
        }

        .cancel {
            background: #e53e3e;
            margin-top: 10px;
        }

        .cancel:hover {
            background: #c53030;
        }

        .msg {
            margin-top: 15px;
            font-weight: bold;
            padding: 10px;
            border-radius: 6px;
        }

        .msg.success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .msg.error {
            background: var(--error-bg);
            color: var(--error-text);
        }
    </style>
</head>
<body data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
    <div class="box">
        <h2>Check Candidate Application Status</h2>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Enter your full name" required>
            <button type="submit">Check Status</button>
            <button type="button" class="cancel" onclick="window.location.href='application.php';">Cancel</button>
        </form>
        <div class="msg <?= strpos($statusMsg, '✅') !== false ? 'success' : (strpos($statusMsg, '❌') !== false ? 'error' : '') ?>"><?= $statusMsg ?></div>
    </div>

    <script>
        // Theme handling - sync with application.php
        document.addEventListener("DOMContentLoaded", function() {
            const currentTheme = document.body.getAttribute('data-theme');
            // Theme is already set from PHP cookie detection
        });
    </script>
</body>
</html>
