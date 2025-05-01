<?php
// dashboard/grooming.php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');
$user = current_user();

$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id      = (int)($_POST['pet_id'] ?? 0);
    $facility_id = (int)($_POST['facility_id'] ?? 0);
    $last_date   = $_POST['last_groom_date'] ?? '';
    $next_date   = $_POST['next_groom_date'] ?? '';

    if (!$pet_id || !$facility_id) {
        $errors[] = 'Pet and Facility are required.';
    } else {
        $upd = $pdo->prepare("
          UPDATE grooming
             SET facility_id=?, last_groom_date=?, next_groom_date=?
           WHERE pet_id=?
        ");
        $upd->execute([$facility_id,$last_date,$next_date,$pet_id]);
        if ($upd->rowCount()===0) {
            $ins = $pdo->prepare("
              INSERT INTO grooming
                (pet_id,facility_id,last_groom_date,next_groom_date)
              VALUES (?,?,?,?)
            ");
            $ins->execute([$pet_id,$facility_id,$last_date,$next_date]);
        }
        $success = 'Grooming info saved.';
    }
}

// Fetch rows
$stmt = $pdo->prepare("
  SELECT p.id AS pet_id, p.name AS pet_name,
         g.facility_id, g.last_groom_date, g.next_groom_date
    FROM pets p
    LEFT JOIN grooming g ON p.id=g.pet_id
   WHERE p.store_id=?
   ORDER BY p.name
");
$stmt->execute([$user['id']]);
$rows = $stmt->fetchAll();

// Facilities for dropdown
$facStmt = $pdo->query("SELECT id,name FROM facilities ORDER BY name");
$facilities = $facStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Grooming Schedule | PetAdopt</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="store.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="store.php">Dashboard</a>
        </div>
        <div class="navbar-end">
          <a class="navbar-item" href="../logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-link">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Grooming & Facility Assignment</h1>
      </div>
    </div>
  </section>

  <!-- Notifications -->
  <section class="section">
    <div class="container">
      <?php if ($errors): ?>
        <div class="notification is-danger">
          <button class="delete"></button>
          <?php foreach($errors as $e): ?><p><?=htmlspecialchars($e)?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="notification is-success">
          <button class="delete"></button>
          <p><?=htmlspecialchars($success)?></p>
        </div>
      <?php endif; ?>

      <!-- Table + Forms -->
      <div class="table-container">
        <table class="table is-fullwidth is-striped">
          <thead>
            <tr>
              <th>Pet</th><th>Facility</th><th>Last Groom</th><th>Next Groom</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($rows as $r): ?>
            <tr>
              <form method="POST">
                <td>
                  <?=htmlspecialchars($r['pet_name'])?>
                  <input type="hidden" name="pet_id" value="<?=$r['pet_id']?>">
                </td>
                <td>
                  <div class="select is-fullwidth">
                    <select name="facility_id" required>
                      <option value="">— select —</option>
                      <?php foreach($facilities as $f): ?>
                      <option value="<?=$f['id']?>"
                        <?= $r['facility_id']==$f['id']?'selected':''?>>
                        <?=htmlspecialchars($f['name'])?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </td>
                <td>
                  <input class="input" type="date" name="last_groom_date"
                    value="<?=htmlspecialchars($r['last_groom_date'] ?? '')?>">
                </td>
                <td>
                  <input class="input" type="date" name="next_groom_date"
                    value="<?=htmlspecialchars($r['next_groom_date'] ?? '')?>">
                </td>
                <td>
                  <button class="button is-primary is-small">Save</button>
                </td>
              </form>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // burger toggle
      document.querySelectorAll('.navbar-burger').forEach(el => {
        el.addEventListener('click', () => {
          const tgt = document.getElementById(el.dataset.target);
          el.classList.toggle('is-active');
          tgt.classList.toggle('is-active');
        });
      });
      // notification close
      document.querySelectorAll('.notification .delete').forEach(btn => {
        btn.addEventListener('click', () => btn.parentNode.remove());
      });
    });
  </script>
</body>
</html>
