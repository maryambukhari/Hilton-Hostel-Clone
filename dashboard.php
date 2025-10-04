<?php
// dashboard.php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>navigate('login.php');</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
try {
    // Fetch bookings
    $stmt = $pdo->prepare("SELECT b.*, d.name, d.image FROM bookings b JOIN destinations d ON b.destination_id = d.id WHERE b.user_id = ?");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch saved destinations
    $stmt = $pdo->prepare("SELECT s.*, d.name, d.image FROM saved_destinations s JOIN destinations d ON s.destination_id = d.id WHERE s.user_id = ?");
    $stmt->execute([$user_id]);
    $saved_destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch saved posts
    $stmt = $pdo->prepare("SELECT sp.*, bp.title FROM saved_posts sp JOIN blog_posts bp ON sp.post_id = bp.id WHERE sp.user_id = ?");
    $stmt->execute([$user_id]);
    $saved_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cancel booking
    if (isset($_GET['cancel_booking'])) {
        $booking_id = (int)$_GET['cancel_booking'];
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$booking_id, $user_id])) {
            echo "<script>alert('Booking cancelled successfully!'); navigate('dashboard.php');</script>";
        } else {
            $error = "Error cancelling booking.";
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $bookings = $saved_destinations = $saved_posts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hilton Hostel</title>
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
        .dashboard-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        h2 {
            color: #ff6f61;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            animation: fadeInUp 1s ease;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .tab-btn {
            padding: 10px 20px;
            background: rgba(255,255,255,0.95);
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .tab-btn.active, .tab-btn:hover {
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            border-color: #ff6f61;
        }
        .section {
            background: rgba(255,255,255,0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            margin-bottom: 20px;
            display: none;
            backdrop-filter: blur(10px);
        }
        .section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        .card {
            display: flex;
            gap: 20px;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.4s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            background: rgba(255,111,97,0.05);
        }
        .card img {
            width: 140px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .btn {
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .cancel-btn {
            background: linear-gradient(45deg, #ff4d4d, #ff6666);
        }
        .error, .empty-state {
            text-align: center;
            color: #ff4d4d;
            background: rgba(255,77,77,0.1);
            padding: 15px;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 600px;
        }
        .empty-state a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .empty-state a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            .dashboard-container { padding: 15px; }
            .card { flex-direction: column; }
            .card img { width: 100%; max-height: 150px; }
            header h1 { font-size: 2em; }
            .tabs { flex-direction: column; align-items: center; }
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
            <a href="#" onclick="navigate('profile.php')">Profile</a>
            <a href="#" onclick="navigate('logout.php')">Logout</a>
        </nav>
    </header>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('bookings')">My Bookings</button>
            <button class="tab-btn" onclick="showTab('saved_destinations')">Saved Destinations</button>
            <button class="tab-btn" onclick="showTab('saved_posts')">Saved Posts</button>
        </div>
        <div id="bookings" class="section active">
            <h3>Your Bookings</h3>
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    No bookings yet. Start exploring destinations!
                    <a href="#" onclick="navigate('search.php')">Search Trips</a>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($booking['image']); ?>" alt="<?php echo htmlspecialchars($booking['name']); ?>" 
                             onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e';">
                        <div>
                            <h4><?php echo htmlspecialchars($booking['name']); ?></h4>
                            <p>Booking Date: <?php echo $booking['booking_date']; ?></p>
                            <p>Status: <?php echo $booking['status']; ?></p>
                            <?php if ($booking['status'] !== 'Cancelled'): ?>
                                <a href="#" onclick="navigate('dashboard.php?cancel_booking=<?php echo $booking['id']; ?>')" class="btn cancel-btn">Cancel Booking</a>
                            <?php endif; ?>
                            <a href="#" onclick="navigate('review.php?dest_id=<?php echo $booking['destination_id']; ?>')" class="btn">Write Review</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div id="saved_destinations" class="section">
            <h3>Saved Destinations</h3>
            <?php if (empty($saved_destinations)): ?>
                <div class="empty-state">
                    No saved destinations yet. Find some amazing places!
                    <a href="#" onclick="navigate('search.php')">Search Trips</a>
                </div>
            <?php else: ?>
                <?php foreach ($saved_destinations as $dest): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($dest['image']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" 
                             onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e';">
                        <div>
                            <h4><?php echo htmlspecialchars($dest['name']); ?></h4>
                            <a href="#" onclick="navigate('booking.php?dest_id=<?php echo $dest['destination_id']; ?>')" class="btn">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div id="saved_posts" class="section">
            <h3>Saved Posts</h3>
            <?php if (empty($saved_posts)): ?>
                <div class="empty-state">
                    No saved posts yet. Check out travel guides!
                    <a href="#" onclick="navigate('blog.php')">View Guides</a>
                </div>
            <?php else: ?>
                <?php foreach ($saved_posts as $post): ?>
                    <div class="card">
                        <div>
                            <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                            <p>Saved on: <?php echo $post['created_at']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function navigate(page) {
            window.location.href = page;
        }
        function showTab(tabId) {
            document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
        }
    </script>
</body>
</html>
