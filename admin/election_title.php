<?php
require_once 'includes/config.php';

// Fetch current election title
$currentQuery = $conn->query("SELECT * FROM election_title WHERE Status = 'Active' LIMIT 1");
$current = $currentQuery->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);

    // Deactivate current title
    $conn->query("UPDATE election_title SET Status = 'Inactive'");

    // Insert new title
    $stmt = $conn->prepare("INSERT INTO election_title (Title, Status) VALUES (?, 'Active')");
    $stmt->bind_param("s", $title);
    $stmt->execute();

    header("Location: election_title.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Title</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; }
        [data-bs-theme='dark'] {
            background-color: #121212;
            color: #f1f1f1;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">Election Title Management</h2>

    <div class="card shadow rounded-4 mb-4">
        <div class="card-body">
            <h5 class="card-title">Current Active Title:</h5>
            <p class="card-text fw-bold">
                <?= $current ? htmlspecialchars($current['Title']) : 'No active title set.' ?>
            </p>
        </div>
    </div>

    <form method="POST" class="card shadow rounded-4 p-4">
        <div class="mb-3">
            <label for="title" class="form-label">New Election Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Set Title</button>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<script>
    // Sync with localStorage theme
    document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
</script>
</body>
</html>
