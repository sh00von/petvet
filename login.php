<?php
require_once 'functions.php';
if (is_logged_in()) redirect_to_dashboard();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pw    = $_POST['password'];

    if (!$email || !$pw) {
        $errors[] = 'Email & password required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($pw, $u['password'])) {
            $_SESSION['user_id'] = $u['id'];
            redirect_to_dashboard();
        } else {
            $errors[] = 'Wrong credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login | PetAdopt</title>
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
        </div>
      </div>
    </div>
  </nav>

  <!-- Login Form -->
  <section class="section">
    <div class="container">
      <div class="column is-5 is-offset-4">
        <h1 class="title has-text-centered">Login to Your Account</h1>

        <?php if ($errors): ?>
          <div class="notification is-danger">
            <button class="delete"></button>
            <?php foreach ($errors as $e): ?>
              <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="box">
          <form method="POST">
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
                  placeholder="Enter your password">
              </div>
            </div>

            <div class="field">
              <div class="control">
                <button class="button is-primary is-fullwidth">
                  Login
                </button>
              </div>
            </div>
          </form>
        </div>

        <p class="has-text-centered">
          Don't have an account?
          <a href="register.php">Register here</a>.
        </p>
      </div>
    </div>
  </section>

  <script>
    // Bulma burger toggle
    document.addEventListener('DOMContentLoaded', () => {
      const burger = document.querySelector('.navbar-burger');
      const menu   = document.getElementById('navMenu');
      if (burger) {
        burger.addEventListener('click', () => {
          burger.classList.toggle('is-active');
          menu.classList.toggle('is-active');
        });
      }
      // Close notifications
      document.querySelectorAll('.notification .delete').forEach($delete => {
        const $notif = $delete.parentNode;
        $delete.addEventListener('click', () => { $notif.remove(); });
      });
    });
  </script>
</body>
</html>
