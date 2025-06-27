<?php
include 'includes/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateId = intval($_POST['candidate_id']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format.";
    } elseif (strlen($password) < 6) {
        $message = "❌ Password should be at least 6 characters.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statement for security
        $stmt = $conn->prepare("UPDATE candidates SET Email = ?, Password = ? WHERE CandidateID = ?");
        $stmt->bind_param("ssi", $email, $hashedPassword, $candidateId);
        $success = $stmt->execute();

        $message = $success ? "✅ Candidate (ID: $candidateId) updated successfully!" : "❌ Failed to update candidate (ID: $candidateId).";
        $stmt->close();
    }
}

// Get candidates missing Email or Password (including empty strings)
$query = "SELECT * FROM candidates WHERE Email IS NULL OR Email = '' OR Password IS NULL OR Password = ''";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Existing Candidates</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #2b6cb0;
            --success: #38a169;
            --danger: #e53e3e;
            --text-light: #2d3748;
            --text-dark: #edf2f7;
            --bg-light: #f8fafc;
            --bg-dark: #1a202c;
            --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .header {
            background: var(--primary);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }

        .success-msg {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid var(--success);
        }

        .error-msg {
            background-color: #fed7d7;
            color: #742a2a;
            border: 1px solid var(--danger);
        }

        form {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
        }

        .candidate-name {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 1.2em;
            color: var(--primary);
        }

        label {
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            margin-top: 15px;
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        button:hover {
            background-color: #2c5282;
        }

        .no-candidates {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #444;
            box-shadow: var(--card-shadow);
        }

        a.back-link {
            display: inline-block;
            margin-bottom: 15px;
            text-decoration: none;
            color: var(--primary);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="header">
            <h2>Update Existing Candidates</h2>
            <p>Fill in missing Email and Password for login functionality.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success-msg' : 'error-msg' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <form method="POST">
                    <input type="hidden" name="candidate_id" value="<?= $row['CandidateID'] ?>">
                    <div class="candidate-name">
                        <?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?> (ID: <?= $row['CandidateID'] ?>)
                    </div>

                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($row['Email']) ?>" required>

                    <label>Default Password:</label>
                    <input type="text" name="password" value="Candidate@2025" required>

                    <button type="submit">Update Candidate</button>
                </form>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-candidates">
                <h3>✅ All Candidates Updated</h3>
                <p>No candidates missing email or password.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
