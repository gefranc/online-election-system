<?php
require_once 'includes/config.php';

// Fetch distinct voters who have voted
$query = "
    SELECT v.VoterID, v.FirstName, v.LastName, MAX(vo.VoteDate) AS LastVoted 
    FROM votes vo
    INNER JOIN voters v ON v.VoterID = vo.VoterID
    GROUP BY vo.VoterID
    ORDER BY LastVoted DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voters Who Voted</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 2rem;
        }
        [data-bs-theme='dark'] {
            background-color: #121212;
            color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Voters Who Have Voted</h2>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Voter ID</th>
                    <th>Full Name</th>
                    <th>Last Vote Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['VoterID'] ?></td>
                            <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                            <td><?= date("Y-m-d H:i:s", strtotime($row['LastVoted'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No voters have voted yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <script>
        // Theme persistence using localStorage
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>
