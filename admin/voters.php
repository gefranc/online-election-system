<?php
require_once 'includes/config.php';

// Fetch all voters
$result = $conn->query("SELECT * FROM voters ORDER BY VoterID DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voters</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 2rem;
        }
        [data-bs-theme='dark'] {
            background-color: #121212;
            color: #f1f1f1;
        }
        td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between mb-4 align-items-center">
        <h2>Voters List</h2>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Photo</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($voter = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $voter['VoterID'] ?></td>
                    <td>
                        <?php if (!empty($voter['Photo'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($voter['Photo']) ?>" alt="Photo" width="40" height="40" class="rounded-circle">
                        <?php else: ?>
                            <span>No Photo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($voter['FirstName'] . ' ' . $voter['LastName']) ?></td>
                    <td><?= htmlspecialchars($voter['Email']) ?></td>
                    <td>
                        <span class="badge bg-<?= $voter['Status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= $voter['Status'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="plugin/edit_voter.php?id=<?= $voter['VoterID'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <?php if ($voter['Status'] === 'active'): ?>
                            <a href="plugin/deactivate_voter.php?id=<?= $voter['VoterID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to deactivate this voter?')">Delete</a>
                        <?php else: ?>
                            <a href="plugin/reactivate_voter.php?id=<?= $voter['VoterID'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure to reactivate this voter?')">Reactivate</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    // Sync with localStorage theme
    document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
</script>
</body>
</html>
