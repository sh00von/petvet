<?php
// dashboard/adopter.php
require_once __DIR__ . '/../functions.php';
require_role('adopter');
$user = current_user();

// Handle appointment deletion
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_appointment_id'])) {
    $delId = (int)$_POST['delete_appointment_id'];
    $del = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
    $del->execute([$delId, $user['id']]);
    $flash = $del->rowCount()
        ? 'Appointment cancelled.'
        : 'Unable to cancel appointment.';
}

// Fetch upcoming vet appointments
$apptStmt = $pdo->prepare("
  SELECT a.id, a.appointment_date,
         a.time_slot, u.name AS vet_name
    FROM appointments a
    JOIN users u ON a.vet_id = u.id
   WHERE a.user_id = ?
     AND a.appointment_date >= CURDATE()
   ORDER BY a.appointment_date
");
$apptStmt->execute([$user['id']]);
$appointments = $apptStmt->fetchAll(PDO::FETCH_ASSOC);

// Pet filter inputs
$typeFilter   = trim($_GET['type'] ?? '');
$breedFilter  = trim($_GET['breed'] ?? '');
$colorFilter  = trim($_GET['color'] ?? '');
$healthFilter = trim($_GET['health_status'] ?? '');
$ageMin       = (int)($_GET['age_min'] ?? 0);
$ageMax       = (int)($_GET['age_max'] ?? 0);

// Dropdown data
$types   = $pdo->query("SELECT DISTINCT type FROM pets")->fetchAll(PDO::FETCH_COLUMN);
$breeds  = $pdo->query("SELECT DISTINCT breed FROM pets")->fetchAll(PDO::FETCH_COLUMN);
$colors  = $pdo->query("SELECT DISTINCT color FROM pets")->fetchAll(PDO::FETCH_COLUMN);
$healths = $pdo->query("SELECT DISTINCT health_status FROM pets")->fetchAll(PDO::FETCH_COLUMN);

// Build WHERE clauses
$where = ["adopter_id IS NULL"];
$params = [];
if ($typeFilter)   { $where[] = "pets.type = ?";          $params[] = $typeFilter; }
if ($breedFilter)  { $where[] = "pets.breed = ?";         $params[] = $breedFilter; }
if ($colorFilter)  { $where[] = "pets.color = ?";         $params[] = $colorFilter; }
if ($healthFilter) { $where[] = "pets.health_status = ?"; $params[] = $healthFilter; }
if ($ageMin > 0)   { $where[] = "pets.age >= ?";          $params[] = $ageMin; }
if ($ageMax > 0)   { $where[] = "pets.age <= ?";          $params[] = $ageMax; }

$sql = "
  SELECT pets.*, users.name AS store_name
    FROM pets
    JOIN users ON pets.store_id = users.id
   WHERE " . implode(' AND ', $where) . "
   ORDER BY pets.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Adopter Dashboard | PetAdopt</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="adopter.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item is-active" href="adopter.php">Dashboard</a>
          <a class="navbar-item" href="../book_vet.php">Book a Vet</a>
          <a class="navbar-item" href="adoption_history.php">My Adoption History</a>
          <a class="navbar-item" href="user_purchases.php">My Purchases</a>
          <a class="navbar-item" href="../shop.php">Shop</a>
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
        <h1 class="title">Adopter Dashboard</h1>
        <h2 class="subtitle">Manage your adoptions, purchases, and appointments</h2>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <!-- Flash -->
      <?php if ($flash): ?>
        <div class="notification <?= strpos($flash,'cancelled')!==false ? 'is-success' : 'is-danger' ?>">
          <button class="delete"></button>
          <?= htmlspecialchars($flash) ?>
        </div>
      <?php endif; ?>

      <!-- Upcoming Appointments -->
      <h2 class="title is-4">Upcoming Vet Appointments</h2>
      <?php if (empty($appointments)): ?>
        <div class="notification is-info">
          You have no upcoming appointments.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Veterinarian</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $a): ?>
              <tr>
                <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                <td><?= htmlspecialchars($a['time_slot']) ?></td>
                <td><?= htmlspecialchars($a['vet_name']) ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_appointment_id" value="<?= $a['id'] ?>">
                    <button class="button is-small is-danger" type="submit">Cancel</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Available Pets to Adopt -->
  <section class="section">
    <div class="container">
      <h2 class="title is-4">Available Pets to Adopt</h2>

      <!-- Filters -->
      <form method="GET">
        <div class="columns is-multiline">
          <!-- Type -->
          <div class="column is-2">
            <div class="field">
              <label class="label">Type</label>
              <div class="control">
                <div class="select is-fullwidth">
                  <select name="type">
                    <option value="">All</option>
                    <?php foreach ($types as $t): ?>
                      <option value="<?=htmlspecialchars($t)?>" <?= $t=== $typeFilter?'selected':''?>>
                        <?=htmlspecialchars($t)?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <!-- Breed -->
          <div class="column is-2">
            <div class="field">
              <label class="label">Breed</label>
              <div class="control">
                <div class="select is-fullwidth">
                  <select name="breed">
                    <option value="">All</option>
                    <?php foreach ($breeds as $b): ?>
                      <option value="<?=htmlspecialchars($b)?>" <?= $b=== $breedFilter?'selected':''?>>
                        <?=htmlspecialchars($b)?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <!-- Color -->
          <div class="column is-2">
            <div class="field">
              <label class="label">Color</label>
              <div class="control">
                <div class="select is-fullwidth">
                  <select name="color">
                    <option value="">All</option>
                    <?php foreach ($colors as $c): ?>
                      <option value="<?=htmlspecialchars($c)?>" <?= $c=== $colorFilter?'selected':''?>>
                        <?=htmlspecialchars($c)?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <!-- Health -->
          <div class="column is-2">
            <div class="field">
              <label class="label">Health</label>
              <div class="control">
                <div class="select is-fullwidth">
                  <select name="health_status">
                    <option value="">All</option>
                    <?php foreach ($healths as $h): ?>
                      <option value="<?=htmlspecialchars($h)?>" <?= $h=== $healthFilter?'selected':''?>>
                        <?=htmlspecialchars(ucfirst($h))?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <!-- Min Age -->
          <div class="column is-1">
            <div class="field">
              <label class="label">Min Age</label>
              <div class="control">
                <input class="input" type="number" name="age_min" min="0" value="<?= $ageMin ?>">
              </div>
            </div>
          </div>
          <!-- Max Age -->
          <div class="column is-1">
            <div class="field">
              <label class="label">Max Age</label>
              <div class="control">
                <input class="input" type="number" name="age_max" min="0" value="<?= $ageMax ?>">
              </div>
            </div>
          </div>
          <!-- Buttons -->
          <div class="column is-2">
            <div class="field">
              <label class="label">&nbsp;</label>
              <div class="control">
                <button class="button is-link is-fullwidth">Filter</button>
              </div>
            </div>
            <div class="field">
              <div class="control">
                <a href="adopter.php" class="button is-light is-fullwidth">Reset</a>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Pets Table -->
      <?php if (empty($pets)): ?>
        <div class="notification is-warning">
          No pets match your filters.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-striped is-fullwidth is-hoverable">
            <thead>
              <tr>
                <th>Name</th><th>Age</th><th>Type</th>
                <th>Breed</th><th>Color</th><th>Health</th>
                <th>Store</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($pets as $pet): ?>
              <tr>
                <td><?=htmlspecialchars($pet['name'])?></td>
                <td><?=htmlspecialchars($pet['age'])?></td>
                <td><?=htmlspecialchars($pet['type'])?></td>
                <td><?=htmlspecialchars($pet['breed'])?></td>
                <td><?=htmlspecialchars($pet['color'])?></td>
                <td><?=htmlspecialchars(ucfirst($pet['health_status']))?></td>
                <td><?=htmlspecialchars($pet['store_name'])?></td>
                <td>
                  <a class="button is-small is-primary" href="../adopt_pet.php?id=<?=$pet['id']?>">Adopt</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.navbar-burger').forEach(el => {
        el.addEventListener('click', () => {
          const menu = document.getElementById(el.dataset.target);
          el.classList.toggle('is-active');
          menu.classList.toggle('is-active');
        });
      });
      document.querySelectorAll('.notification .delete').forEach(btn => {
        btn.addEventListener('click', () => btn.parentNode.remove());
      });
    });
  </script>
</body>
</html>
