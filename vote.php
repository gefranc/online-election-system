<?php
require_once 'config/config.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'voter') {
    header("Location: login.php");
    exit();
}

// Get all positions
$positions = $pdo->query("SELECT * FROM positions")->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $position_id = $_POST['position_id'];
    $candidate_id = $_POST['candidate_id'];
    $user_id = $_SESSION['user']['id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, position_id) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $candidate_id, $position_id]);
        $_SESSION['success'] = "Vote submitted successfully!";
        header("Location: vote.php");
        exit();
    } catch(PDOException $e) {
        $error = "You have already voted for this position";
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="row">
    <?php foreach($positions as $position): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $position['name']; ?></h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="position_id" value="<?php echo $position['id']; ?>">
                        <?php
                        $candidates = $pdo->prepare("SELECT * FROM candidates WHERE position_id = ?");
                        $candidates->execute([$position['id']]);
                        $candidates = $candidates->fetchAll();
                        ?>
                        <?php foreach($candidates as $candidate): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="candidate_id" 
                                    value="<?php echo $candidate['id']; ?>" required>
                                <label class="form-check-label">
                                    <?php echo $candidate['name']; ?>
                                    <?php if($candidate['photo']): ?>
                                        <img src="uploads/<?php echo $candidate['photo']; ?>" width="50">
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-success mt-3">Vote</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>