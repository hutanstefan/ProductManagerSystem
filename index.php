<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$page_size = intval($_GET['per_page'] ?? DEFAULT_PAGE_SIZE);

if (!in_array($page_size, ALLOWED_PAGE_SIZES, true)) {
    $page_size = DEFAULT_PAGE_SIZE;
}

$current_page = intval($_GET['page'] ?? 1);

$total_products = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$total_pages = max(1, intval(ceil($total_products / $page_size)));

if ($current_page < 1) {
    $current_page = 1;
} elseif ($current_page > $total_pages) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $page_size;

$statement = $pdo->prepare(
    'SELECT id, name, description, price, image, availability_date, in_stock
     FROM products
     ORDER BY id DESC
     LIMIT :limit OFFSET :offset'
);
$statement->bindValue(':limit', $page_size, PDO::PARAM_INT);
$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
$statement->execute();
$products = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<main class="container">
    <h1>Product List</h1>

    <div class="table-wrapper">
        <table class="products-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Availability Date</th>
                    <th>Stock Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td class="empty-state" colspan="7">No products found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td data-label="Name"><?= e($product['name']) ?></td>
                            <td data-label="Description"><?= e($product['description']) ?></td>
                            <td data-label="Price"><?= e(formatPrice($product['price'])) ?></td>
                            <td data-label="Image">
                                <img
                                    class="product-image"
                                    src="<?= e(productImage($product['image'])) ?>"
                                    alt="<?= e($product['name']) ?>"
                                >
                            </td>
                            <td data-label="Availability Date"><?= e(formatDate($product['availability_date'])) ?></td>
                            <td data-label="Stock Status">
                                <span class="stock-status <?= $product['in_stock'] ? 'in-stock' : 'out-of-stock' ?>">
                                    <?= $product['in_stock'] ? 'In stock' : 'Out of stock' ?>
                                </span>
                            </td>
                            <td data-label="Actions">-</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-controls">
        <?php if ($total_pages > 1): ?>
            <nav class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?= e(pageUrl($current_page - 1, $page_size)) ?>">Previous</a>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <?php if ($page === $current_page): ?>
                        <span class="current-page"><?= $page ?></span>
                    <?php else: ?>
                        <a href="<?= e(pageUrl($page, $page_size)) ?>"><?= $page ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= e(pageUrl($current_page + 1, $page_size)) ?>">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <form class="page-size-form" method="get" action="index.php">
            <input type="hidden" name="page" value="1">
            <label for="per-page">Products per page</label>
            <select id="per-page" name="per_page">
                <?php foreach (ALLOWED_PAGE_SIZES as $size): ?>
                    <option value="<?= $size ?>" <?= $page_size === $size ? 'selected' : '' ?>>
                        <?= $size ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Apply</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
