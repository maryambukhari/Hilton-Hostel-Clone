<?php
// blog.php
session_start();
require 'db.php';

try {
    $per_page = 5;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $per_page;

    // Prepare query with explicit integer binding for LIMIT and OFFSET
    $stmt = $pdo->prepare("
        SELECT blog_posts.*, COALESCE(users.username, 'Anonymous') as username 
        FROM blog_posts 
        LEFT JOIN users ON blog_posts.author_id = users.id 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total count
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $total_posts = $total_stmt->fetchColumn();
    $total_pages = max(1, ceil($total_posts / $per_page));

    // Handle post creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        if (!empty($title) && !empty($content)) {
            $author_id = $_SESSION['user_id'];
            $insert_stmt = $pdo->prepare("INSERT INTO blog_posts (title, content, author_id) VALUES (?, ?, ?)");
            if ($insert_stmt->execute([$title, $content, $author_id])) {
                echo "<script>alert('Post created successfully!'); window.location.href='blog.php';</script>";
                exit;
            } else {
                $create_error = "Error creating post.";
            }
        } else {
            $create_error = "Title and content are required.";
        }
    }
} catch (PDOException $e) {
    $db_error = "Database error: " . $e->getMessage();
    $posts = [];
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Guides - Hilton Hostel</title>
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
        .blog-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        h2 {
            color: #ff6f61;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            animation: fadeInUp 1s ease;
        }
        .new-post-form {
            background: rgba(255,255,255,0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            display: <?php echo isset($_SESSION['user_id']) ? 'block' : 'none'; ?>;
            animation: slideInLeft 0.8s ease;
        }
        .new-post-form h3 {
            color: #ff6f61;
            margin-bottom: 15px;
        }
        .new-post-form input, .new-post-form textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #eee;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .new-post-form input:focus, .new-post-form textarea:focus {
            border-color: #ff6f61;
            outline: none;
            box-shadow: 0 0 10px rgba(255,111,97,0.2);
        }
        .new-post-form button {
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
        .new-post-form button:hover {
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
        .blog-post {
            background: rgba(255,255,255,0.95);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            display: flex;
            gap: 20px;
            backdrop-filter: blur(10px);
        }
        .blog-post:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .blog-post img {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
        }
        .blog-post h3 {
            color: #ff6f61;
            margin-bottom: 10px;
            font-size: 1.5em;
        }
        .blog-post .meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .blog-post p {
            color: #555;
            margin-bottom: 15px;
        }
        .btn {
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .no-posts {
            text-align: center;
            color: #666;
            font-size: 1.2em;
            padding: 40px;
            background: rgba(255,255,255,0.5);
            border-radius: 15px;
        }
        .no-posts a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .no-posts a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .pagination {
            text-align: center;
            margin: 30px 0;
        }
        .pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background: linear-gradient(45deg, #ff6f61, #ff8a65);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .pagination a:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255,111,97,0.4);
        }
        .pagination .current {
            background: #666;
            font-weight: bold;
        }
        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            .blog-container { padding: 15px; }
            .blog-post { flex-direction: column; text-align: center; }
            .blog-post img { width: 100%; height: auto; }
            header h1 { font-size: 2em; }
            nav a { margin: 0 8px; font-size: 0.9em; }
            h2 { font-size: 2em; }
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
    <div class="blog-container">
        <h2>Travel Guides & Tips</h2>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="new-post-form">
                <h3>Create a New Post</h3>
                <?php if (isset($create_error)): ?>
                    <div class="error"><?php echo htmlspecialchars($create_error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="title" placeholder="Post Title" required maxlength="255">
                    <textarea name="content" placeholder="Write your travel guide here..." rows="5" required></textarea>
                    <button type="submit">Publish Post</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (isset($db_error)): ?>
            <div class="error"><?php echo htmlspecialchars($db_error); ?></div>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <?php if (isset($_SESSION['user_id'])): ?>
                    No posts yet! Be the first to create one above.
                <?php else: ?>
                    No travel guides available yet. Login to create your own!
                    <a href="#" onclick="navigate('login.php')">Login to Create Posts</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="blog-post">
                    <img src="assets/blog-placeholder.jpg" alt="Blog Image" onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e';">
                    <div>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="meta">By <?php echo htmlspecialchars($post['username']); ?> on <?php echo date('M j, Y', strtotime($post['created_at'])); ?></p>
                        <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="#" onclick="savePost(<?php echo $post['id']; ?>)" class="btn">Save Article</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="#" onclick="navigate('blog.php?page=<?php echo $page - 1; ?>')">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="#" onclick="navigate('blog.php?page=<?php echo $i; ?>')" <?php echo $i === $page ? 'class="current"' : ''; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="#" onclick="navigate('blog.php?page=<?php echo $page + 1; ?>')">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script>
        function navigate(page) {
            window.location.href = page;
        }
        function savePost(postId) {
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                alert('Please login to save articles.');
                navigate('login.php');
                return;
            }
            fetch('save_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}`
            }).then(response => response.json()).then(data => {
                alert(data.message);
            }).catch(err => {
                alert('Error saving post. Please try again.');
            });
        }
    </script>
</body>
</html>
