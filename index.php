<?php
// 1. Start Session & Track Visits (Step 8)
session_start();
if (!isset($_SESSION['visits'])) {
    $_SESSION['visits'] = 1;
} else {
    $_SESSION['visits']++;
}

// 2. Connect to Database (Step 2)
require 'db.php';

// 3. Handle New Message Submission via POST (Steps 4, 7, 9)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = trim($_POST['author'] ?? '');
    $body   = trim($_POST['body'] ?? '');

    if ($author !== '' && $body !== '') {
        // Cookie logic: save if checked, delete if unchecked
        if (isset($_POST['remember'])) {
            setcookie('remembered_name', $author, time() + (86400 * 30), "/");
        } else {
            setcookie('remembered_name', '', time() - 3600, "/");
        }

        // Prepared statement for INSERT
        $stmt = $pdo->prepare("INSERT INTO messages (author, body) VALUES (?, ?)");
        $stmt->execute([$author, $body]);

        // Redirect to prevent duplicate submissions on refresh
        header("Location: index.php");
        exit;
    }
}

// 4. Pre-fill name from Cookie if available (Step 7)
$savedName = $_COOKIE['remembered_name'] ?? '';

// 5. Fetch Messages with Optional GET Search (Steps 5, 6, 9)
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    // ILIKE makes search case-insensitive in PostgreSQL
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE body ILIKE ? OR author ILIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
}
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Wall</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 0 15px; }
        .card { border: 1px solid #ccc; padding: 12px; margin-bottom: 10px; border-radius: 6px; }
        .meta { font-size: 0.85em; color: #555; margin-bottom: 6px; }
        form { margin-bottom: 20px; }
        input[type="text"], textarea { width: 100%; padding: 8px; margin: 6px 0; box-sizing: border-box; }
    </style>
</head>
<body>

    <h1>Class Wall</h1>
    <!-- Step 8: Display Visit Counter -->
    <p><strong>You've visited <?= $_SESSION['visits'] ?> times</strong> in this browser session.</p>

    <!-- Step 3: Post Message Form (POST) -->
    <form method="POST" action="index.php">
        <label>Your Name:</label>
        <input type="text" name="author" value="<?= htmlspecialchars($savedName) ?>" required>

        <label>Message:</label>
        <textarea name="body" rows="3" required></textarea>

        <label>
            <input type="checkbox" name="remember" <?= $savedName !== '' ? 'checked' : '' ?>>
            Remember my name
        </label>
        <br><br>
        <button type="submit">Post Message</button>
    </form>

    <hr>

    <!-- Step 6: Search Form (GET) -->
    <form method="GET" action="index.php">
        <input type="text" name="search" placeholder="Search messages..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <?php if ($search !== ''): ?>
            <a href="index.php">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Step 5 & 9: Display Messages Safely -->
    <?php if (empty($messages)): ?>
        <p>No messages found.</p>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="card">
                <div class="meta">
                    <strong><?= htmlspecialchars($msg['author']) ?></strong> • <?= htmlspecialchars($msg['created_at']) ?>
                </div>
                <div><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>