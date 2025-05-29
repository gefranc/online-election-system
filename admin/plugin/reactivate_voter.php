<?php
require_once '../includes/config.php';

if (isset($_GET['id'])) {
    $voterID = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE voters SET Status = 'Active' WHERE VoterID = ?");
    $stmt->bind_param("i", $voterID);
    $stmt->execute();
}

header("Location: ../voters.php");
exit();
