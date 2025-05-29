<?php
session_start();
include 'includes/config.php';

$adminID = $_SESSION['admin_id'] ?? null;

if (!$adminID) {
    header("Location: ../admin_login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM admin WHERE AdminID = ?");
$stmt->bind_param("i", $adminID);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();


// Fetch counts
$positions = $conn->query("SELECT COUNT(*) as count FROM positions")->fetch_assoc()['count'];
$candidates = $conn->query("SELECT COUNT(*) as count FROM candidates")->fetch_assoc()['count'];
$voters = $conn->query("SELECT COUNT(*) as count FROM voters")->fetch_assoc()['count'];
$conn->close();

$maxValue = max($positions, $candidates, $voters);
function getBarWidth($value, $max) {
    return ($max > 0) ? intval(($value / $max) * 100) : 0;
}



include 'includes/header.php';
include 'includes/navbar.php';
?>


<div class="main-content">
    <div class="card-container">
        <div class="card" onclick="location.href='position.php'">
            <h4>Positions: <?= $positions ?></h4>
            <div class="bar" style="width: <?= getBarWidth($positions, $maxValue) ?>%;"></div>
        </div>
        <div class="card" onclick="location.href='candidates.php'">
            <h4>Candidates: <?= $candidates ?></h4>
            <div class="bar" style="width: <?= getBarWidth($candidates, $maxValue) ?>%;"></div>
        </div>
        <div class="card" onclick="location.href='voters.php'">
            <h4>Total Voters: <?= $voters ?></h4>
            <div class="bar" style="width: <?= getBarWidth($voters, $maxValue) ?>%;"></div>
        </div> 
        <div class="card" onclick="location.href='voters_voted.php'">
           <h4>Voters Voted:</h4>
           <div class="bar" style="width: 100%;"></div>  
        </div>
            <div class="card" onclick="location.href='ballot.php'">
                <h4>Ballot Management</h4>
                <div class="bar" style="width: 100%;"></div>
            </div>
            <div class="card" onclick="location.href='tally.php'">
                <h4>Vote Tally</h4>
                <div class="bar" style="width: 100%;"></div>
            </div>
        <div class="card" onclick="location.href='election_title.php'">
            <h4>Election Title</h4>
            <div class="bar" style="width: 100%;"></div>
            
        </div>
    </div>
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
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
}
window.onclick = function (event) {
    if (!event.target.closest('.profile-section')) {
        document.getElementById('profileDropdown').style.display = 'none';
    }
};
</script>
</body>
</html>
