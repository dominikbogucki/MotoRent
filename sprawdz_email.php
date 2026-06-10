<?php
include 'includes/db.php';

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM uzytkownicy WHERE email = ?");
    $stmt->execute([$email]);

    echo $stmt->fetch() ? 'zajety' : 'wolny';
}
