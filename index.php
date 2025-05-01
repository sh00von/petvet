<?php
// index.php

require_once __DIR__ . '/functions.php';

// If already logged in → dashboard
if (is_logged_in()) {
    redirect_to_dashboard();
}

$stmt = $pdo->query("
    SELECT p.*, u.name AS store_name
      FROM pets p
      JOIN users u ON p.store_id = u.id
     WHERE p.adopter_id IS NULL
  ORDER BY p.created_at DESC
");
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>PetAdopt</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="index.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-end">
          <a href="register.php" class="navbar-item">Register</a>
          <a href="login.php" class="navbar-item">Login</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-primary is-bold">
    <div class="hero-body">
      <div class="container has-text-centered">
        <h1 class="title">Welcome to PetAdopt</h1>
        <h2 class="subtitle">
          Browse pets available for adoption or
          <a class="has-text-weight-bold" href="login.php">login</a> to adopt.
        </h2>
      </div>
    </div>
  </section>

  <!-- Available Pets Table -->
  <section class="section">
    <div class="container">
      <h2 class="title is-4">Available Pets</h2>

      <?php if (empty($pets)): ?>
        <div class="notification is-warning">
          No pets available right now. Check back soon!
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-striped is-fullwidth">
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
                <td><?= htmlspecialchars($pet['name']) ?></td>
                <td><?= htmlspecialchars($pet['age']) ?></td>
                <td><?= htmlspecialchars($pet['type']) ?></td>
                <td><?= htmlspecialchars($pet['breed']) ?></td>
                <td><?= htmlspecialchars($pet['color']) ?></td>
                <td><?= htmlspecialchars($pet['health_status']) ?></td>
                <td><?= htmlspecialchars($pet['store_name']) ?></td>
                <td>
                  <!-- Clearly indicate login is required -->
                  <a href="login.php" class="button is-small is-warning">
                    Login Required
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Burger Script -->
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
