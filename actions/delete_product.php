<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$page = max(1, intval($_POST['page'] ?? 1));
$page_size = intval($_POST['per_page'] ?? DEFAULT_PAGE_SIZE);
if (!in_array($page_size, ALLOWED_PAGE_SIZES, true)) {
    $page_size = DEFAULT_PAGE_SIZE;
}
$search = isset($_POST['search']) && is_string($_POST['search']) ? trim($_POST['search']) : '';
$return_url = '../index.php?' . http_build_query([
    'page' => $page,
    'per_page' => $page_size,
    'search' => $search,
]);

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . $return_url);
    exit;
}

try {
    $statement = $pdo->prepare('SELECT image FROM products WHERE id = :id');
    $statement->execute(['id' => $id]);
    $image = $statement->fetchColumn();

    if ($image !== false) {
        $statement = $pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $_SESSION['delete_error'] = 'The product could not be deleted. Please try again.';
    header('Location: ' . $return_url);
    exit;
}

if ($image === false) {
    header('Location: ' . $return_url);
    exit;
}

if ($image && is_file(__DIR__ . '/../uploads/' . basename($image))) {
    unlink(__DIR__ . '/../uploads/' . basename($image));
}

header('Location: ' . $return_url);
exit;
