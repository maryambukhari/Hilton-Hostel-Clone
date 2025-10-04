<?php
// profile.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>navigate('login.php');</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = null;

try {
    // Fetch user details
    $stmt = $pdo->prepare("SELECT username, email, image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "User not found.";
    }

    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        // Validate inputs
        if (empty($username) || empty($email)) {
            $error = "Username and email are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Handle image upload
            $image_path = $user['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                $file_type = $_FILES['image']['type'];
                $file_size = $_FILES['image']['size'];
                $file_tmp = $_FILES['image']['tmp_name'];

                // Check uploads directory
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $error = "Failed to create uploads directory.";
                    }
                } elseif (!is_writable($upload_dir)) {
                    $error = "Uploads directory is not writable.";
                }

                if (!$error) {
                    if (!in_array($file_type, $allowed_types)) {
                        $error = "Only JPEG, PNG, or GIF images are allowed.";
                    } elseif ($file_size > $max_size) {
                        $error = "Image size must be less than 2MB.";
                    } else {
                        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $image_path = $upload_dir . $user_id . '_profile_' . time() . '.' . $ext;
                        if (!move_uploaded_file($file_tmp, $image_path)) {
                            $error = "Failed to upload image. Possible issues: directory permissions or server restrictions.";
                        }
                    }
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Handle specific upload errors
                switch ($_FILES['image']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = "Uploaded file is too large.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error = "File was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error = "Missing temporary directory.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error = "Failed to write file to disk.";
                        break;
                    default:
                        $error = "Unknown upload error.";
                }
            }

            // Update user details
            if (!$error) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, image = ? WHERE id = ?");
                if ($stmt->execute([$username, $email, $image_path, $user_id])) {
                    $success = "Profile updated successfully!";
                    $_SESSION['username'] = $username; // Update session
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['image'] = $image_path;
                } else {
                    $error = "Error updating profile.";
                }
            }
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Hilton Hostel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            color: #333;
            line-height: 1.6;
        }
        header {
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            padding: 20px;
            text-align: center;
            animation: slideDown 0.8s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        header h1 {
            color: white;
            font-size: 2.5em;
            font-weight: 600;
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        nav a:hover {
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255,215,0,0.5);
        }
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            animation: fadeInUp 1s ease;
        }
        h2 {
            color: #ff6f61;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
        }
        .profile-details {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ff6f61;
            transition: all 0.3s ease;
        }
        .profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .profile-info {
            flex: 1;
        }
        .profile-info p {
            margin: 10px 0;
            font-size: 1.1em;
            color: #555;
        }
        .profile-form {
            padding: 20px;
        }
        .profile-form h3 {
            color: #ff6f61;
            margin-bottom: 15px;
        }
        .profile-form input, .profile-form input[type="file"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #eee;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .profile-form input:focus {
            border-color: #ff6f61;
            outline: none;
            box-shadow: 0 0 10px rgba(255,111,97,0.2);
        }
        .profile-form button {
            padding: 12px 25px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        .profile-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .success, .error {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            color: #2e7d32;
            background: rgba(46,125,50,0.1);
        }
        .error {
            color: #ff4d4d;
            background: rgba(255,77,77,0.1);
        }
        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            .profile-container { padding: 15px; }
            .profile-details { flex-direction: column; align-items: center; }
            .profile-image { width: 120px; height: 120px; }
            header h1 { font-size: 2em; }
            nav a { margin: 0 8px; font-size: 0.9em; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hilton Hostel</h1>
        <nav>
            <a href="#" onclick="navigate('index.php')">Home</a>
            <a href="#" onclick="navigate('search.php')">Search Trips</a>
            <a href="#" onclick="navigate('blog.php')">Travel Guides</a>
            <a href="#" onclick="navigate('dashboard.php')">Dashboard</a>
            <a href="#" onclick="navigate('logout.php')">Logout</a>
        </nav>
    </header>
    <div class="profile-container">
        <h2>Your Profile</h2>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($user): ?>
            <div class="profile-details">
                <img src="<?php echo $user['image'] ? htmlspecialchars($user['image']) : 'https://via.placeholder.com/150'; ?>" 
                     alt="Profile Image" class="profile-image">
                <div class="profile-info">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
            <div class="profile-form">
                <h3>Edit Profile</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required maxlength="50">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif">
                    <button type="submit">Update Profile</button>
                </form>
            </div>
        <?php else: ?>
            <div class="error">Unable to load profile details.</div>
        <?php endif; ?>
    </div>
    <script>
        function navigate(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
