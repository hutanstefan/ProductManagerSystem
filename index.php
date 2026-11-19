<?php
require_once __DIR__ . '/includes/db.php';

$count = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo 'Products: ' . $count;