<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

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
    if ($is_ajax) {
        jsonResponse(false, 'Product not found');
    }
    setFlashMessage('error', 'Product not found');
    redirect($return_url);
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
    if ($is_ajax) {
        jsonResponse(false, 'The product could not be deleted. Please try again');
    }
    setFlashMessage('error', 'The product could not be deleted. Please try again');
    redirect($return_url);
}

if ($image === false) {
    if ($is_ajax) {
        jsonResponse(false, 'Product not found');
    }
    setFlashMessage('error', 'Product not found');
    redirect($return_url);
}

if ($image && is_file(__DIR__ . '/../uploads/' . basename($image))) {
    unlink(__DIR__ . '/../uploads/' . basename($image));
}

if ($is_ajax) {
    jsonResponse(true, 'Product deleted');
}

setFlashMessage('success', 'Product deleted');
redirect($return_url);
