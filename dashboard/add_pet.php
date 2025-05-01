<?php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $age           = (int) ($_POST['age'] ?? 0);
    $type          = trim($_POST['type'] ?? '');
    $breed         = trim($_POST['breed'] ?? '');
    $color         = trim($_POST['color'] ?? '');
    $health_status = $_POST['health_status'] ?? '';

    $validStatuses = ['good','sick','injured'];

    if (!$name || !$age || !$type || !$breed || !$color || !$health_status) {
        $errors[] = 'All fields are required.';
    } elseif (!in_array($health_status, $validStatuses, true)) {
        $errors[] = 'Invalid health status.';
    } else {
        $stmt = $pdo->prepare("
          INSERT INTO pets
            (name,age,type,breed,color,health_status,store_id)
           VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->execute([
          $name, $age, $type, $breed,
          $color, $health_status,
          $_SESSION['user_id']
        ]);
        header('Location: store.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add New Pet | PetAdopt</title>
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
  <section class="hero is-info">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Add New Pet</h1>
      </div>
    </div>
  </section>

  <!-- Form -->
  <section class="section">
    <div class="container">
      <?php if ($errors): ?>
        <div class="notification is-danger">
          <button class="delete"></button>
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="box">
        <form method="POST">
          <div class="field">
            <label class="label">Name</label>
            <div class="control">
              <input class="input" name="name" type="text" placeholder="Pet's name"
                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Age</label>
            <div class="control">
              <input class="input" name="age" type="number" min="0" placeholder="Age in years"
                value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Type</label>
            <div class="control">
              <input class="input" name="type" type="text" placeholder="e.g. Dog, Cat"
                value="<?= htmlspecialchars($_POST['type'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Breed</label>
            <div class="control">
              <input class="input" name="breed" type="text" placeholder="Breed"
                value="<?= htmlspecialchars($_POST['breed'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Color</label>
            <div class="control">
              <input class="input" name="color" type="text" placeholder="Color"
                value="<?= htmlspecialchars($_POST['color'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Health Status</label>
            <div class="control">
              <div class="select is-fullwidth">
                <select name="health_status">
                  <option value="">-- Select Status --</option>
                  <option value="good"    <?= (($_POST['health_status'] ?? '')==='good')    ? 'selected' : '' ?>>Good</option>
                  <option value="sick"    <?= (($_POST['health_status'] ?? '')==='sick')    ? 'selected' : '' ?>>Sick</option>
                  <option value="injured" <?= (($_POST['health_status'] ?? '')==='injured') ? 'selected' : '' ?>>Injured</option>
                </select>
              </div>
            </div>
          </div>

          <div class="field">
            <div class="control">
              <button class="button is-primary is-fullwidth">Add Pet</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // burger
      const burger = document.querySelector('.navbar-burger');
      const menu   = document.getElementById('navMenu');
      burger.addEventListener('click', () => {
        burger.classList.toggle('is-active');
        menu.classList.toggle('is-active');
      });
      // close notification
      document.querySelectorAll('.notification .delete').forEach(btn => {
        btn.addEventListener('click', () => btn.parentNode.remove());
      });
    });
  </script>
</body>
</html>
