<?php
// shop.php
require_once 'functions.php';

$stmt = $pdo->query("
  SELECT p.id, p.name, p.description, p.price,
         COALESCE(SUM(s.quantity),0) AS stock
    FROM products p
    LEFT JOIN supply s
      ON s.product_id = p.id
     AND s.expiry_date >= CURDATE()
   GROUP BY p.id
   HAVING stock > 0
   ORDER BY p.name
");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Shop Products | PetAdopt</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation" aria-label="main navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="index.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="index.php">Home</a>
          <?php if (is_logged_in()): ?>
            <a class="navbar-item" href="dashboard/adopter.php">Dashboard</a>
          <?php endif; ?>
        </div>
        <div class="navbar-end">
          <?php if (is_logged_in()): ?>
            <a class="navbar-item" href="logout.php">Logout</a>
          <?php else: ?>
            <a class="navbar-item" href="login.php">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-info">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Product Shop</h1>
        <h2 class="subtitle">Browse and purchase from our available products</h2>
      </div>
    </div>
  </section>

  <!-- Products Table -->
  <section class="section">
    <div class="container">
      <?php if (empty($items)): ?>
        <div class="notification is-warning">
          No items in stock.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
              <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>In Stock</th>
                <th>Quantity</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): ?>
              <tr>
                <td><?= htmlspecialchars($it['name']) ?></td>
                <td><?= htmlspecialchars($it['description']) ?></td>
                <td>$<?= number_format($it['price'], 2) ?></td>
                <td><?= htmlspecialchars($it['stock']) ?></td>
                <td>
                  <?php if (is_logged_in() && current_user()['role'] === 'adopter'): ?>
                    <form method="POST" action="purchase_product.php" class="field has-addons">
                      <p class="control">
                        <input
                          class="input"
                          type="number"
                          name="quantity"
                          min="1"
                          max="<?= $it['stock'] ?>"
                          value="1"
                          style="width: 80px;">
                        <input type="hidden" name="product_id" value="<?= $it['id'] ?>">
                      </p>
                      <p class="control">
                        <button class="button is-primary">Buy</button>
                      </p>
                    </form>
                  <?php else: ?>
                    <a href="login.php" class="button is-link">Login to Buy</a>
                  <?php endif; ?>
                </td>
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
