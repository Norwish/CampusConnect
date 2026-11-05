<?php
require_once 'config.php';
$id = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT);
if (!$id && $_SERVER['REQUEST_METHOD'] === 'POST') $id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$stmt = db()->prepare("SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) AS registered FROM events e WHERE e.id=? AND e.status='published'");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) { http_response_code(404); exit('Event not found.'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $matric = trim($_POST['student_id'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $matric === '') {
        $error = 'Enter your name, a valid email address and student ID.';
    } else {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            $lock = $pdo->prepare("SELECT capacity FROM events WHERE id=? FOR UPDATE");
            $lock->execute([$id]);
            $capacity = (int)$lock->fetchColumn();
            $count = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id=?");
            $count->execute([$id]);
            if ((int)$count->fetchColumn() >= $capacity) {
                $pdo->rollBack();
                $error = 'Sorry, this event has reached its capacity.';
            } else {
                $insert = $pdo->prepare("INSERT INTO registrations (event_id, full_name, email, student_id, phone) VALUES (?,?,?,?,?)");
                $insert->execute([$id, $name, $email, $matric, $phone]);
                $pdo->commit();
                flash('Registration successful. Your place has been reserved.');
                redirect('index.php');
            }
        } catch (PDOException $ex) {
            if (db()->inTransaction()) db()->rollBack();
            $error = $ex->getCode() === '23000'
                ? 'This email or student ID is already registered for this event.'
                : 'Registration could not be completed. Please try again.';
        }
    }
}
$notice = get_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Register | CampusConnect</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">C</span> CampusConnect</a><nav><a href="index.php">All events</a></nav></header>
<main class="form-page"><a class="back-link" href="index.php">← Back to events</a>
<div class="form-card"><p class="eyebrow">SAVE YOUR SPOT</p><h1>Event registration</h1><div class="selected-event"><strong><?= e($event['title']) ?></strong><span><?= e(date('D, d M Y', strtotime($event['event_date']))) ?> · <?= e(date('g:i A', strtotime($event['event_time']))) ?> · <?= e($event['venue']) ?></span></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="stack-form" id="registrationForm">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
<label>Full name<input name="full_name" required maxlength="120" value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="e.g. Ada Okafor"></label>
<label>Student email<input type="email" name="email" required maxlength="190" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com"></label>
<label>Student ID / Matric number<input name="student_id" required maxlength="60" value="<?= e($_POST['student_id'] ?? '') ?>" placeholder="e.g. CSC/2025/001"></label>
<label>Phone number <span class="muted">(optional)</span><input name="phone" maxlength="30" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+234..."></label>
<button class="button button-primary" type="submit">Confirm registration</button>
</form></div></main><script src="assets/app.js"></script></body></html>
