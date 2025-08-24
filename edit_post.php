<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT title, content, section_id FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    header('Location: index.php');
    exit();
}

$title = $post['title'];
$content = $post['content'];
$section_id = (int)$post['section_id'];
$section = null;
if ($section_id) {
    $secStmt = $db->prepare("SELECT title FROM sections WHERE id = ?");
    $secStmt->execute([$section_id]);
    $section = $secStmt->fetch(PDO::FETCH_ASSOC);
}
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $title = ucwords(strtolower($title));
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $update = $db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $update->execute([$title, $content, $id]);
        header('Location: view_post.php?id=' . $id);
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
<label for="content">Content (Markdown supported)</label><br>
<textarea name="content" id="content" rows="10" cols="50"><?php echo htmlspecialchars($content); ?></textarea><br>
<button type="submit">Update</button>
</form>
<?php if ($section): ?>
<p><a href="view_section.php?id=<?php echo $section_id; ?>">Back to <?php echo htmlspecialchars($section['title']); ?></a></p>
<?php else: ?>
<p><a href="index.php">Back to Index</a></p>
<?php endif; ?>
</body>
</html>
