<?php
require_once '../includes/config.php';

if (isset($_GET['id'])) {
    $voterID = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM voters WHERE VoterID = ?");
    $stmt->bind_param("i", $voterID);
    $stmt->execute();
    $result = $stmt->get_result();
    $voter = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_voter'])) {
    $voterID = intval($_POST['id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $status = trim($_POST['status']);

    $stmt = $conn->prepare("UPDATE voters SET FirstName = ?, LastName = ?, Email = ?, Status = ? WHERE VoterID = ?");
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $status, $voterID);
    $stmt->execute();
    header("Location: ../voters.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Voter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header">
            <h3>Edit Voter</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $voter['VoterID'] ?>">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($voter['FirstName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($voter['LastName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($voter['Email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Active" <?= ($voter['Status'] === 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($voter['Status'] === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="Blocked" <?= ($voter['Status'] === 'Blocked') ? 'selected' : '' ?>>Blocked</option>
                    </select>
                </div>
                <button type="submit" name="update_voter" class="btn btn-primary">Update</button>
                <a href="../voters.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<script>
    document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
</script>
</body>
</html>
