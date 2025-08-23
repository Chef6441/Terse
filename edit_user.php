<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT username, password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user['username'];
$current_password = $user['password'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $username = $new_username;
    $new_password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if ($new_username === '') {
        $message = 'Username is required';
    } elseif ($new_password !== $confirm) {
        $message = 'Passwords do not match';
    } else {
        $password_to_save = $current_password;
        if ($new_password !== '') {
            $password_to_save = password_hash($new_password, PASSWORD_DEFAULT);
        }
        try {
            $stmt = $db->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $stmt->execute([$new_username, $password_to_save, $user_id]);
            $_SESSION['username'] = $new_username;
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $message = 'Username already taken';
            } else {
                throw $e;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit User</title>
</head>
<body>
<h1>Edit User</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="post">
<label for="username">Username</label><br>
<input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>"><br>
<label for="password">New Password (leave blank to keep current)</label><br>
<input type="password" name="password" id="password"><br>
<label for="confirm">Confirm Password</label><br>
<input type="password" name="confirm" id="confirm"><br>
<button type="submit">Save</button>
</form>
<p><a href="index.php">Back to posts</a></p>
</body>
</html>

