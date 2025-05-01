<?php
// dashboard/facilities.php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');

$facilities = $pdo->query("SELECT * FROM facilities ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Facilities | PetAdopt</title>
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
          <a class="navbar-item" href="add_facility.php">Add Facility</a>
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
        <h1 class="title">Facilities</h1>
      </div>
    </div>
  </section>

  <!-- Table -->
  <section class="section">
    <div class="container">
      <?php if (empty($facilities)): ?>
        <div class="notification is-warning">
          No facilities yet. <a href="add_facility.php">Add one</a>.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-fullwidth">
            <thead>
              <tr><th>Name</th><th>Address</th><th>Contact</th></tr>
            </thead>
            <tbody>
            <?php foreach ($facilities as $f): ?>
              <tr>
                <td><?=htmlspecialchars($f['name'])?></td>
                <td><?=htmlspecialchars($f['address'])?></td>
                <td><?=htmlspecialchars($f['contact'])?></td>
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
      const burger = document.querySelector('.navbar-burger');
      const menu = document.getElementById('navMenu');
      burger.addEventListener('click', () => {
        burger.classList.toggle('is-active');
        menu.classList.toggle('is-active');
      });
    });
  </script>
</body>
</html>
