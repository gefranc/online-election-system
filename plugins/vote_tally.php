<?php
$db = new mysqli('localhost', 'root', '', 'voting_system');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$results = [];
$positions_query = $db->query("SELECT * FROM positions");
while ($position = $positions_query->fetch_assoc()) {
    $position_id = $position['PositionID'];
    $position_title = $position['PositionName'];

    $candidates_query = $db->query("
        SELECT c.CandidateID, c.FirstName, c.LastName, c.Photo, COUNT(v.VoteID) as votes
        FROM candidates c
        LEFT JOIN votes v ON c.CandidateID = v.CandidateID
        WHERE c.PositionID = $position_id
        GROUP BY c.CandidateID, c.FirstName, c.LastName, c.Photo
        ORDER BY votes DESC
    ");

    $candidates = [];
    while ($candidate = $candidates_query->fetch_assoc()) {
        $candidates[] = $candidate;
    }

    $results[] = [
        'title' => $position_title,
        'candidates' => $candidates
    ];
}

$total_voters = $db->query("SELECT COUNT(DISTINCT VoterID) as total FROM votes")->fetch_assoc()['total'];
$db->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Church Election Results</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    :root {
      --bg-color: #ffffff;
      --text-color: #1c1c1c;
      --primary-color: #4a6fa5;
      --secondary-color: #e0e0e0;
    }

    [data-theme="dark"] {
      --bg-color: #121212;
      --text-color: #f0f0f0;
      --primary-color: #6d8cc0;
      --secondary-color: #333333;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      padding: 20px;
      margin: 0;
      transition: background-color 0.3s, color 0.3s;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .dashboard {
      max-width: 1100px;
      margin: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid var(--secondary-color);
    }

    th {
      background-color: var(--primary-color);
      color: white;
    }

    .charts {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 10px;
    }

    canvas {
      max-width: 450px;
      max-height: 400px;
      background: var(--secondary-color);
      padding: 10px;
      border-radius: 8px;
    }

    .candidate-photo {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
      margin-right: 10px;
    }

    .candidate-info {
      display: flex;
      align-items: center;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      transition: background-color 0.3s;
    }

    .btn-primary:hover {
      background-color: #3a5a8c;
    }

    .btn-primary i {
      margin-right: 6px;
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <header>
      <h1>Church Election Results</h1>
      <a href="../dashboard.php" class="btn-primary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </header>

    <div id="resultsTable"></div>
    <div class="charts" id="chartsContainer"></div>
  </div>

  <script>
    const electionData = {
      positions: <?php echo json_encode($results); ?>,
      totalVoters: <?php echo $total_voters; ?>
    };

    const vibrantColors = [
      '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
      '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe',
      '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000'
    ];

    function renderTable() {
      const container = document.getElementById('resultsTable');
      container.innerHTML = "";

      electionData.positions.forEach(position => {
        const totalVotes = position.candidates.reduce((sum, c) => sum + (parseInt(c.votes) || 0), 0);
        const maxVotes = Math.max(...position.candidates.map(c => parseInt(c.votes) || 0));

        let tableHTML = `<h2>${position.title}</h2><table><thead>
          <tr>
            <th>Candidate</th>
            <th>Votes</th>
            <th>Percentage</th>
            <th>Result</th>
          </tr></thead><tbody>`;

        position.candidates.forEach(candidate => {
          const percentage = totalVotes > 0 ? ((candidate.votes / totalVotes) * 100).toFixed(1) : 0;
          const isWinner = candidate.votes == maxVotes;
          const photo = candidate.Photo ? `<img src='../../admin/uploads/candidates/${candidate.Photo}' class='candidate-photo'>` : '';
          tableHTML += `
            <tr>
              <td><div class="candidate-info">${photo} ${candidate.FirstName} ${candidate.LastName}</div></td>
              <td>${candidate.votes}</td>
              <td>${percentage}%</td>
              <td>${isWinner ? "✅ Winner" : ""}</td>
            </tr>`;
        });

        tableHTML += "</tbody></table>";
        container.innerHTML += tableHTML;
      });
    }

    function renderCharts() {
      const container = document.getElementById('chartsContainer');
      container.innerHTML = "";

      electionData.positions.forEach((position, index) => {
        if (!position.candidates.length) return;

        const labels = position.candidates.map(c => `${c.FirstName} ${c.LastName}`);
        const data = position.candidates.map(c => parseInt(c.votes) || 0);

        // BAR CHART
        const barCanvas = document.createElement("canvas");
        barCanvas.id = `barChart${index}`;
        container.appendChild(barCanvas);

        new Chart(barCanvas.getContext('2d'), {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              label: `Votes for ${position.title}`,
              data,
              backgroundColor: vibrantColors.slice(0, data.length),
              borderColor: '#222',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            plugins: {
              title: {
                display: true,
                text: `Bar Chart - ${position.title}`,
                color: getComputedStyle(document.body).getPropertyValue('--text-color')
              },
              legend: {
                labels: {
                  color: getComputedStyle(document.body).getPropertyValue('--text-color')
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  color: getComputedStyle(document.body).getPropertyValue('--text-color')
                }
              },
              x: {
                ticks: {
                  color: getComputedStyle(document.body).getPropertyValue('--text-color')
                }
              }
            }
          }
        });

        // PIE CHART
        const pieCanvas = document.createElement("canvas");
        pieCanvas.id = `pieChart${index}`;
        container.appendChild(pieCanvas);

        new Chart(pieCanvas.getContext('2d'), {
          type: 'pie',
          data: {
            labels,
            datasets: [{
              data,
              backgroundColor: vibrantColors.slice(0, data.length),
              borderColor: '#fff',
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            plugins: {
              title: {
                display: true,
                text: `Pie Chart - ${position.title}`,
                color: getComputedStyle(document.body).getPropertyValue('--text-color')
              },
              legend: {
                labels: {
                  color: getComputedStyle(document.body).getPropertyValue('--text-color')
                }
              }
            }
          }
        });
      });
    }

    document.addEventListener("DOMContentLoaded", () => {
      const savedTheme = document.cookie.split("; ").find(row => row.startsWith("theme="))?.split("=")[1] || "light";
      document.body.setAttribute("data-theme", savedTheme);
      renderTable();
      renderCharts();
    });
  </script>
</body>
</html>
