<?php
// review.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>navigate('login.php');</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
$dest_id = isset($_GET['dest_id']) ? (int)$_GET['dest_id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$dest_id]);
    $dest = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dest) {
        echo "<script>alert('Invalid destination.'); navigate('dashboard.php');</script>";
        exit;
    }

    // Fetch average rating
    $avg_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE destination_id = ?");
    $avg_stmt->execute([$dest_id]);
    $avg_rating = $avg_stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        if ($rating >= 1 && $rating <= 5) {
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, destination_id, rating, comment) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $dest_id, $rating, $comment])) {
                echo "<script>alert('Review submitted successfully!'); navigate('dashboard.php');</script>";
                exit;
            } else {
                $error = "Error submitting review.";
            }
        } else {
            $error = "Rating must be between 1 and 5.";
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
    <title>Review <?php echo htmlspecialchars($dest['name'] ?? 'Trip'); ?> - Hilton Hostel</title>
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
        .review-container {
            max-width: 600px;
            margin: 40px auto;
            background: rgba(255,255,255,0.95);
            padding: 30px;
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
        .destination-preview {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255,111,97,0.1);
            border-radius: 10px;
        }
        .destination-preview img {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .avg-rating {
            color: #ffd700;
            font-size: 1.2em;
            margin: 10px 0;
        }
        select, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #eee;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        select:focus, textarea:focus {
            border-color: #ff6f61;
            outline: none;
            box-shadow: 0 0 10px rgba(255,111,97,0.2);
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .error {
            color: #ff4d4d;
            background: rgba(255,77,77,0.1);
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .stars {
            color: #ffd700;
            font-size: 1.5em;
            margin: 10px 0;
        }
        .stars span {
            color: #ddd;
            transition: color 0.3s ease;
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
            .review-container { padding: 20px; margin: 20px; }
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
    <div class="review-container">
        <h2>Review <?php echo htmlspecialchars($dest['name']); ?></h2>
        <div class="destination-preview">
            <img src="assets/<?php echo htmlspecialchars($dest['image']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" onerror="this.src='assets/bali.jpg';">
            <p><?php echo htmlspecialchars($dest['description']); ?></p>
            <p><strong>$<?php echo number_format($dest['price'], 2); ?></strong> for <?php echo $dest['duration']; ?> days</p>
            <?php if ($avg_rating['review_count'] > 0): ?>
                <div class="avg-rating">Average Rating: <?php echo round($avg_rating['avg_rating'], 1); ?>/5 (<?php echo $avg_rating['review_count']; ?> reviews)</div>
                <div class="stars">★★★★★<span style="color: #ddd;">☆☆☆☆☆</span></div>
            <?php else: ?>
                <p>Be the first to review!</p>
            <?php endif; ?>
        </div>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Rating:</label>
            <select name="rating" required>
                <option value="">Select Rating</option>
                <option value="5">5 Stars ★★★★★</option>
                <option value="4">4 Stars ★★★★☆</option>
                <option value="3">3 Stars ★★★☆☆</option>
                <option value="2">2 Stars ★★☆☆☆</option>
                <option value="1">1 Star ★☆☆☆☆</option>
            </select>
            <label>Comment:</label>
            <textarea name="comment" placeholder="Share your experience... (optional)" maxlength="1000"></textarea>
            <button type="submit">Submit Review</button>
        </form>
    </div>
    <script>
        function navigate(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
