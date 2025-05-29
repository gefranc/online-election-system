<?php
// plugin/reactivate_position.php
require_once '../includes/config.php'; // Update with your actual DB config file

if (isset($_GET['id'])) {
    $positionID = intval($_GET['id']);

    // Update status to Active
    $sql = "UPDATE positions SET Status = 'Active' WHERE PositionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $positionID);

    if ($stmt->execute()) {
        header("Location: ../position.php?message=reactivated");
        exit();
    } else {
        echo "Error reactivating record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
