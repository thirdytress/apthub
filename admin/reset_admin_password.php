<?php
// TEMPORARY RESET SCRIPT - DELETE AFTER USE
// Save as ahub/admin/reset_admin_password.php
// Protect via ?key=yoursecret

$secretKey = 'resetme123'; // <- palitan mo ito ng mahirap hulaan na key
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    http_response_code(403);
    die('Access denied.');
}

// --- CONFIG (adjust only if your setup is different) ---
$dbHost = 'localhost';
$dbName = 'apthub_db';
$dbUser = 'root';
$dbPass = '';
// --------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newpass = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($newpass === '' || $username === '') {
        $msg = "Username and new password are required.";
    } elseif ($newpass !== $confirm) {
        $msg = "New passwords do not match.";
    } else {
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check admin exists
            $s = $pdo->prepare("SELECT admin_id FROM admins WHERE username = :u LIMIT 1");
            $s->bindParam(':u', $username);
            $s->execute();
            if ($s->rowCount() === 0) {
                $msg = "Admin username not found.";
            } else {
                $hash = password_hash($newpass, PASSWORD_DEFAULT);
                $u = $pdo->prepare("UPDATE admins SET password = :p WHERE username = :u");
                $u->bindParam(':p', $hash);
                $u->bindParam(':u', $username);
                $u->execute();
                $msg = "Password updated successfully for user: " . htmlspecialchars($username) . ". Delete this file immediately.";
            }
        } catch (PDOException $e) {
            $msg = "DB Error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Reset Admin Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-5">
  <div class="col-md-6 mx-auto">
    <div class="card p-4 shadow">
      <h4 class="mb-3">Reset Admin Password (TEMP)</h4>
      <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?=htmlspecialchars($msg)?></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label>Admin Username</label>
          <input name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>New Password</label>
          <input name="new_password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Confirm New Password</label>
          <input name="confirm_password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Reset Password</button>
      </form>
      <p class="mt-3 text-muted small">
        Important: Delete this file after use: <code>admin/reset_admin_password.php</code>
      </p>
    </div>
  </div>
</div>
</body>
</html>
