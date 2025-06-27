<?php
session_start();
include 'config/config.php';

if (!isset($_SESSION['voter_id'])) {
    header('Location: login.php');
    exit();
}
$voterID = $_SESSION['voter_id'];

$voterQuery = $conn->prepare("SELECT * FROM voters WHERE VoterID = ?");
$voterQuery->bind_param("i", $voterID);
$voterQuery->execute();
$voterResult = $voterQuery->get_result();
$voter = $voterResult->fetch_assoc();

$hasVotedQuery = $conn->query("SELECT COUNT(*) as count FROM votes WHERE VoterID = $voterID");
$hasVoted = $hasVotedQuery->fetch_assoc()['count'] > 0;

$positionsQuery = $conn->query("
    SELECT p.PositionID, p.PositionName, p.Description, c.CandidateID, c.FirstName, c.LastName, c.Photo 
    FROM positions p 
    LEFT JOIN candidates c ON p.PositionID = c.PositionID AND c.Status = 'active'
    WHERE p.Status = 'active'
    ORDER BY p.PositionID, c.LastName, c.FirstName
");

// Fetch election title
$electionTitleResult = $conn->query("SELECT Title FROM election_title WHERE Status = 'active' LIMIT 1");
$electionTitle = "Election"; // default title
if ($electionTitleResult && $electionTitleResult->num_rows > 0) {
    $electionTitle = $electionTitleResult->fetch_assoc()['Title'];
}


$positions = [];
while ($row = $positionsQuery->fetch_assoc()) {
    $position_id = $row['PositionID'];
    if (!isset($positions[$position_id])) {
        $positions[$position_id] = [
            'PositionName' => $row['PositionName'],
            'Description' => $row['Description'],
            'Candidates' => []
        ];
    }

    if ($row['CandidateID']) {
        $positions[$position_id]['Candidates'][] = [
            'CandidateID' => $row['CandidateID'],
            'FirstName' => $row['FirstName'],
            'LastName' => $row['LastName'],
            'Photo' => $row['Photo']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Voter Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/voter_dashboard.css">
</head>
<body data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
    <div class="logo">🛡️ ChurchVote</div>
      <div class="d-flex align-items-center">
        <a href="candidate/application.php" class="btn btn-outline-light me-3">
          <i class="fas fa-user-plus"></i> Candidate Application
        </a>
        <button type="button" class="theme-toggle me-3" onclick="toggleTheme()">
          <i class="fas fa-moon"></i>
        </button>
        <div class="dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" role="button" data-bs-toggle="dropdown">
            <img src="uploads/<?php echo $voter['Photo']; ?>" class="profile-img me-2">
            <?php echo htmlspecialchars($voter['FirstName'] . ' ' . $voter['LastName']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="plugins/view_profile.php"><i class="fas fa-user"></i> View Profile</a></li>
            <li><a class="dropdown-item" href="plugins/edit_profile.php"><i class="fas fa-edit"></i> Edit Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <?php if (isset($_GET['voted'])): ?>
    <div class="container mt-4">
        <?php if ($_GET['voted'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Your vote has been submitted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['voted'] === 'already'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                ⚠️ You have already voted. Each voter is allowed only one vote.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['voted'] === 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ There was an error submitting your vote. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


  <div class="container mt-4">
  <div class="container mt-4">
  <div class="text-center mb-4">
    <h1 class="election-title"><?php echo htmlspecialchars($electionTitle); ?></h1>
  </div>
  <h2 class="mb-4">Vote Your Candidates</h2>


    <?php if ($hasVoted): ?>
      <div class="alert alert-success">
        <h4 class="alert-heading">Thank You for Voting!</h4>
        <p>You have already submitted your vote. Each voter is allowed to vote only once.</p>
        <hr>
        <div class="d-flex justify-content-between align-items-center">
          <p class="mb-0">Want to see the current voting results?</p>
          <a href="plugins/vote_tally.php" class="btn btn-primary">
            <i class="fas fa-chart-bar me-2"></i>View Results
         </a>
        </div>
      </div>
    <?php else: ?>
      <form id="votingForm" action="plugins/submit_vote.php" method="POST">
        <?php foreach ($positions as $position_id => $position): ?>
          <div class="card mb-4">
            <div class="card-header">
              <h3 class="position-title">
                <div class="position-info">
                  <h4 class="position-name"><?php echo htmlspecialchars($position['PositionName']); ?></h4>
                  <?php if (!empty($position['Description'])): ?>
                    <p class="position-description"><?php echo htmlspecialchars($position['Description']); ?></p>
                  <?php endif; ?>
                </div>
                <span class="vote-status" id="status-<?php echo $position_id; ?>">Not Voted</span>
              </h3>
            </div>
            <div class="card-body">
              <div class="row">
                <?php if (empty($position['Candidates'])): ?>
                  <div class="col-12">
                    <p class="text-muted">No candidates available for this position.</p> 
                  </div>
                <?php else: ?>
                  <?php foreach ($position['Candidates'] as $candidate): ?>
                    <div class="col-md-3 mb-3">
                      <div class="candidate-card" onclick="selectCandidate(this, <?php echo $position_id; ?>, <?php echo $candidate['CandidateID']; ?>)">
                        <img src="../admin/uploads/candidates/<?php echo !empty($candidate['Photo']) ? htmlspecialchars($candidate['Photo']) : 'default-candidate.jpg'; ?>" class="candidate-img">  
                        <h5><?php echo htmlspecialchars($candidate['FirstName'] . ' ' . $candidate['LastName']); ?></h5>
                        <input type="radio" name="vote[<?php echo $position_id; ?>]" value="<?php echo $candidate['CandidateID']; ?>" hidden>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="vote-section">
          <div class="d-flex justify-content-between">
            <button type="reset" class="btn btn-secondary" onclick="resetVotes()">
              <i class="fas fa-undo me-2"></i>Reset
            </button>
            <div>
              <button type="button" onclick="window.location='dashboard.php'" class="btn btn-danger me-2">
                <i class="fas fa-times me-2"></i>Cancel
              </button>
              <button type="submit" class="btn btn-success submit-vote-btn" disabled 
                      data-tooltip="Please select a candidate for each position">
                <i class="fas fa-check me-2"></i>Submit Vote
              </button>
            </div>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/voter_dashboard.js"></script>
</body>
</html>
