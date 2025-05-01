<?php
// dashboard/user_purchases.php
require_once __DIR__ . '/../functions.php';
require_role('adopter');
$user = current_user();

$stmt = $pdo->prepare("
  SELECT pc.purchase_date,
         pr.name       AS product_name,
         pc.unit_price,
         pc.quantity
    FROM purchases pc
    JOIN products pr ON pc.product_id = pr.id
   WHERE pc.user_id = ?
   ORDER BY pc.purchase_date DESC
");
$stmt->execute([$user['id']]);
$hist = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Purchase History | PetAdopt</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation" aria-label="main navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="adopter.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="adopter.php">Dashboard</a>
          <a class="navbar-item" href="adoption_history.php">Adoptions</a>
          <a class="navbar-item is-active" href="user_purchases.php">Purchases</a>
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
        <h1 class="title">My Purchase History</h1>
        <h2 class="subtitle">Review all the products you've bought</h2>
      </div>
    </div>
  </section>

  <!-- Content -->
  <section class="section">
    <div class="container">
      <?php if (empty($hist)): ?>
        <div class="notification is-warning">
          You have not made any purchases yet.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($hist as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['purchase_date']) ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td>$<?= number_format($r['unit_price'], 2) ?></td>
                <td><?= htmlspecialchars($r['quantity']) ?></td>
                <td>$<?= number_format($r['unit_price'] * $r['quantity'], 2) ?></td>
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
