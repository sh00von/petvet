<?php
require_once __DIR__ . '/../functions.php';
require_role('store_owner');
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = trim($_POST['name']);
  $desc = trim($_POST['description']);
  $price= trim($_POST['price']);

  if (!$name||!$price) {
    $errors[] = 'Name and price are required.';
  } elseif (!is_numeric($price)) {
    $errors[] = 'Price must be a number.';
  }

  if (empty($errors)) {
    $stmt = $pdo->prepare("
      INSERT INTO products (name,description,price,store_id)
      VALUES (?,?,?,?)
    ");
    $stmt->execute([$name,$desc,$price,$user['id']]);
    header('Location: store_products.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../css/style.css">
  <title>Add Product</title>
</head>
<body>
<nav>
  <a href="store_products.php">← Back to Products</a> |
  <a href="../logout.php">Logout</a>
</nav>
<h1>Add Product</h1>
<?php foreach($errors as $e): ?>
  <p style="color:red"><?=htmlspecialchars($e)?></p>
<?php endforeach; ?>
<form method="POST">
  <input name="name"        placeholder="Product Name" value="<?=@htmlspecialchars($name)?>">
  <textarea name="description" placeholder="Description"><?=@htmlspecialchars($desc)?></textarea>
  <input name="price"       placeholder="Price (e.g. 19.99)" value="<?=@htmlspecialchars($price)?>">
  <button type="submit">Add</button>
</form>
</body>
</html>
