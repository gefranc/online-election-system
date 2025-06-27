            
<?php
require 'includes/config.php';

$msg = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $applicationId = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $appQuery = $conn->prepare("SELECT * FROM candidate_applications WHERE id = ? AND status = 'pending'");
        $appQuery->bind_param("i", $applicationId);
        $appQuery->execute();
        $resultApp = $appQuery->get_result();

        if ($resultApp->num_rows === 1) {
            $app = $resultApp->fetch_assoc();

            // Extract first and last name from full name
            $nameParts = explode(" ", $app['full_name'], 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
            
            // Get other application details
            $email = $app['email'];
            $positionId = $app['position_id'];
            $photo = $app['photo'];

            $defaultPassword = "Candidate@2025";
            $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);

            $insert = $conn->prepare("INSERT INTO candidates (FirstName, LastName, Email, Password, Photo, PositionID, Status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $insert->bind_param("sssssi", $firstName, $lastName, $email, $hashedPassword, $photo, $positionId);

            if ($insert->execute()) {
                $update = $conn->prepare("UPDATE candidate_applications SET status = 'approved' WHERE id = ?");
                $update->bind_param("i", $applicationId);
                $update->execute();
                $msg = "✅ Candidate approved and added successfully.";
            } else {
                $msg = "❌ Error inserting into candidates: " . $insert->error;
            }
        } else {
            $msg = "❌ Invalid application or already approved.";
        }
    } elseif ($action === 'reject') {
        $update = $conn->prepare("UPDATE candidate_applications SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $applicationId);
        if ($update->execute()) {
            $msg = "❌ Candidate application rejected.";
        } else {
            $msg = "❌ Error rejecting application.";
        }
    } elseif ($action === 'deactivate') {
        // Fetch email from application
        $getApp = $conn->prepare("SELECT email FROM candidate_applications WHERE id = ?");
        $getApp->bind_param("i", $applicationId);
        $getApp->execute();
        $appResult = $getApp->get_result();

        if ($appResult->num_rows === 1) {
            $email = $appResult->fetch_assoc()['email'];

            // Deactivate candidate and revert application status
            $deactivate = $conn->prepare("UPDATE candidates SET Status = 'inactive' WHERE Email = ?");
            $deactivate->bind_param("s", $email);

            $revert = $conn->prepare("UPDATE candidate_applications SET status = 'pending' WHERE id = ?");
            $revert->bind_param("i", $applicationId);

            if ($deactivate->execute() && $revert->execute()) {
                $msg = "⚠️ Candidate has been deactivated and application set to pending.";
            } else {
                $msg = "❌ Failed to deactivate candidate.";
            }
        } else {
            $msg = "❌ Application not found.";
        }
    }
}

// Fetch all applications with position name
$result = $conn->query("SELECT ca.*, p.PositionName as position_name FROM candidate_applications ca JOIN positions p ON ca.position_id = p.PositionID ORDER BY ca.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Candidate Applications</title>
    <style>
        :root {
            --bg-color: #f4f4f9;
            --text-color: #333;
            --input-bg: #fff;
            --box-shadow: rgba(0, 0, 0, 0.1);
        }
        body.dark {
            --bg-color: #1f1f1f;
            --text-color: #f1f1f1;
            --input-bg: #2c2c2c;
            --box-shadow: rgba(255, 255, 255, 0.1);
        }
        body {
            font-family: "Segoe UI", sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 40px;
            transition: background 0.3s, color 0.3s;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            color: white;
            padding: 12px;
            border-radius: 6px;
        }
        .success { background: #28a745; }
        .error { background: #dc3545; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--input-bg);
            box-shadow: 0 0 15px var(--box-shadow);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }
        th {
            background: #2c5282;
            color: white;
        }
        tr:hover { background: #edf2f7; }
        .dark tr:hover { background: #3a3a3a; }
        .photo {
            width: 50px;
            height: 50px;
            border-radius: 50px;
            object-fit: cover;
            display: flex;
            justify-content: center;
            
        }
        .btn {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 14px;
        }
        .approve { background: #38a169; }
        .reject { background: #e53e3e; }
        .status {
            font-weight: bold;
            text-transform: capitalize;
        }
        .pending { color: orange; }
        .approved { color: green; }
        .rejected { color: red; }
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            td {
                margin-bottom: 10px;
            }
            
        }
    </style>
</head>
<body>
    <h2>Candidate Applications Management</h2>

    <div style="display:flex; justify-content:flex-end; text-align: center; margin-bottom: 20px;">
        <a href="dashboard.php" class="btn" style="background: #4a5568; text-decoration: none;">← Back</a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="message <?= strpos($msg, '✅') !== false || strpos($msg, '⚠️') !== false ? 'success' : 'error' ?>">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Full Name</th>
                <th>Member ID</th>
                <th>Position</th>
                <th>Ministry</th>
                <th>Reason</th>
                <th>Start Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['photo'])): ?>
                            <img src="uploads/candidates/<?= htmlspecialchars($row['photo']) ?>" alt="Photo" class="photo">
                        <?php else: ?>
                            <span>No Photo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['member_id']) ?></td>
                    <td><?= htmlspecialchars($row['position_name']) ?></td>
                    <td><?= htmlspecialchars($row['ministry']) ?></td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td><?= htmlspecialchars($row['service_start_date']) ?></td>
                    <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <a href="?action=approve&id=<?= $row['id'] ?>" class="btn approve">Approve</a>
                            <a href="?action=reject&id=<?= $row['id'] ?>" class="btn reject">Reject</a>
                        <?php elseif ($row['status'] === 'approved'): ?>
                            <a href="?action=deactivate&id=<?= $row['id'] ?>" class="btn reject">Deactivate</a>
                        <?php else: ?>
                            <em>N/A</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark');
        }
    </script>
</body>
</html>
