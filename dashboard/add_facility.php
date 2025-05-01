<?php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if ($name === '') {
        $errors[] = 'Facility name is required.';
    } else {
        $stmt = $pdo->prepare("
          INSERT INTO facilities (name,address,contact)
          VALUES (?,?,?)
        ");
        $stmt->execute([$name, $address, $contact]);
        header('Location: facilities.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Facility | PetAdopt</title>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="facilities.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="facilities.php">Back to Facilities</a>
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
        <h1 class="title">Add New Facility</h1>
      </div>
    </div>
  </section>

  <!-- Form -->
  <section class="section">
    <div class="container">
      <?php if ($errors): ?>
        <div class="notification is-danger">
          <button class="delete"></button>
          <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="box">
        <form method="POST">
          <div class="field">
            <label class="label">Facility Name</label>
            <div class="control">
              <input class="input"
                     type="text"
                     name="name"
                     placeholder="Name"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Address</label>
            <div class="control">
              <input class="input"
                     type="text"
                     name="address"
                     placeholder="Optional"
                     value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Contact</label>
            <div class="control">
              <input class="input"
                     type="text"
                     name="contact"
                     placeholder="Optional"
                     value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <div class="control">
              <button class="button is-primary is-fullwidth">
                Add Facility
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // navbar burger
      const burger = document.querySelector('.navbar-burger');
      const menu   = document.getElementById('navMenu');
      burger.addEventListener('click', () => {
        burger.classList.toggle('is-active');
        menu.classList.toggle('is-active');
      });
      // close notifications
      document.querySelectorAll('.notification .delete').forEach(btn =>
        btn.addEventListener('click', () => btn.parentNode.remove())
      );
    });
  </script>
</body>
</html>
