<?php
// positions.php
require_once 'includes/config.php';

// Handle position status changes
if (isset($_GET['deactivate'])) {
    $positionID = $_GET['deactivate'];
    $updateStmt = $conn->prepare("UPDATE positions SET Status = 'Inactive' WHERE PositionID = ?");
    $updateStmt->bind_param("i", $positionID);
    $updateStmt->execute();
    header("Location: positions.php");
    exit();
}

if (isset($_GET['reactivate'])) {
    $positionID = $_GET['reactivate'];
    $updateStmt = $conn->prepare("UPDATE positions SET Status = 'Active' WHERE PositionID = ?");
    $updateStmt->bind_param("i", $positionID);
    $updateStmt->execute();
    header("Location: positions.php");
    exit();
}

// Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$whereClause = '';
if (!empty($search)) {
    $searchSafe = $conn->real_escape_string($search);
    $whereClause = " WHERE PositionName LIKE '%$searchSafe%' OR Description LIKE '%$searchSafe%'";
}

// Fetch positions with additional fields
$query = "SELECT PositionID, PositionName, Description, VotesAllowed, Status FROM positions $whereClause ORDER BY PositionID";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Manage Positions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: red;
            font-weight: bold;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .description-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .votes-allowed-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-user-tie me-2"></i>Manage Positions</h2>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Admin
                </a>
            </div>
        </div>

        <!-- Search + Add -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form class="input-group" method="GET">
                    <input type="text" name="search" class="form-control" placeholder="Search positions..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary"><i class="fas fa-search me-1"></i> Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="position.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Position
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Position Name</th>
                        <th>Description</th>
                        <th>Votes Allowed</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['PositionID'] ?></td>
                                <td><?= htmlspecialchars($row['PositionName']) ?></td>
                                <td class="description-cell" title="<?= htmlspecialchars($row['Description']) ?>">
                                    <?= !empty($row['Description']) ? htmlspecialchars($row['Description']) : '<span class="text-muted">No description</span>' ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary votes-allowed-badge">
                                        <?= $row['VotesAllowed'] ?> vote<?= $row['VotesAllowed'] != 1 ? 's' : '' ?> allowed
                                    </span>
                                </td>
                                <td class="status-<?= strtolower($row['Status']) ?>">
                                    <?= ucfirst($row['Status']) ?>
                                </td>
                                <td class="d-flex gap-2">
                                    <a href="plugin/edit_position.php?id=<?= $row['PositionID'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($row['Status'] === 'active'): ?>
                                        <a href="plugin/delete_position.php?id=<?= $row['PositionID'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to deactivate this position?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="plugin/reactivate_position.php?id=<?= $row['PositionID'] ?>" class="btn btn-sm btn-success" title="Reactivate" onclick="return confirm('Are you sure you want to reactivate this position?')">
                                            <i class="fas fa-toggle-on"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x mb-3 text-muted"></i><br>
                                No positions found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Position Modal -->
    <div class="modal fade" id="addPositionModal" tabindex="-1" aria-labelledby="addPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="plugin/add_position.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="positionName" class="form-label">Position Name*</label>
                        <input type="text" class="form-control" id="positionName" name="positionName" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="votesAllowed" class="form-label">Votes Allowed*</label>
                        <input type="number" class="form-control" id="votesAllowed" name="votesAllowed" min="1" value="1" required>
                        <small class="text-muted">Number of candidates a voter can select for this position</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" name="add_position"><i class="fas fa-save me-1"></i> Save Position</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme management
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
        
        // Tooltip initialization for description hover
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionCells = document.querySelectorAll('.description-cell');
            descriptionCells.forEach(cell => {
                cell.addEventListener('mouseenter', function() {
                    if (this.offsetWidth < this.scrollWidth) {
                        this.setAttribute('data-bs-toggle', 'tooltip');
                        this.setAttribute('data-bs-placement', 'top');
                        this.setAttribute('title', this.getAttribute('title'));
                        new bootstrap.Tooltip(this);
                    }
                });
            });
        });
    </script>
</body>
</html>