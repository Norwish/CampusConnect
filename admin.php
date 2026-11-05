<?php
require_once 'config.php'; require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'create_event') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $date = $_POST['event_date'] ?? '';
        $time = $_POST['event_time'] ?? '';
        $venue = trim($_POST['venue'] ?? '');
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
        if ($title && $date && $time && $venue && $capacity && $capacity > 0) {
            $stmt = $pdo->prepare("INSERT INTO events (title, description, category, event_date, event_time, venue, capacity) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$title, $description, $category, $date, $time, $venue, $capacity]);
            flash('Event created successfully.');
        } else flash('Complete all required event fields. Capacity must be greater than zero.', 'error');
    } elseif ($action === 'toggle_checkin') {
        $regId = (int)($_POST['registration_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE registrations SET checked_in = IF(checked_in=1,0,1) WHERE id=?");
        $stmt->execute([$regId]);
        flash('Attendance status updated.');
    } elseif ($action === 'cancel_event') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE events SET status='cancelled' WHERE id=?");
        $stmt->execute([$eventId]);
        flash('Event cancelled.');
    }
    redirect('admin.php' . (!empty($_POST['selected_event']) ? '?event=' . (int)$_POST['selected_event'] : ''));
}
$events = $pdo->query("SELECT e.*, COUNT(r.id) AS registered FROM events e LEFT JOIN registrations r ON r.event_id=e.id GROUP BY e.id ORDER BY e.event_date DESC")->fetchAll();
$selected = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT);
$attendees = [];
if ($selected) {
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE event_id=? ORDER BY created_at DESC");
    $stmt->execute([$selected]); $attendees = $stmt->fetchAll();
}
$notice = get_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin dashboard | CampusConnect</title><link rel="stylesheet" href="style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">C</span> CampusConnect</a><nav><a href="index.php">View site</a><a href="logout.php">Log out</a></nav></header>
<main class="dashboard">
<div class="dashboard-heading"><div><p class="eyebrow">CONTROL CENTRE</p><h1>Admin dashboard</h1><p class="muted">Welcome, <?= e($_SESSION['admin_name'] ?? 'Admin') ?>.</p></div><a class="button button-primary" href="#create-event">+ Create event</a></div>
<?php if ($notice): ?><div class="alert <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif; ?>
<div class="dashboard-grid">
<section class="panel" id="create-event"><h2>Create an event</h2><form method="post" class="stack-form compact"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create_event">
<label>Event title<input name="title" required maxlength="160"></label><label>Description<textarea name="description" rows="3"></textarea></label>
<div class="two-col"><label>Category<input name="category" value="General" maxlength="60"></label><label>Capacity<input type="number" name="capacity" min="1" required></label></div>
<div class="two-col"><label>Date<input type="date" name="event_date" required></label><label>Time<input type="time" name="event_time" required></label></div>
<label>Venue<input name="venue" required maxlength="160"></label><button class="button button-primary">Publish event</button></form></section>
<section class="panel"><h2>Events</h2><?php if (!$events): ?><p class="muted">No events created yet.</p><?php else: ?><div class="event-list"><?php foreach ($events as $event): ?><div class="event-row"><div><strong><?= e($event['title']) ?></strong><small><?= e(date('d M Y', strtotime($event['event_date']))) ?> · <?= (int)$event['registered'] ?>/<?= (int)$event['capacity'] ?> registered · <?= e($event['status']) ?></small></div><a class="text-link" href="admin.php?event=<?= (int)$event['id'] ?>">Attendees</a><?php if ($event['status'] === 'published'): ?><form method="post" onsubmit="return confirm('Cancel this event?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="cancel_event"><input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>"><button class="link-button danger">Cancel</button></form><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?></section>
</div>
<?php if ($selected): ?><section class="panel attendee-panel"><div class="section-heading"><h2>Attendee list</h2><span class="muted"><?= count($attendees) ?> registered</span></div>
<?php if (!$attendees): ?><p class="muted">No registrations for this event yet.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Name</th><th>Student ID</th><th>Email</th><th>Registered</th><th>Attendance</th><th>Action</th></tr></thead><tbody><?php foreach ($attendees as $person): ?><tr><td><?= e($person['full_name']) ?></td><td><?= e($person['student_id']) ?></td><td><?= e($person['email']) ?></td><td><?= e(date('d M Y', strtotime($person['created_at']))) ?></td><td><span class="status <?= $person['checked_in'] ? 'checked' : 'pending' ?>"><?= $person['checked_in'] ? 'Checked in' : 'Not checked in' ?></span></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle_checkin"><input type="hidden" name="registration_id" value="<?= (int)$person['id'] ?>"><input type="hidden" name="selected_event" value="<?= (int)$selected ?>"><button class="small-button"><?= $person['checked_in'] ? 'Undo' : 'Check in' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section><?php endif; ?>
</main><script src="app.js"></script></body></html>
