<?php
// purchase_product.php
require_once 'functions.php';
require_role('adopter');

$pid = (int)($_POST['product_id']  ?? 0);
$qty = (int)($_POST['quantity']    ?? 0);

if (!$pid || $qty < 1) {
    $_SESSION['flash_error'] = 'Invalid product or quantity.';
    header('Location: shop.php');
    exit;
}

// 1) Check available stock
$chk = $pdo->prepare("
  SELECT COALESCE(SUM(quantity),0) AS stock
    FROM supply
   WHERE product_id = ? AND expiry_date >= CURDATE()
");
$chk->execute([$pid]);
$stock = (int)$chk->fetchColumn();

if ($stock < $qty) {
    $_SESSION['flash_error'] = 'Only ' . $stock . ' unit(s) in stock.';
    header('Location: shop.php');
    exit;
}

// 2) Fetch current unit price
$pr = $pdo->prepare("SELECT price FROM products WHERE id = ?");
$pr->execute([$pid]);
$unit_price = $pr->fetchColumn();

// 3) Begin transaction
try {
    $pdo->beginTransaction();

    // 4) Deduct from supply batches (oldest first)
    $remaining = $qty;

    $batchStmt  = $pdo->prepare("
      SELECT id, quantity
        FROM supply
       WHERE product_id = ? AND expiry_date >= CURDATE()
       ORDER BY expiry_date ASC
    ");
    $updateStmt = $pdo->prepare("UPDATE supply SET quantity = ? WHERE id = ?");
    $deleteStmt = $pdo->prepare("DELETE FROM supply WHERE id = ?");

    $batchStmt->execute([$pid]);
    while ($remaining > 0 && ($batch = $batchStmt->fetch(PDO::FETCH_ASSOC))) {
        $deduct = min($batch['quantity'], $remaining);
        $newQty = $batch['quantity'] - $deduct;

        if ($newQty > 0) {
            $updateStmt->execute([$newQty, $batch['id']]);
        } else {
            $deleteStmt->execute([$batch['id']]);
        }

        $remaining -= $deduct;
    }

    // 5) Record purchase (with quantity and unit_price)
    $ins = $pdo->prepare("
      INSERT INTO purchases (user_id, product_id, quantity, unit_price)
      VALUES (?, ?, ?, ?)
    ");
    $ins->execute([
        $_SESSION['user_id'],
        $pid,
        $qty,
        $unit_price
    ]);

    // 6) Commit
    $pdo->commit();
    $_SESSION['flash_success'] = "You bought {$qty} unit(s).";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Purchase failed: ' . $e->getMessage();
}

header('Location: shop.php');
exit;
