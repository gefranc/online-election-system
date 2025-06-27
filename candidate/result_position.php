<?php
session_start();
if (!isset($_SESSION['candidate_id'])) {
    header("Location: candidate_login.php");
    exit();
}

include '../config/config.php';

$candidateID = $_SESSION['candidate_id'];

// Get candidate info
$candidate = $conn->query("SELECT * FROM candidates WHERE CandidateID = '$candidateID'")->fetch_assoc();
$positionID = $candidate['PositionID'];
$position = $conn->query("SELECT * FROM positions WHERE PositionID = '$positionID'")->fetch_assoc();
$positionName = $position['PositionName'];

// Get all candidates for the position with their vote counts
$candidates_query = $conn->query("
    SELECT c.CandidateID, c.FirstName, c.LastName, c.Photo, COUNT(v.VoteID) AS votes
    FROM candidates c
    LEFT JOIN votes v ON c.CandidateID = v.CandidateID
    WHERE c.PositionID = '$positionID'
    GROUP BY c.CandidateID
    ORDER BY votes DESC
");

$candidates = [];
while ($row = $candidates_query->fetch_assoc()) {
    $candidates[] = $row;
}
$totalVotes = array_sum(array_column($candidates, 'votes'));

// Find logged-in candidate's data and rank
$myCandidate = null;
$myRank = 0;
foreach ($candidates as $index => $c) {
    if ($c['CandidateID'] == $candidateID) {
        $myCandidate = $c;
        $myRank = $index + 1;
        break;
    }
}
$totalCandidates = count($candidates);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <title>My Election Result</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    :root {
      --bg-color: #ffffff;
      --text-color: #333333;
      --primary-color: #4a6fa5;
      --secondary-color: #e0e0e0;
      --highlight-color: #d1ecf1;
    }

    [data-theme="dark"] {
      --bg-color: #1a1a1a;
      --text-color: #f0f0f0;
      --primary-color: #6d8cc0;
      --secondary-color: #333333;
      --highlight-color: #0d6efd33;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      margin: 0;
      padding: 20px;
      transition: background-color 0.3s, color 0.3s;
    }

    .dashboard {
      max-width: 1000px;
      margin: auto;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
      padding: 8px 16px;
      border-radius: 5px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid var(--secondary-color);
      vertical-align: middle;
      text-align: left;
    }

    th {
      background-color: var(--primary-color);
      color: white;
      font-weight: bold;
    }

    .my-row {
      background-color: var(--highlight-color);
      font-weight: bold;
    }

    .candidate-photo {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 50%;
      margin-right: 10px;
    }

    .candidate-info {
      display: flex;
      align-items: center;
      height: 100%;
    }

    /* Ensure all table cells have consistent alignment */
    table td:nth-child(1) { /* Candidate column */
      text-align: left;
    }
    
    table td:nth-child(2), /* Votes column */
    table td:nth-child(3), /* Percentage column */
    table td:nth-child(4) { /* Result column */
      text-align: center;
    }
    
    table th:nth-child(1) { /* Candidate header */
      text-align: left;
    }
    
    table th:nth-child(2), /* Votes header */
    table th:nth-child(3), /* Percentage header */
    table th:nth-child(4) { /* Result header */
      text-align: center;
    }

    .summary-box {
      background-color: var(--highlight-color);
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 1.1rem;
      text-align: center;
      font-weight: 600;
    }

    .charts {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }

    canvas {
      width: 100%;
      max-width: 450px;
      height: 400px !important;
      background: var(--secondary-color);
      border-radius: 10px;
      padding: 10px;
    }

    @media (max-width: 600px) {
      .candidate-photo {
        width: 60px;
        height: 60px;
      }

      canvas {
        max-width: 100%;
      }

      .charts {
        flex-direction: column;
        align-items: center;
      }

      header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <header>
      <h1>Results for: <?php echo htmlspecialchars($positionName); ?></h1>
      <a href="candidate_dashboard.php" class="btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
    </header>

    <?php if ($myCandidate): ?>
      <div class="summary-box">
        You are currently <strong><?php echo $myRank; ?><?php
          echo ($myRank == 1 ? 'st' : ($myRank == 2 ? 'nd' : ($myRank == 3 ? 'rd' : 'th')));
        ?> out of <?php echo $totalCandidates; ?></strong> candidates.
        <br>
        You received <strong><?php echo $myCandidate['votes']; ?></strong> vote(s),
        which is <strong><?php echo $totalVotes > 0 ? round(($myCandidate['votes'] / $totalVotes) * 100, 1) : 0; ?>%</strong> of total votes.
      </div>

      <table>
        <thead>
          <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
            <th>Result</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $percentage = $totalVotes > 0 ? round(($myCandidate['votes'] / $totalVotes) * 100, 1) : 0;
            $isWinner = $myCandidate['votes'] == max(array_column($candidates, 'votes'));
            $photo = $myCandidate['Photo']
              ? "<img src='../admin/uploads/candidates/" . htmlspecialchars($myCandidate['Photo']) . "' class='candidate-photo' alt='Your photo'>"
              : '';
            echo "<tr class='my-row'>
                    <td><div class='candidate-info'>{$photo}" . htmlspecialchars($myCandidate['FirstName']) . " " . htmlspecialchars($myCandidate['LastName']) . "</div></td>
                    <td>{$myCandidate['votes']}</td>
                    <td>{$percentage}%</td>
                    <td>" . ($isWinner ? '✅ Winner' : '⏳ Pending') . "</td>
                  </tr>";
          ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>Your data could not be found.</p>
    <?php endif; ?>

    <div class="charts">
      <canvas id="barChart"></canvas>
      <canvas id="pieChart"></canvas>
    </div>
  </div>

  <script>
    const candidates = <?php echo json_encode($candidates); ?>;
    const labels = candidates.map(c => `${c.FirstName} ${c.LastName}`);
    const data = candidates.map(c => parseInt(c.votes) || 0);
    const colors = ['#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#f032e6'];

    Chart.defaults.maintainAspectRatio = false;

    new Chart(document.getElementById('barChart'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Votes',
          data: data,
          backgroundColor: colors.slice(0, data.length),
          borderColor: '#444',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: { display: true, text: 'Bar Chart' },
          legend: { display: false }
        }
      }
    });

    new Chart(document.getElementById('pieChart'), {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: data,
          backgroundColor: colors.slice(0, data.length),
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: { display: true, text: 'Pie Chart' }
        }
      }
    });

    // Set theme from localStorage
    document.addEventListener('DOMContentLoaded', () => {
      const savedTheme = localStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
    });
  </script>
</body>
</html>
