<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_position'])) {
    $positionName = trim($_POST['positionName']);
    $description = trim($_POST['description']);

    if (!empty($positionName) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO positions (PositionName, Description, Status) VALUES (?, ?, 'Active')");
        $stmt->bind_param("ss", $positionName, $description);
        $stmt->execute();
        header("Location: ../position.php");
        exit();
    } else {
        echo "Both Position Name and Description are required.";
    }
}
?>
