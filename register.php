<?php
require_once 'functions.php';
if (is_logged_in()) redirect_to_dashboard();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pw    = $_POST['password'];
    $role  = $_POST['role'];

    if (!$name || !$email || !$pw || !$role) {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    } elseif (!in_array($role, ['adopter','store_owner','vet'], true)) {
        $errors[] = 'Invalid role.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already in use.';
        } else {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
              "INSERT INTO users (name,email,password,role)
               VALUES (?,?,?,?)"
            );
            $stmt->execute([$name,$email,$hash,$role]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            redirect_to_dashboard();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register | PetAdopt</title>
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
          <a href="login.php" class="navbar-item">Login</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Registration Form -->
  <section class="section">
    <div class="container">
      <div class="column is-6 is-offset-3">
        <h1 class="title has-text-centered">Create an Account</h1>

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
              <label class="label">Name</label>
              <div class="control">
                <input
                  class="input"
                  type="text"
                  name="name"
                  placeholder="Your full name"
                  value="<?= htmlspecialchars($name ?? '') ?>">
              </div>
            </div>

            <div class="field">
              <label class="label">Email</label>
              <div class="control">
                <input
                  class="input"
                  type="email"
                  name="email"
                  placeholder="you@example.com"
                  value="<?= htmlspecialchars($email ?? '') ?>">
              </div>
            </div>

            <div class="field">
              <label class="label">Password</label>
              <div class="control">
                <input
                  class="input"
                  type="password"
                  name="password"
                  placeholder="Choose a secure password">
              </div>
            </div>

            <div class="field">
              <label class="label">Role</label>
              <div class="control">
                <div class="select is-fullwidth">
                  <select name="role">
                    <option value="">Select Role</option>
                    <option value="adopter"
                      <?= (isset($role) && $role==='adopter') ? 'selected' : '' ?>>
                      Adopter
                    </option>
                    <option value="store_owner"
                      <?= (isset($role) && $role==='store_owner') ? 'selected' : '' ?>>
                      Store Owner
                    </option>
                    <option value="vet"
                      <?= (isset($role) && $role==='vet') ? 'selected' : '' ?>>
                      Veterinarian
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <div class="field">
              <div class="control">
                <button class="button is-primary is-fullwidth">
                  Register
                </button>
              </div>
            </div>
          </form>
        </div>

        <p class="has-text-centered">
          Already have an account?
          <a href="login.php">Login here</a>.
        </p>
      </div>
    </div>
  </section>

  <script>
    // Bulma burger toggle
    document.addEventListener('DOMContentLoaded', () => {
      const burger = document.querySelector('.navbar-burger');
      const menu   = document.getElementById('navMenu');
      burger.addEventListener('click', () => {
        burger.classList.toggle('is-active');
        menu.classList.toggle('is-active');
      });
      // Close notification
      document.querySelectorAll('.notification .delete').forEach($del => {
        const $notif = $del.parentNode;
        $del.addEventListener('click', () => { $notif.remove(); });
      });
    });
  </script>
</body>
</html>
