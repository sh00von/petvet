<?php
// dashboard/store_products.php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');
$user = current_user();

// Fetch store products with total available (non-expired supply)
$stmt = $pdo->prepare("
  SELECT p.*,
         COALESCE(SUM(s.quantity),0) AS in_stock
    FROM products p
    LEFT JOIN supply s
      ON s.product_id = p.id
     AND s.expiry_date >= CURDATE()
   WHERE p.store_id = ?
   GROUP BY p.id
   ORDER BY p.created_at DESC
");
$stmt->execute([$user['id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Products & Inventory | PetAdopt</title>
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
          <a class="navbar-item is-active" href="store_products.php">Products</a>
          <a class="navbar-item" href="add_product.php">Add Product</a>
          <a class="navbar-item" href="add_supply.php">Add Supply</a>
        </div>
        <div class="navbar-end">
          <a class="navbar-item" href="../logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-warning">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Products & Inventory</h1>
        <h2 class="subtitle">Manage your store’s product catalog and stock</h2>
      </div>
    </div>
  </section>

  <!-- Table Section -->
  <section class="section">
    <div class="container">
      <?php if (empty($products)): ?>
        <div class="notification is-info">
          No products yet. <a href="add_product.php">Add your first product</a>.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>Name</th>
                <th>Price</th>
                <th>In Stock</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($products as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td>$<?= number_format($p['price'], 2) ?></td>
                <td><?= htmlspecialchars($p['in_stock']) ?></td>
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
