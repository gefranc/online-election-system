<?php
session_start();
include 'includes/config.php';

// Join with positions table to get position names
$result = $conn->query("
    SELECT c.CandidateID, c.FirstName, c.LastName, c.Photo, c.Status, 
           p.PositionName, p.PositionID
    FROM candidates c
    JOIN positions p ON c.PositionID = p.PositionID
    ORDER BY p.PositionID, c.LastName
");


?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates</title>
    <style>
        :root {
            --bg-color: #f4f4f4;
            --text-color: #000;
            --card-bg: #fff;
            --header-bg: #333;
            --thead-bg: #e0e0e0;
            --thead-text: #000;
            --active-color: #4CAF50;
            --inactive-color: #f44336;
        }

        [data-theme="dark"] {
            --bg-color: #1e1e1e;
            --text-color: #fff;
            --card-bg: #2c2c2c;
            --header-bg: #222;
            --thead-bg: #444;
            --thead-text: #fff;
            --active-color: #388E3C;
            --inactive-color: #D32F2F;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .header {
            background: var(--header-bg);
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-content {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            table-layout: auto;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: left;
            vertical-align: middle;
            white-space: normal;
            width: auto;
        }

        th {
            background-color: var(--thead-bg);
            color: var(--thead-text);
        }
        

        .dark-mode-toggle {
            background: none;
            border: 1px solid #fff;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }

        .status-active {
            color: var(--active-color);
            font-weight: bold;
        }

        .status-inactive {
            color: var(--inactive-color);
            font-weight: bold;
        }

        .action-btns {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            color: white;
        }

        .edit-btn {
            background-color: #2196F3;
        }

        .delete-btn {
            background-color: #f44336;
        }

        td img {
            width: 80px; height: 80px; border-radius: 50%; object-fit: cover; 
        }

        .action-btn-wrapper {
            display: flex;
            align-items: center;
            gap: 5px;
            height: 100px;
        }
    </style>
</head>
<body onload="applyTheme()">

<div class="header">
    <h2>🗳️ Candidates List</h2>
    <div>
        <button onclick="location.href='plugin/add_candidate.php'" class="dark-mode-toggle">➕ Add Candidate</button>
        <button onclick="location.href='dashboard.php'" class="dark-mode-toggle">← Go Back</button>
    </div>
</div>

<div class="main-content">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Position</th>
                <th>Status</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['CandidateID'] ?></td>
                <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                <td><?= htmlspecialchars($row['PositionName']) ?></td>
                <td class="<?= $row['Status'] === 'Active' ? 'status-active' : 'status-inactive' ?>">
                    <?= htmlspecialchars($row['Status']) ?>
                </td>
                <td>
                    <?php if (!empty($row['Photo'])): ?>
                        <img src="uploads/candidates/<?= htmlspecialchars($row['Photo']) ?>" alt="Photo"> 
                    <?php else: ?>
                        <span>No photo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-btn-wrapper">
                        <button onclick="window.location.href='plugin/edit_candidate.php?id=<?= $row['CandidateID'] ?>'" class="action-btn edit-btn">Edit</button>
                        <button onclick="confirmDelete(<?= $row['CandidateID'] ?>)" class="action-btn delete-btn">Delete</button>
                    </div>
                </div>
                </td>

            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleDarkMode() {
        const html = document.documentElement;
        const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    }

    function applyTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    }

    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this candidate?')) {
            window.location.href = 'plugin/delete_candidate.php?id=' + id;
        }
    }
</script>

</body>
</html>   