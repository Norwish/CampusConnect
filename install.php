<?php
require_once 'config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (strlen($username) < 4 || strlen($password) < 10) {
        $message = 'Use a username of at least 4 characters and a password of at least 10 characters.';
    } else {
        $pdo = db();
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          username VARCHAR(80) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS events (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          title VARCHAR(160) NOT NULL,
          description TEXT,
          category VARCHAR(60) NOT NULL DEFAULT 'General',
          event_date DATE NOT NULL,
          event_time TIME NOT NULL,
          venue VARCHAR(160) NOT NULL,
          capacity INT UNSIGNED NOT NULL,
          status ENUM('published','cancelled') NOT NULL DEFAULT 'published',
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          event_id INT UNSIGNED NOT NULL,
          full_name VARCHAR(120) NOT NULL,
          email VARCHAR(190) NOT NULL,
          student_id VARCHAR(60) NOT NULL,
          phone VARCHAR(30) DEFAULT NULL,
          checked_in TINYINT(1) NOT NULL DEFAULT 0,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY unique_event_email (event_id,email),
          UNIQUE KEY unique_event_student (event_id,student_id),
          CONSTRAINT fk_registration_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $count = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($count > 0) $message = 'An administrator already exists. Delete install.php now and sign in.';
        else {
            $stmt = $pdo->prepare("INSERT INTO admins (username,password_hash) VALUES (?,?)");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $message = 'Setup complete. Delete install.php from your server now, then sign in at admin-login.php.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Install CampusConnect</title><link rel="stylesheet" href="assets/style.css"></head><body><main class="form-page"><div class="form-card narrow"><p class="eyebrow">ONE-TIME SETUP</p><h1>Set up administrator</h1><p class="muted">First update the database credentials in config.php. After setup, remove install.php from your server.</p><?php if ($message): ?><div class="alert"><?= e($message) ?></div><?php endif; ?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Admin username<input name="username" minlength="4" required></label><label>Admin password<input type="password" name="password" minlength="10" required></label><button class="button button-primary">Create admin and tables</button></form></div></main></body></html>
