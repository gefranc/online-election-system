<?php
require_once '../includes/config.php';

if (isset($_GET['id'])) {
    $positionID = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM positions WHERE PositionID = ?");
    $stmt->bind_param("i", $positionID);
    $stmt->execute();
    $result = $stmt->get_result();
    $position = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_position'])) {
    $positionID = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE positions SET PositionName = ?, Description = ? WHERE PositionID = ?");
    $stmt->bind_param("ssi", $name, $description, $positionID);
    $stmt->execute();
    header("Location: ../position.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Position</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            padding-top: 60px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card shadow-lg rounded-4">
        <div class="card-header">
            <h3 class="mb-0">Edit Position</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $position['PositionID'] ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Position Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($position['PositionName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" required><?= htmlspecialchars($position['Description']) ?></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" name="update_position" class="btn btn-primary">Update</button>
                    <a href="../position.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Theme Setup from localStorage -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    });
</script>
</body>
</html>
