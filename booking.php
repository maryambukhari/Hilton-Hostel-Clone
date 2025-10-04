<?php
// booking.php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>navigate('login.php');</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
$dest_id = isset($_GET['dest_id']) ? (int)$_GET['dest_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$dest_id]);
$dest = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$dest) {
    echo "<script>alert('Invalid destination.'); navigate('search.php');</script>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'];
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        echo "<script>alert('Booking date cannot be in the past.');</script>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, destination_id, booking_date, status) VALUES (?, ?, ?, 'Confirmed')");
            if ($stmt->execute([$user_id, $dest_id, $booking_date])) {
                // Simulate email (use PHPMailer in production)
                $user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $user->execute([$user_id]);
                $email = $user->fetchColumn();
                // mail($email, "Booking Confirmation", "Your trip to {$dest['name']} is confirmed for $booking_date!");
                echo "<script>alert('Booking confirmed! Check your email.'); navigate('dashboard.php');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($dest['name']); ?> - Hilton Hostel</title>
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
        }
        header {
            background: #ff6f61;
            padding: 20px;
            text-align: center;
            animation: slideDown 0.8s ease;
        }
        header h1 {
            color: white;
            font-size: 2.5em;
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #ffd700;
            text-shadow: 0 0 5px rgba(0,0,0,0.3);
        }
        .booking-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease;
        }
        h2 {
            color: #ff6f61;
            text-align: center;
            margin-bottom: 20px;
        }
        .destination-info {
            margin-bottom: 20px;
            text-align: center;
        }
        .destination-info img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border 0.3s;
        }
        input:focus {
            border-color: #ff6f61;
            outline: none;
            box-shadow: 0 0 5px rgba(255,111,97,0.5);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s;
        }
        button:hover {
            transform: scale(1.05);
            background: linear-gradient(45deg, #e55a50, #ff6f61);
        }
        button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        button:hover::after {
            width: 300px;
            height: 300px;
        }
        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #ff6f61;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            .booking-container { padding: 20px; }
            header h1 { font-size: 2em; }
            nav a { margin: 0 10px; font-size: 0.9em; }
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
    <div class="booking-container">
        <h2>Book <?php echo htmlspecialchars($dest['name']); ?></h2>
        <div class="destination-info">
            <img src="assets/<?php echo htmlspecialchars($dest['image']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>">
            <p><?php echo htmlspecialchars($dest['description']); ?></p>
            <p><strong>$<?php echo number_format($dest['price'], 2); ?></strong> for <?php echo $dest['duration']; ?> days</p>
        </div>
        <form method="POST" onsubmit="showLoading()">
            <input type="date" name="booking_date" id="booking_date" required>
            <button type="submit">Confirm Booking</button>
        </form>
        <div class="loading" id="loading">
            <div class="loading-spinner"></div>
            <p>Processing...</p>
        </div>
    </div>
    <script>
        function navigate(page) {
            window.location.href = page;
        }
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }
        // Client-side date validation
        document.getElementById('booking_date').setAttribute('min', '<?php echo date('Y-m-d'); ?>');
    </script>
</body>
</html>
