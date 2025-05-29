<?php
require_once 'includes/config.php';

// Fetch full ballot records
$query = "
    SELECT vo.VoteID, vo.VoteDate, 
           v.FirstName AS VoterFirst, v.LastName AS VoterLast,
           c.FirstName AS CandidateFirst, c.LastName AS CandidateLast,
           p.PositionName
    FROM votes vo
    INNER JOIN voters v ON v.VoterID = vo.VoterID
    INNER JOIN candidates c ON c.CandidateID = vo.CandidateID
    INNER JOIN positions p ON p.PositionID = c.PositionID
    ORDER BY vo.VoteDate DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ballot Management</title>
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
    <h2 class="mb-4">Ballot Management</h2>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Vote ID</th>
                <th>Voter Name</th>
                <th>Candidate Name</th>
                <th>Position</th>
                <th>Vote Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['VoteID'] ?></td>
                        <td><?= htmlspecialchars($row['VoterFirst'] . ' ' . $row['VoterLast']) ?></td>
                        <td><?= htmlspecialchars($row['CandidateFirst'] . ' ' . $row['CandidateLast']) ?></td>
                        <td><?= htmlspecialchars($row['PositionName']) ?></td>
                        <td><?= date("Y-m-d H:i:s", strtotime($row['VoteDate'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No ballot records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<script>
    // Apply theme from localStorage
    document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
</script>
</body>
</html>
