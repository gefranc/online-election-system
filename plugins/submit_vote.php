<?php
session_start();
require_once '../config/config.php'; // DB connection


// Check if the user is logged in and is a voter
if (!isset($_SESSION['voter_id'])) {
    header("Location: ../login.php");
    exit();
}

$voterID = $_SESSION['voter_id'];

// Check if voter has already voted (optional, based on your system logic)
$checkVote = $conn->prepare("SELECT COUNT(*) FROM votes WHERE VoterID = ?");
$checkVote->bind_param("i", $voterID);
$checkVote->execute();
$checkVote->bind_result($voteCount);
$checkVote->fetch();
$checkVote->close();

if ($voteCount > 0) {
    // Already voted
    header("Location: ../dashboard.php?voted=already");
    exit();
}

// Ensure form data is present
if (isset($_POST['vote']) && is_array($_POST['vote'])) {
    $votes = $_POST['vote']; // array: PositionID => CandidateID

    $stmt = $conn->prepare("INSERT INTO votes (VoterID, PositionID, CandidateID) VALUES (?, ?, ?)");

    foreach ($votes as $positionID => $candidateID) {
        $positionID = intval($positionID);
        $candidateID = intval($candidateID);

        $stmt->bind_param("iii", $voterID, $positionID, $candidateID);
        $stmt->execute();
    }

    $stmt->close();

    // Optional: Update a "has_voted" flag in voters table
    // $conn->query("UPDATE voters SET has_voted = 1 WHERE VoterID = $voterID");

    // Redirect with success
    header("Location: ../dashboard.php?voted=success");
    exit();
} else {
    // No vote submitted
    header("Location: ../dashboard.php?voted=error");
    exit();
}
?>
