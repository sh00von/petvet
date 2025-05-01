<?php
// dashboard/store.php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');
$user = current_user();

// Metrics for tiles
$totalPetsStmt = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE store_id = ?");
$totalPetsStmt->execute([$user['id']]);
$totalPets = $totalPetsStmt->fetchColumn();

$availPetsStmt = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE store_id = ? AND adopter_id IS NULL");
$availPetsStmt->execute([$user['id']]);
$availPets = $availPetsStmt->fetchColumn();

$totalProdStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE store_id = ?");
$totalProdStmt->execute([$user['id']]);
$totalProds = $totalProdStmt->fetchColumn();

$stockStmt = $pdo->prepare("
  SELECT COALESCE(SUM(s.quantity),0)
    FROM products p
    JOIN supply s ON s.product_id = p.id
   WHERE p.store_id = ? AND s.expiry_date >= CURDATE()
");
$stockStmt->execute([$user['id']]);
$totalStock = $stockStmt->fetchColumn();

// Fetch pets
$stmtPets = $pdo->prepare("
  SELECT p.*, u.name AS adopter_name
    FROM pets p
    LEFT JOIN users u ON p.adopter_id = u.id
   WHERE p.store_id = ?
   ORDER BY p.created_at DESC
");
$stmtPets->execute([$user['id']]);
$pets = $stmtPets->fetchAll(PDO::FETCH_ASSOC);

// Fetch products
$stmtProd = $pdo->prepare("
  SELECT pr.*,
         COALESCE(SUM(s.quantity),0) AS in_stock
    FROM products pr
    LEFT JOIN supply s
      ON s.product_id = pr.id
     AND s.expiry_date >= CURDATE()
   WHERE pr.store_id = ?
   GROUP BY pr.id
   ORDER BY pr.created_at DESC
");
$stmtProd->execute([$user['id']]);
$products = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

// Fetch facilities
$stmtFac = $pdo->query("SELECT * FROM facilities ORDER BY name");
$facilities = $stmtFac->fetchAll(PDO::FETCH_ASSOC);

// Fetch grooming assignments
$stmtGroom = $pdo->prepare("
  SELECT p.name AS pet_name,
         f.name AS facility_name,
         g.last_groom_date,
         g.next_groom_date
    FROM grooming g
    JOIN pets p       ON g.pet_id = p.id
    JOIN facilities f ON g.facility_id = f.id
   WHERE p.store_id = ?
   ORDER BY g.next_groom_date ASC
");
$stmtGroom->execute([$user['id']]);
$groomings = $stmtGroom->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Store Dashboard | PetAdopt</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light" role="navigation" aria-label="main navigation">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="store.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a href="add_pet.php" class="navbar-item">Add Pet</a>
          <a href="store_products.php" class="navbar-item">Products</a>
          <a href="add_product.php" class="navbar-item">Add Product</a>
          <a href="add_supply.php" class="navbar-item">Add Supply</a>
          <a href="facilities.php" class="navbar-item">Facilities</a>
          <a href="add_facility.php" class="navbar-item">Add Facility</a>
          <a href="grooming.php" class="navbar-item">Grooming</a>
          <a href="store_purchases.php" class="navbar-item">Purchases</a>
          <a href="store_adoptions.php" class="navbar-item">Adoptions</a>
        </div>
        <div class="navbar-end">
          <a href="../logout.php" class="navbar-item">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero is-primary">
    <div class="hero-body">
      <div class="container">
        <h1 class="title">Welcome, <?= htmlspecialchars($user['name']) ?></h1>
        <p>Your'e a Store Keeper</p>
      </div>
    </div>
  </section>

  <!-- Tiles -->
  <section class="section">
    <div class="container">
      <div class="columns is-multiline">
        <?php foreach ([
          ['Pets',      $totalPets,  'is-info'],
          ['Available', $availPets,  'is-success'],
          ['Products',  $totalProds, 'is-warning'],
          ['In Stock',  $totalStock, 'is-danger'],
        ] as list($label, $count, $color)): ?>
        <div class="column is-3">
          <div class="notification <?= $color ?>">
            <p class="title is-4"><?= $count ?></p>
            <p class="subtitle"><?= $label ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Detailed Sections -->
  <section class="section">
    <div class="container">

      <!-- Pets Card -->
      <div class="card">
        <header class="card-header">
          <p class="card-header-title">My Pets</p>
        </header>
        <div class="card-content">
          <?php if (empty($pets)): ?>
            <div class="notification is-warning">No pets added yet.</div>
          <?php else: ?>
            <div class="table-container">
              <table class="table is-striped is-fullwidth">
                <thead>
                  <tr>
                    <th>Name</th><th>Age</th><th>Type</th>
                    <th>Breed</th><th>Color</th><th>Health</th>
                    <th>Adopted?</th><th>Adopter</th>
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
                    <td><?= htmlspecialchars(ucfirst($pet['health_status'])) ?></td>
                    <td><?= $pet['adopter_id'] ? 'Yes' : 'No' ?></td>
                    <td>
                      <?= $pet['adopter_id']
                         ? htmlspecialchars($pet['adopter_name'])
                         : '-' ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Products Card -->
      <div class="card" style="margin-top: 1rem;">
        <header class="card-header">
          <p class="card-header-title">Products & Inventory</p>
        </header>
        <div class="card-content">
          <?php if (empty($products)): ?>
            <div class="notification is-warning">No products added yet.</div>
          <?php else: ?>
            <div class="table-container">
              <table class="table is-striped is-fullwidth">
                <thead>
                  <tr>
                    <th>Product</th><th>Price</th><th>In Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $pr): ?>
                  <tr>
                    <td><?= htmlspecialchars($pr['name']) ?></td>
                    <td>$<?= number_format($pr['price'], 2) ?></td>
                    <td><?= $pr['in_stock'] ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Facilities Card -->
      <div class="card" style="margin-top: 1rem;">
        <header class="card-header">
          <p class="card-header-title">Facilities</p>
        </header>
        <div class="card-content">
          <?php if (empty($facilities)): ?>
            <div class="notification is-warning">
              No facilities defined. <a href="add_facility.php">Add one</a>.
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
                    <td><?= htmlspecialchars($f['name']) ?></td>
                    <td><?= htmlspecialchars($f['address']) ?></td>
                    <td><?= htmlspecialchars($f['contact']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Grooming Card -->
      <div class="card" style="margin-top: 1rem;">
        <header class="card-header">
          <p class="card-header-title">Grooming Schedule</p>
        </header>
        <div class="card-content">
          <?php if (empty($groomings)): ?>
            <div class="notification is-warning">
              No grooming assignments yet. <a href="grooming.php">Set them up</a>.
            </div>
          <?php else: ?>
            <div class="table-container">
              <table class="table is-striped is-fullwidth">
                <thead>
                  <tr>
                    <th>Pet</th><th>Facility</th>
                    <th>Last Groom</th><th>Next Groom</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($groomings as $g): ?>
                  <tr>
                    <td><?= htmlspecialchars($g['pet_name']) ?></td>
                    <td><?= htmlspecialchars($g['facility_name']) ?></td>
                    <td><?= htmlspecialchars($g['last_groom_date'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($g['next_groom_date'] ?? '-') ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>

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
