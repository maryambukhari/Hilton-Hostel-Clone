<?php
// search.php
session_start();
require 'db.php';
$filters = [];
$params = [];
try {
    $query = "SELECT * FROM destinations WHERE 1=1";
    if (!empty($_POST['name'])) {
        $query .= " AND name LIKE ?";
        $params[] = "%" . $_POST['name'] . "%";
    }
    if (!empty($_POST['price'])) {
        $query .= " AND price <= ?";
        $params[] = $_POST['price'];
    }
    if (!empty($_POST['type'])) {
        $query .= " AND type = ?";
        $params[] = $_POST['type'];
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $destinations = [];
    $error = "Error fetching destinations: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Trips - Hilton Hostel</title>
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
        .search-container {
            max-width: 1000px;
            margin: 40px auto;
            background: rgba(255,255,255,0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
        }
        form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        input, select {
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            flex: 1;
            min-width: 150px;
            transition: all 0.3s ease;
        }
        input:focus, select:focus {
            border-color: #ff6f61;
            outline: none;
            box-shadow: 0 0 10px rgba(255,111,97,0.2);
        }
        button {
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
        .destinations {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            padding: 20px;
        }
        .destination-card {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            overflow: hidden;
            width: 320px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.4s ease;
        }
        .destination-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .destination-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: 3px solid #ff6f61;
        }
        .destination-card h3 {
            padding: 15px;
            color: #ff6f61;
            font-size: 1.5em;
        }
        .destination-card p {
            padding: 0 15px 15px;
            color: #555;
        }
        .btn {
            display: block;
            padding: 12px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            margin: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .error {
            text-align: center;
            color: #ff4d4d;
            background: rgba(255,77,77,0.1);
            padding: 15px;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 600px;
        }
        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            form { flex-direction: column; }
            .destination-card { width: 100%; max-width: 350px; }
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="navigate('dashboard.php')">Dashboard</a>
                <a href="#" onclick="navigate('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="navigate('login.php')">Login</a>
                <a href="#" onclick="navigate('signup.php')">Signup</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="search-container">
        <h2>Search Trips</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Destination Name">
            <input type="number" name="price" placeholder="Max Price" min="0" step="0.01">
            <select name="type">
                <option value="">Select Type</option>
                <option value="Adventure">Adventure</option>
                <option value="Relaxation">Relaxation</option>
                <option value="Cultural">Cultural</option>
                <option value="Luxury">Luxury</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>
    <section class="destinations">
        <?php if (empty($destinations)): ?>
            <div class="error">No destinations match your search.</div>
        <?php else: ?>
            <?php foreach ($destinations as $dest): ?>
                <div class="destination-card">
                    <img src="<?php echo htmlspecialchars($dest['image']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" 
                         onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e';">
                    <h3><?php echo htmlspecialchars($dest['name']); ?></h3>
                    <p><?php echo htmlspecialchars($dest['description']); ?></p>
                    <p><strong>$<?php echo number_format($dest['price'], 2); ?></strong> for <?php echo $dest['duration']; ?> days</p>
                    <a href="#" onclick="navigate('booking.php?dest_id=<?php echo $dest['id']; ?>')" class="btn">Book Now</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="#" onclick="saveDestination(<?php echo $dest['id']; ?>)" class="btn">Save to Wishlist</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <script>
        function navigate(page) {
            window.location.href = page;
        }
        function saveDestination(destId) {
            fetch('save_destination.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `dest_id=${destId}`
            }).then(response => response.json()).then(data => {
                alert(data.message);
            }).catch(err => {
                alert('Error saving destination.');
            });
        }
    </script>
</body>
</html>
