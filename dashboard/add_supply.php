<?php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');
$user = current_user();
$errors = [];

// Fetch products to populate dropdown
$prods = $pdo->prepare("SELECT id,name FROM products WHERE store_id = ?");
$prods->execute([$user['id']]);
$products = $prods->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 0);
    $exp = $_POST['expiry_date'] ?? '';

    if (!$pid || $qty < 1 || !$exp) {
        $errors[] = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("
          INSERT INTO supply (product_id,quantity,expiry_date)
          VALUES (?,?,?)
        ");
        $stmt->execute([$pid,$qty,$exp]);
        header('Location: store_products.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Supply | PetAdopt</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="store_products.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="store_products.php">Back to Products</a>
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
        <h1 class="title">Add Supply</h1>
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
            <label class="label">Product</label>
            <div class="control">
              <div class="select is-fullwidth">
                <select name="product_id">
                  <option value="">-- choose product --</option>
                  <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"
                      <?= (($_POST['product_id'] ?? '')==$p['id'])?'selected':'' ?>>
                      <?= htmlspecialchars($p['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="field">
            <label class="label">Quantity</label>
            <div class="control">
              <input class="input" name="quantity" type="number" min="1" placeholder="Units"
                value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <label class="label">Expiry Date</label>
            <div class="control">
              <input class="input" name="expiry_date" type="date"
                value="<?= htmlspecialchars($_POST['expiry_date'] ?? '') ?>">
            </div>
          </div>

          <div class="field">
            <div class="control">
              <button class="button is-primary is-fullwidth">Add Supply</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelector('.navbar-burger').addEventListener('click', () => {
        document.querySelector('.navbar-burger').classList.toggle('is-active');
        document.getElementById('navMenu').classList.toggle('is-active');
      });
      document.querySelectorAll('.notification .delete').forEach(btn => {
        btn.onclick = () => btn.parentNode.remove();
      });
    });
  </script>
</body>
</html>
