<?php
// plugin/delete_position.php
require_once '../includes/config.php'; // Update with your actual DB config file

if (isset($_GET['id'])) {
    $positionID = intval($_GET['id']);

    // Update status to Inactive
    $sql = "UPDATE positions SET Status = 'Inactive' WHERE PositionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $positionID);

    if ($stmt->execute()) {
        header("Location: ../position.php?message=deactivated");
        exit();
    } else {
        echo "Error deactivating record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
