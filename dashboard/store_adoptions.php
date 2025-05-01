<?php
// dashboard/store_adoptions.php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');
$user = current_user();

// Fetch all adoptions of this store's pets
$stmt = $pdo->prepare("
  SELECT ah.adoption_date,
         p.name AS pet_name,
         u.name AS adopter_name
    FROM adoption_history ah
    JOIN pets p ON ah.pet_id = p.id
    JOIN users u ON ah.user_id = u.id
   WHERE p.store_id = ?
   ORDER BY ah.adoption_date DESC
");
$stmt->execute([$user['id']]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Adoption History | PetAdopt</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation">
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
          <a class="navbar-item" href="store_products.php">Products</a>
          <a class="navbar-item" href="store_purchases.php">Purchases</a>
          <a class="navbar-item is-active" href="store_adoptions.php">Adoptions</a>
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
        <h1 class="title">Pets You've Placed</h1>
        <h2 class="subtitle">Adoption history of your store’s pets</h2>
      </div>
    </div>
  </section>

  <!-- Table Section -->
  <section class="section">
    <div class="container">
      <?php if (empty($records)): ?>
        <div class="notification is-warning">
          No pets have been adopted yet.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>Adopted On</th>
                <th>Pet Name</th>
                <th>Adopter</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($records as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['adoption_date']) ?></td>
                <td><?= htmlspecialchars($row['pet_name']) ?></td>
                <td><?= htmlspecialchars($row['adopter_name']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Bulma Burger Script -->
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
