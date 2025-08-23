<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT title, content FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    header('Location: index.php');
    exit();
}

$title = $post['title'];
$content = $post['content'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $update = $db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $update->execute([$title, $content, $id]);
        header('Location: index.php');
        exit();
    } else {
        $message = 'Title and content are required';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Post</title>
</head>
<body>
<h1>Edit Post</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="post">
<label for="title">Title</label><br>
<input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>"><br>
<label for="content">Content</label><br>
<textarea name="content" id="content" rows="10" cols="50"><?php echo htmlspecialchars($content); ?></textarea><br>
<button type="submit">Update</button>
</form>
<p><a href="index.php">Back to posts</a></p>
</body>
</html>
