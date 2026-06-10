<?php
include 'includes/db.php';

$stmt = $pdo->query("SELECT * FROM opinie ORDER BY data_dodania DESC LIMIT 5");
$opinie = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($opinie);
