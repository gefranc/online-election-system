<?php
// Database connection
$db = new mysqli('localhost', 'root', '', 'voting_system');

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get election results
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

// Get total voters
$total_voters = $db->query("SELECT COUNT(DISTINCT VoterID) as total FROM votes")->fetch_assoc()['total'];
$db->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Church Election Results</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    :root {
      --bg-color: #ffffff;
      --text-color: #333333;
      --primary-color: #4a6fa5;
      --secondary-color: #e0e0e0;
    }

    [data-theme="dark"] {
      --bg-color: #1a1a1a;
      --text-color: #f0f0f0;
      --primary-color: #6d8cc0;
      --secondary-color: #333333;
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
      margin: 0 auto;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    #themeToggle {
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th, td {
      padding: 12px;
      text-align: left;
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
    }

    canvas {
      max-width: 450px;
      max-height: 400px;
      background: var(--secondary-color);
      border-radius: 8px;
      padding: 10px;
    }

    .candidate-photo {
      width: 100px;
      height: 100px;
      border-radius: 60%;
      object-fit: cover;
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
      color: white;
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
      <div>
        <a href="candidate_dashboard.php" class="btn btn-primary">
          <i class="fas fa-arrow-left me-1"></i> Back 
        </a>
        <!-- <button id="themeToggle" onclick="toggleTheme()">Toggle Theme</button> -->
      </div>
    </header>

    <div class="results-container">
      <div id="resultsTable"></div>
      <div class="charts">
        <canvas id="barChart"></canvas>
        <canvas id="pieChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    const electionData = {
      positions: <?php echo json_encode($results); ?>,
      totalVoters: <?php echo $total_voters; ?>
    };

    function renderTable() {
      const tableContainer = document.getElementById('resultsTable');
      let tableHTML = '';

      electionData.positions.forEach(position => {
        const maxVotes = Math.max(...position.candidates.map(c => parseInt(c.votes) || 0));

        tableHTML += `
          <h2>${position.title}</h2>
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
              ${position.candidates.map(candidate => {
                const totalVotes = position.candidates.reduce((sum, c) => sum + (parseInt(c.votes) || 0), 0);
                const percentage = totalVotes > 0 ? ((candidate.votes / totalVotes) * 100).toFixed(1) : 0;
                const isWinner = candidate.votes == maxVotes;
                const photo = candidate.Photo ? `<img src="../admin/uploads/candidates/${candidate.Photo}" class="candidate-photo" alt="${candidate.FirstName} ${candidate.LastName}">` : '';
                return `
                  <tr>
                    <td>
                      <div class="candidate-info">
                        ${photo}
                        ${candidate.FirstName} ${candidate.LastName}
                      </div>
                    </td>
                    <td>${candidate.votes}</td>
                    <td>${percentage}%</td>
                    <td>${isWinner ? '✅ Winner' : ''}</td>
                  </tr>
                `;
              }).join('')}
            </tbody>
          </table>
        `;
      });

      tableContainer.innerHTML = tableHTML;
    }

    function renderCharts() {
  const chartsContainer = document.querySelector(".charts");
  chartsContainer.innerHTML = ""; // Clear old charts if any

  electionData.positions.forEach((position, index) => {
    if (!position.candidates.length) return; // Skip if no candidates

    const labels = position.candidates.map(c => `${c.FirstName} ${c.LastName}`);
    const data = position.candidates.map(c => parseInt(c.votes) || 0);

    // Vibrant high-contrast color palette
    const vibrantColors = [
      '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
      '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe',
      '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000'
    ];

    // === BAR CHART ===
    const barCanvas = document.createElement("canvas");
    const barId = `barChart${index}`;
    barCanvas.id = barId;
    chartsContainer.appendChild(barCanvas);

    new Chart(barCanvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: `Votes for ${position.title}`,
          data: data,
          backgroundColor: vibrantColors.slice(0, data.length),
          borderColor: '#333',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: `Bar Chart - ${position.title}`,
            color: getComputedStyle(document.documentElement).getPropertyValue('--text-color')
          },
          legend: {
            labels: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-color')
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-color')
            }
          },
          x: {
            ticks: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-color')
            }
          }
        }
      }
    });

    // === PIE CHART ===
    const pieCanvas = document.createElement("canvas");
    const pieId = `pieChart${index}`;
    pieCanvas.id = pieId;
    chartsContainer.appendChild(pieCanvas);

    new Chart(pieCanvas.getContext('2d'), {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: data,
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
            color: getComputedStyle(document.documentElement).getPropertyValue('--text-color')
          },
          legend: {
            position: 'right',
            labels: {
              color: getComputedStyle(document.documentElement).getPropertyValue('--text-color')
            }
          }
        }
      }
    });
  });
}


    function toggleTheme() {
      const current = localStorage.getItem('theme') === 'light' ? 'dark' : 'light';
      localStorage.setItem('theme', current);
      document.documentElement.setAttribute('data-theme', current);
    }

    document.addEventListener('DOMContentLoaded', () => {
      const savedTheme = localStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
      renderTable();
      renderCharts();
    });
  </script>
</body>
</html>
