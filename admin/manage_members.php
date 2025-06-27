<?php
require 'includes/config.php';

$msg = "";

// Handle activation/deactivation
if (isset($_GET['action']) && isset($_GET['id'])) {
    $memberId = intval($_GET['id']);
    $newStatus = $_GET['action'] === 'approve' ? 1 : 0;

    $stmt = $conn->prepare("UPDATE church_members SET is_active = ? WHERE member_id = ?");
    $stmt->bind_param("ii", $newStatus, $memberId);
    if ($stmt->execute()) {
        $msg = $newStatus ? "✅ Member approved." : "⚠️ Member deactivated.";
    } else {
        $msg = "❌ Error updating member.";
    }
}

// Fetch all members
$result = $conn->query("SELECT * FROM church_members ORDER BY membership_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Church Members</title>
    <style>
    :root {
        --primary: #2b6cb0;
        --primary-dark: #2c5282;
        --success: #38a169;
        --success-light: #c6f6d5;
        --success-dark: #276749;
        --danger: #e53e3e;
        --danger-light: #fed7d7;
        --danger-dark: #742a2a;
        --text-light: #2d3748;
        --text-dark: #edf2f7;
        --bg-light: #f8fafc;
        --bg-dark: #1a202c;
        --border-light: #e2e8f0;
        --border-dark: #4a5568;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        padding: 20px;
        transition: background 0.3s ease, color 0.3s ease;
    }

    body.light-theme {
        background: var(--bg-light);
        color: var(--text-light);
    }

    body.dark-theme {
        background: var(--bg-dark);
        color: var(--text-dark);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow);
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
    }

    .msg {
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 6px;
        font-weight: 500;
    }

    .success-msg {
        background-color: var(--success-light);
        color: var(--success-dark);
    }

    .error-msg {
        background-color: var(--danger-light);
        color: var(--danger-dark);
    }

    .dark-theme .success-msg {
        background-color: var(--success-dark);
        color: var(--success-light);
    }

    .dark-theme .error-msg {
        background-color: var(--danger-dark);
        color: var(--danger-light);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        border-radius: 8px;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
    }

    th {
        background-color: var(--primary);
        color: white;
        font-weight: 600;
        position: sticky;
        top: 0;
    }

    tr:nth-child(even) {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .dark-theme tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }

    .status.active {
        background-color: var(--success-light);
        color: var(--success-dark);
    }

    .status.inactive {
        background-color: var(--danger-light);
        color: var(--danger-dark);
    }

    .dark-theme .status.active {
        background-color: var(--success-dark);
        color: var(--success-light);
    }

    .dark-theme .status.inactive {
        background-color: var(--danger-dark);
        color: var(--danger-light);
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .approve {
        background-color: var(--success);
        color: white;
    }

    .deactivate {
        background-color: var(--danger);
        color: white;
    }

    .member-photo, .no-photo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .no-photo {
        background-color: #eee;
        color: #888;
    }

    .dark-theme .no-photo {
        background-color: #333;
        color: #aaa;
    }

    td a, td a:visited {
        color: inherit;
        text-decoration: underline;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
        
        th, td {
            padding: 8px 10px;
        }
        
        .btn {
            padding: 8px 15px;
            font-size: 14px;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Church Members Management</h1>
            <a href="dashboard.php" class="btn btn-primary">
                &larr; Back
            </a>
        </div>

        <?php if ($msg): ?>
            <div class="msg <?= strpos($msg, '❌') !== false ? 'error-msg' : 'success-msg' ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Ministry</th>
                        <th>Membership Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['photo'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" alt="Member Photo" class="member-photo">
                                <?php else: ?>
                                    <div class="no-photo">No Photo</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><?= htmlspecialchars($row['ministry']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['membership_date'])) ?></td>
                            <td>
                                <span class="status <?= $row['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['is_active']): ?>
                                    <button onclick="window.location.href='?action=deactivate&id=<?= $row['member_id'] ?>'" class="action-btn deactivate">Deactivate</button>
                                <?php else: ?>
                                    <button onclick="window.location.href='?action=approve&id=<?= $row['member_id'] ?>'" class="action-btn approve">Approve</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Theme handling
            const theme = localStorage.getItem('theme') || 'light';
            document.body.classList.add(theme + '-theme');
            
            // Confirm before deactivating
            const deactivateButtons = document.querySelectorAll('.deactivate');
            deactivateButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to deactivate this member?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>