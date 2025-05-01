<?php
// dashboard/adoption_history.php
require_once __DIR__ . '/../functions.php';
require_role('adopter');
$user = current_user();

$stmt = $pdo->prepare("
  SELECT ah.adoption_date,
         p.name, p.age, p.type, p.breed, p.color, p.health_status
    FROM adoption_history ah
    JOIN pets p ON ah.pet_id = p.id
   WHERE ah.user_id = ?
   ORDER BY ah.adoption_date DESC
");
$stmt->execute([$user['id']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Adoption History | PetAdopt</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar is-light">
    <div class="container">
      <div class="navbar-brand">
        <a class="navbar-item" href="adopter.php"><strong>PetAdopt</strong></a>
        <a role="button" class="navbar-burger" data-target="navMenu">
          <span></span><span></span><span></span>
        </a>
      </div>
      <div id="navMenu" class="navbar-menu">
        <div class="navbar-start">
          <a class="navbar-item" href="adopter.php">Back to Dashboard</a>
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
        <h1 class="title">My Adoption History</h1>
      </div>
    </div>
  </section>

  <!-- Table -->
  <section class="section">
    <div class="container">
      <?php if (empty($history)): ?>
        <div class="notification is-warning">
          You have not adopted any pets yet.
        </div>
      <?php else: ?>
        <div class="table-container">
          <table class="table is-striped is-fullwidth">
            <thead>
              <tr>
                <th>Date</th><th>Name</th><th>Age</th><th>Type</th>
                <th>Breed</th><th>Color</th><th>Health</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($history as $row): ?>
              <tr>
                <td><?=htmlspecialchars($row['adoption_date'])?></td>
                <td><?=htmlspecialchars($row['name'])?></td>
                <td><?=htmlspecialchars($row['age'])?></td>
                <td><?=htmlspecialchars($row['type'])?></td>
                <td><?=htmlspecialchars($row['breed'])?></td>
                <td><?=htmlspecialchars($row['color'])?></td>
                <td><?=htmlspecialchars(ucfirst($row['health_status']))?></td>
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
