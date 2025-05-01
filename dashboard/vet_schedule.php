<?php
// dashboard/vet_schedule.php
require_once __DIR__ . '/../functions.php';
require_role('vet');
$user = current_user();

// Fetch existing schedule
$stmt = $pdo->prepare("
  SELECT days, start_time, end_time
    FROM vet_schedule
   WHERE vet_id = ?
");
$stmt->execute([$user['id']]);
$scheduleRow = $stmt->fetch(PDO::FETCH_ASSOC);

$existingDays  = $scheduleRow ? json_decode($scheduleRow['days'], true) : [];
$existingStart = $scheduleRow ? substr($scheduleRow['start_time'], 0, 5) : '';
$existingEnd   = $scheduleRow ? substr($scheduleRow['end_time'],   0, 5) : '';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days       = $_POST['days'] ?? [];
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time   = trim($_POST['end_time']   ?? '');

    if (empty($days)) {
        $errors[] = 'Please select at least one day.';
    }
    if ($start_time === '' || $end_time === '') {
        $errors[] = 'Please specify both start and end times.';
    }

    if (empty($errors)) {
        $daysJson = json_encode(array_values($days));
        if ($scheduleRow) {
            $upd = $pdo->prepare("
              UPDATE vet_schedule
                 SET days=?, start_time=?, end_time=?
               WHERE vet_id=?
            ");
            $upd->execute([$daysJson, $start_time, $end_time, $user['id']]);
        } else {
            $ins = $pdo->prepare("
              INSERT INTO vet_schedule
                (vet_id, days, start_time, end_time)
              VALUES (?, ?, ?, ?)
            ");
            $ins->execute([$user['id'], $daysJson, $start_time, $end_time]);
        }
        $success = 'Your schedule has been saved.';
        $existingDays  = $days;
        $existingStart = $start_time;
        $existingEnd   = $end_time;
        $scheduleRow   = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Schedule | PetAdopt</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="vet.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="vet.php">Home</a>
          <a class="navbar-item is-active" href="vet_schedule.php">
            Manage Schedule
          </a>
        </div>
        <div class="navbar-end">
          <a class="navbar-item" href="../logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-info">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Your Weekly Availability</h1>
        <h2 class="subtitle">Set or update your working hours</h2>
      </div>
    </div>
  </section>

  <!-- Content -->
  <section class="section">
    <div class="container">

      <!-- Current Schedule -->
      <?php if ($scheduleRow): ?>
        <div class="card">
          <header class="card-header">
            <p class="card-header-title">Current Schedule</p>
          </header>
          <div class="card-content">
            <div class="table-container">
              <table class="table is-fullwidth is-striped">
                <thead>
                  <tr><th>Day</th><th>From</th><th>To</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($existingDays as $d): ?>
                  <tr>
                    <td><?= htmlspecialchars($d) ?></td>
                    <td><?= htmlspecialchars($existingStart) ?></td>
                    <td><?= htmlspecialchars($existingEnd) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="notification is-danger">
          You have not set your availability yet.
        </div>
      <?php endif; ?>

      <!-- Feedback -->
      <?php if ($errors): ?>
        <div class="notification is-danger">
          <button class="delete"></button>
          <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="notification is-success">
          <button class="delete"></button>
          <p><?= htmlspecialchars($success) ?></p>
        </div>
      <?php endif; ?>

      <!-- Schedule Form -->
      <div class="box">
        <form method="POST">
          <div class="field">
            <label class="label">Select Days</label>
            <div class="field is-grouped is-grouped-multiline">
              <?php
                $weekdays = [
                  'Mon'=>'Monday','Tue'=>'Tuesday','Wed'=>'Wednesday',
                  'Thu'=>'Thursday','Fri'=>'Friday',
                  'Sat'=>'Saturday','Sun'=>'Sunday'
                ];
                foreach ($weekdays as $abbr => $label):
              ?>
              <div class="control">
                <label class="checkbox">
                  <input type="checkbox" name="days[]" value="<?= $abbr ?>"
                    <?= in_array($abbr, $existingDays) ? 'checked' : '' ?>>
                  <?= $label ?>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="field is-horizontal">
            <div class="field-label is-normal">
              <label class="label">From / To</label>
            </div>
            <div class="field-body">
              <div class="field">
                <p class="control has-icons-left">
                  <input class="input" type="time" name="start_time"
                    value="<?= htmlspecialchars($_POST['start_time'] ?? $existingStart) ?>">
                  <span class="icon is-small is-left">
                    <i class="fas fa-clock"></i>
                  </span>
                </p>
              </div>
              <div class="field">
                <p class="control has-icons-left">
                  <input class="input" type="time" name="end_time"
                    value="<?= htmlspecialchars($_POST['end_time'] ?? $existingEnd) ?>">
                  <span class="icon is-small is-left">
                    <i class="fas fa-clock"></i>
                  </span>
                </p>
              </div>
            </div>
          </div>

          <div class="field">
            <div class="control">
              <button class="button is-primary">Save Schedule</button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </section>

  <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Navbar burger
      document.querySelectorAll('.navbar-burger').forEach(el => {
        el.addEventListener('click', () => {
          const tgt = document.getElementById(el.dataset.target);
          el.classList.toggle('is-active');
          tgt.classList.toggle('is-active');
        });
      });
      // Close notifications
      document.querySelectorAll('.notification .delete').forEach(btn => {
        btn.addEventListener('click', () => btn.parentNode.remove());
      });
    });
  </script>
</body>
</html>
