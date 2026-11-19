<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$products = $pdo->query(
    'SELECT id, name, description, price, image, availability_date, in_stock
     FROM products
     ORDER BY id DESC'
)->fetchAll();

$pageTitle = 'Products';
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
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
