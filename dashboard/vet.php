<?php
// dashboard/vet.php
require_once __DIR__ . '/../functions.php';
require_role('vet');

$user = current_user();

// Fetch this vet's single-row schedule
$stmt = $pdo->prepare("
  SELECT days, start_time, end_time
    FROM vet_schedule
   WHERE vet_id = ?
");
$stmt->execute([$user['id']]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

// Decode if exists
if ($s) {
    $days      = json_decode($s['days'], true);
    $startTime = substr($s['start_time'], 0, 5);
    $endTime   = substr($s['end_time'],   0, 5);
} else {
    $days      = [];
    $startTime = '';
    $endTime   = '';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Vet Dashboard | PetAdopt</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation" aria-label="main navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="vet.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item is-active" href="vet.php">Home</a>
          <a class="navbar-item" href="vet_schedule.php">
            <?= empty($days) ? 'Set Schedule' : 'Edit Schedule' ?>
          </a>
        </div>
        <div class="navbar-end">
          <a class="navbar-item" href="../logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-primary">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Welcome, Dr. <?= htmlspecialchars($user['name']) ?></h1>
        <h2 class="subtitle">Your Weekly Availability</h2>
      </div>
    </div>
  </section>

  <!-- Content -->
  <section class="section">
    <div class="container">
      <?php if (empty($days)): ?>
        <div class="notification is-danger">
          You have not set your availability yet.
          <a href="vet_schedule.php" class="has-text-weight-bold">Click here to set your schedule.</a>
        </div>
      <?php else: ?>
        <div class="card">
          <header class="card-header">
            <p class="card-header-title">
              Current Schedule
            </p>
          </header>
          <div class="card-content">
            <div class="table-container">
              <table class="table is-striped is-fullwidth">
                <thead>
                  <tr>
                    <th>Day</th>
                    <th>From</th>
                    <th>To</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($days as $day): ?>
                  <tr>
                    <td><?= htmlspecialchars($day) ?></td>
                    <td><?= htmlspecialchars($startTime) ?></td>
                    <td><?= htmlspecialchars($endTime) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Bulma burger script -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const burger = document.querySelector('.navbar-burger');
      const menu   = document.getElementById('navMenu');
      burger.addEventListener('click', () => {
        burger.classList.toggle('is-active');
        menu.classList.toggle('is-active');
      });
    });
  </script>
</body>
</html>
