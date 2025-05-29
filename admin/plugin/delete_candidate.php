<?php
session_start();
include '../includes/config.php';

if (isset($_GET['id'])) {
    $candidateID = intval($_GET['id']);

    // Delete candidate record permanently
    $stmt = $conn->prepare("DELETE FROM candidates WHERE CandidateID = ?");
    $stmt->bind_param("i", $candidateID);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Candidate permanently deleted.";
    } else {
        $_SESSION['error'] = "Failed to delete candidate.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid candidate ID.";
}

header("Location: ../candidates.php");
exit();
