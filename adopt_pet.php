<?php
// adopt_pet.php
require_once 'functions.php';
require_role('adopter');

if (!isset($_GET['id'])) {
    header('Location: dashboard/adopter.php');
    exit;
}

$pet_id = (int)$_GET['id'];

// 1) Attempt to adopt
$stmt = $pdo->prepare("
  UPDATE pets
     SET adopter_id = ?
   WHERE id = ? 
     AND adopter_id IS NULL
");
$stmt->execute([ $_SESSION['user_id'], $pet_id ]);

if ($stmt->rowCount()) {
    // 2) Record adoption event
    $ins = $pdo->prepare("
      INSERT INTO adoption_history (pet_id,user_id)
      VALUES (?,?)
    ");
    $ins->execute([ $pet_id, $_SESSION['user_id'] ]);
}

header('Location: dashboard/adopter.php');
exit;
