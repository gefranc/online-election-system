<?php
session_start();
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $positionID = (int)$_POST['position'];
    $status = 'Active'; // Default status

    // Handle photo upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/candidates/"; 
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        // Generate a unique filename using timestamp and random string
        $newFilename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
        $target_file = $target_dir . $newFilename;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES['photo']['tmp_name']);
        if ($check !== false) {
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedTypes)) {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                    $photo = $newFilename; // Store filename in database
                } else {
                    echo "<script>alert('Error uploading photo.');</script>";
                }
            } else {
                echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
            }
        } else {
            echo "<script>alert('File is not an image.');</script>";
        }
    }

    if (!empty($photo)) {
        try {
            $stmt = $conn->prepare("INSERT INTO candidates (FirstName, LastName, Photo, PositionID, Status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $firstName, $lastName, $photo, $positionID, $status);

            if ($stmt->execute()) {
                echo "<script>alert('Candidate added successfully!'); window.location='../candidates.php';</script>";
            } else {
                // If database insert fails, delete the uploaded file
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                echo "<script>alert('Error adding candidate: " . $conn->error . "');</script>";
            }
        } catch (Exception $e) {
            // If any error occurs, delete the uploaded file
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
}

// Fetch positions from database for dropdown
$positions = [];
$positionQuery = $conn->query("SELECT PositionID, PositionName FROM positions ORDER BY PositionName");
if ($positionQuery) {
    while ($row = $positionQuery->fetch_assoc()) {
        $positions[$row['PositionID']] = $row['PositionName'];
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Add Candidate</title>
    <style>
        :root {
            --bg-color: #f4f4f4;
            --text-color: #000;
            --card-bg: #fff;
            --header-bg: #333;
        }

        [data-theme="dark"] {
            --bg-color: #1e1e1e;
            --text-color: #fff;
            --card-bg: #2c2c2c;
            --header-bg: #222;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .header {
            background: var(--header-bg);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input[type="text"], select, input[type="file"] {
            width: calc(100% - 22px); 
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: var(--card-bg);
            color: var(--text-color);
            box-sizing: border-box; 
        }

        button {
            background-color: #28a745;
            border: none;
            padding: 10px 15px;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .dark-mode-toggle {
            background: none;
            border: 1px solid #fff;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body onload="applyTheme()">

<div class="header">
    <h2>➕ Add Candidate</h2>
    <div>
        <button onclick="location.href='../candidates.php'" class="dark-mode-toggle">← Back to List</button>
    </div>
</div>

<div class="container">
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name" required>

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" id="last_name" required>

        <label for="position">Position</label>
        <select name="position" id="position" required>
            <option value="">-- Select Position --</option>
            <?php foreach ($positions as $id => $name): ?>
                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="photo">Candidate Photo</label>
        <div class="photo-upload">
            <input type="file" name="photo" id="photo" accept="image/*" required onchange="previewImage(this)">
            <div id="imagePreview" class="image-preview">
                <img id="preview" src="../candidates/default.png" alt="Preview"> 
            </div>
        </div>
        <style>
            .photo-upload {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .image-preview {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                overflow: hidden;
                border: 2px solid var(--text-color);
            }
            .image-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
        </style>
        <script>
            function previewImage(input) {
                const preview = document.getElementById('preview');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>

        <button type="submit">Save Candidate</button>
    </form>
</div>

<script>
    function applyTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
</script>

</body>
</html>