<?php
include '../../config/config.php';

if (!isset($_GET['id'])) {
    header("Location: ../../candidates.php");
    exit();
}

$candidateID = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstname'];
    $lastName = $_POST['lastname'];
    $positionID = $_POST['position'];

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $targetDir = "../uploads/candidates/";
        $targetFile = $targetDir . $photoName;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile);

        $stmt = $conn->prepare("UPDATE candidates SET Firstname=?, LastName=?, PositionID=?, Photo=? WHERE CandidateID=?");
        $stmt->bind_param("ssisi", $firstName, $lastName, $positionID, $photoName, $candidateID);
    } else {
        $stmt = $conn->prepare("UPDATE candidates SET Firstname=?, LastName=?, PositionID=? WHERE CandidateID=?");
        $stmt->bind_param("ssii", $firstName, $lastName, $positionID, $candidateID);
    }

    if ($stmt->execute()) {
        header("Location: ../candidates.php?success=1");
    } else {
        echo "Error updating candidate.";
    }
    exit();
}

// Fetch candidate
$stmt = $conn->prepare("SELECT * FROM candidates WHERE CandidateID=?");
$stmt->bind_param("i", $candidateID);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();

// Fetch positions
$positions = $conn->query("SELECT * FROM positions ORDER BY PositionName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Candidate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            transition: all 0.3s ease;
        }
        :root {
            --bg: #f0f0f0;
            --text: #222;
            --card: #fff;
            --border: #ddd;
        }
        body.dark {
            --bg: #121212;
            --text: #f1f1f1;
            --card: #1e1e1e;
            --border: #333;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: var(--card);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 16px;
            border-radius: 5px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
        }
        input[type="file"] {
            padding: 5px;
        }
        .photo-preview {
            display: block;
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        button, a {
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            cursor: pointer;
            border-radius: 5px;
        }
        a {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Candidate</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>First Name:</label>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($candidate['FirstName']); ?>" required>

            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($candidate['LastName']); ?>" required>

            <label>Position:</label>
            <select name="position" required>
                <?php while ($row = $positions->fetch_assoc()): ?>
                    <option value="<?php echo $row['PositionID']; ?>" <?php if ($row['PositionID'] == $candidate['PositionID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['PositionName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Current Photo:</label><br>
            <img src="../candidates/<?php echo htmlspecialchars($candidate['Photo']); ?>" class="photo-preview" alt="Candidate Photo"><br>

            <label>Change Photo:</label>
            <input type="file" name="photo" accept="image/*"><br>

            <button type="submit">Update</button>
            <a href="../candidates.php">Cancel</a>
        </form>
    </div>

    <script>
        // Apply dark/light mode based on localStorage
        const theme = localStorage.getItem("theme");
        if (theme === "dark") {
            document.body.classList.add("dark");
        }
    </script>
</body>
</html>
