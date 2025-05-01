<?php
// book_vet.php

require_once __DIR__ . '/functions.php';
require_role('adopter');

$user = current_user();
$errors = [];
$success = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vet_id           = (int)($_POST['vet_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';

    if (!$vet_id) {
        $errors[] = 'Please choose a veterinarian.';
    }
    if (!$appointment_date) {
        $errors[] = 'Please select an appointment date.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
          SELECT days, start_time, end_time
            FROM vet_schedule
           WHERE vet_id = ?
        ");
        $stmt->execute([$vet_id]);
        $sched = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sched) {
            $errors[] = 'That veterinarian has not set a schedule.';
        } else {
            $days       = json_decode($sched['days'], true);
            $start_time = substr($sched['start_time'], 0, 5);
            $end_time   = substr($sched['end_time'],   0, 5);

            $chosen    = DateTime::createFromFormat('Y-m-d', $appointment_date);
            $today     = new DateTime('today');
            $diff      = (int)$today->diff($chosen)->format('%r%a');
            $dayCode   = $chosen->format('D');

            if ($diff < 0 || $diff > 14 || !in_array($dayCode, $days)) {
                $errors[] = 'Selected date is not within the next 15 days or not on an available weekday.';
            } else {
                $chk = $pdo->prepare("
                  SELECT COUNT(*) FROM appointments
                   WHERE vet_id = ? AND appointment_date = ?
                ");
                $chk->execute([$vet_id, $appointment_date]);
                if ($chk->fetchColumn() > 0) {
                    $errors[] = 'That date is already booked.';
                }
            }

            if (empty($errors)) {
                $ins = $pdo->prepare("
                  INSERT INTO appointments
                    (user_id, vet_id, appointment_date, time_slot)
                  VALUES (?, ?, ?, ?)
                ");
                $time_slot = "{$start_time}-{$end_time}";
                $ins->execute([
                    $user['id'],
                    $vet_id,
                    $appointment_date,
                    $time_slot
                ]);
                $success = "Appointment booked for {$appointment_date} at {$time_slot}.";
            }
        }
    }
}

// Prepare available dates if vet selected
$selected_vet    = (int)($_REQUEST['vet_id'] ?? 0);
$available_dates = [];
if ($selected_vet) {
    $stmt = $pdo->prepare("
      SELECT days, start_time, end_time
        FROM vet_schedule
       WHERE vet_id = ?
    ");
    $stmt->execute([$selected_vet]);
    $sched = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sched) {
        $days       = json_decode($sched['days'], true);
        $start_time = substr($sched['start_time'], 0, 5);
        $end_time   = substr($sched['end_time'],   0, 5);
        $today      = new DateTime('today');

        for ($i = 0; $i < 15; $i++) {
            $dt = (clone $today)->add(new DateInterval("P{$i}D"));
            if (in_array($dt->format('D'), $days)) {
                $available_dates[] = [
                    'date'  => $dt->format('Y-m-d'),
                    'label' => $dt->format('D, M j, Y') . " ({$start_time}-{$end_time})"
                ];
            }
        }
    }
}

// Fetch vets
$vets = $pdo->query("
  SELECT id, name
    FROM users
   WHERE role = 'vet'
   ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Book a Vet Appointment | PetAdopt</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="index.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="index.php">Home</a>
          <a class="navbar-item" href="dashboard/adopter.php">Dashboard</a>
        </div>
        <div class="navbar-end">
          <a class="navbar-item" href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-primary">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Book a Vet Appointment</h1>
        <h2 class="subtitle">Choose your veterinarian and preferred date</h2>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <section class="section">
    <div class="container">

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

      <!-- Select Vet -->
      <form method="GET">
        <div class="field is-horizontal">
          <div class="field-label is-normal">
            <label class="label">Veterinarian</label>
          </div>
          <div class="field-body">
            <div class="field">
              <div class="control">
                <div class="select is-fullwidth">
                  <select name="vet_id" onchange="this.form.submit()">
                    <option value="">-- Select Vet --</option>
                    <?php foreach ($vets as $vet): ?>
                      <option value="<?= $vet['id'] ?>"
                        <?= $vet['id'] === $selected_vet ? 'selected' : '' ?>>
                        <?= htmlspecialchars($vet['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Date Picker -->
      <?php if ($selected_vet): ?>
        <?php if (empty($available_dates)): ?>
          <div class="notification is-warning">
            This vet has no available slots in the next 15 days.
          </div>
        <?php else: ?>
          <form method="POST">
            <input type="hidden" name="vet_id" value="<?= $selected_vet ?>">
            <div class="field is-horizontal">
              <div class="field-label is-normal">
                <label class="label">Appointment Date</label>
              </div>
              <div class="field-body">
                <div class="field">
                  <div class="control">
                    <div class="select is-fullwidth">
                      <select name="appointment_date">
                        <?php foreach ($available_dates as $opt): ?>
                          <option value="<?= $opt['date'] ?>">
                            <?= htmlspecialchars($opt['label']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="field">
                  <div class="control">
                    <button class="button is-primary">
                      Book Appointment
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // burger toggle
      document.querySelectorAll('.navbar-burger').forEach(el => {
        el.addEventListener('click', () => {
          const menu = document.getElementById(el.dataset.target);
          el.classList.toggle('is-active');
          menu.classList.toggle('is-active');
        });
      });
      // close notifications
      document.querySelectorAll('.notification .delete').forEach(btn => {
        btn.addEventListener('click', () => btn.parentNode.remove());
      });
    });
  </script>
</body>
</html>
