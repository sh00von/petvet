<?php
// dashboard/store_purchases.php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');
$user = current_user();

$stmt = $pdo->prepare("
  SELECT pc.purchase_date,
         u.name   AS buyer_name,
         pr.name  AS product_name,
         pc.unit_price,
         pc.quantity
    FROM purchases pc
    JOIN products pr ON pc.product_id = pr.id
    JOIN users u    ON pc.user_id    = u.id
   WHERE pr.store_id = ?
   ORDER BY pc.purchase_date DESC
");
$stmt->execute([$user['id']]);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sales History | PetAdopt</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation" aria-label="main navigation">
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
          <a class="navbar-item is-active" href="store_purchases.php">Sales</a>
          <a class="navbar-item" href="store_adoptions.php">Adoptions</a>
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
        <h1 class="title">Products You've Sold</h1>
        <h2 class="subtitle">Review your store’s sales history</h2>
      </div>
    </div>
  </section>

  <!-- Content -->
  <section class="section">
    <div class="container">
      <?php if (empty($purchases)): ?>
        <div class="notification is-warning">
          No sales have been recorded yet.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Buyer</th>
                <th>Product</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($purchases as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['purchase_date']) ?></td>
                <td><?= htmlspecialchars($row['buyer_name']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td>$<?= number_format($row['unit_price'], 2) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td>$<?= number_format($row['unit_price'] * $row['quantity'], 2) ?></td>
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
