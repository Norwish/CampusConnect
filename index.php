<?php
require_once 'config.php';
$events = db()->query(
    "SELECT e.*,
      (SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) AS registered
     FROM events e WHERE e.status='published' AND e.event_date >= CURDATE()
     ORDER BY e.event_date ASC, e.event_time ASC"
)->fetchAll();
$notice = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CampusConnect | Student Events</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-mark">C</span> CampusConnect</a>
  <nav><a href="index.php">Events</a><a href="admin-login.php">Admin</a></nav>
</header>
<main>
  <section class="hero">
    <div class="hero-copy">
      <p class="eyebrow">YOUR CAMPUS. YOUR COMMUNITY.</p>
      <h1>Find your next<br><span>campus experience.</span></h1>
      <p>Discover student events, reserve your place and connect with your campus community.</p>
      <a class="button button-light" href="#events">Explore events</a>
    </div>
    <div class="hero-art" aria-hidden="true"><div class="art-circle">✦</div><div class="art-card">LEARN<br>MEET<br>GROW</div></div>
  </section>

  <section class="section" id="events">
    <div class="section-heading">
      <div><p class="eyebrow">WHAT'S HAPPENING</p><h2>Upcoming events</h2></div>
      <span class="muted"><?= count($events) ?> event(s)</span>
    </div>
    <?php if ($notice): ?><div class="alert <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif; ?>
    <?php if (!$events): ?>
      <div class="empty-state"><h3>No upcoming events yet</h3><p>Check back soon. New campus events will appear here.</p></div>
    <?php else: ?>
      <div class="event-grid">
      <?php foreach ($events as $event):
        $remaining = max(0, (int)$event['capacity'] - (int)$event['registered']);
        $full = $remaining === 0;
      ?>
        <article class="event-card">
          <div class="event-banner"><span><?= e(strtoupper($event['category'])) ?></span><strong><?= e(date('d', strtotime($event['event_date']))) ?><small><?= e(date('M', strtotime($event['event_date']))) ?></small></strong></div>
          <div class="event-content">
            <h3><?= e($event['title']) ?></h3>
            <p><?= e($event['description']) ?></p>
            <div class="event-meta"><span>◷ <?= e(date('g:i A', strtotime($event['event_time']))) ?></span><span>⌖ <?= e($event['venue']) ?></span></div>
            <div class="capacity"><span><?= (int)$event['registered'] ?> / <?= (int)$event['capacity'] ?> registered</span><span><?= $full ? 'Full' : $remaining . ' spaces left' ?></span></div>
            <div class="progress"><span style="width:<?= min(100, ((int)$event['registered'] / max(1, (int)$event['capacity'])) * 100) ?>%"></span></div>
            <?php if ($full): ?><button class="button button-disabled" disabled>Registration full</button>
            <?php else: ?><a class="button button-primary" href="register.php?event=<?= (int)$event['id'] ?>">Register now</a><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
<footer><span>© <?= date('Y') ?> CampusConnect</span><span>Built for campus communities</span></footer>
<script src="app.js"></script>
</body></html>
