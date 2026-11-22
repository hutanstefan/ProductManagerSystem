<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$is_edit = isset($_GET['id']);
$product_id = $is_edit ? intval($_GET['id']) : null;
$product = null;

if ($is_edit) {
    $statement = $pdo->prepare(
        'SELECT id, name, description, price, image, availability_date, in_stock
         FROM products
         WHERE id = :id'
    );
    $statement->execute(['id' => $product_id]);
    $product = $statement->fetch();

    if (!$product) {
        setFlashMessage('error', 'Product not found');
        header('Location: index.php');
        exit;
    }
}

$values = [
    'name' => $product['name'] ?? '',
    'description' => $product['description'] ?? '',
    'price' => $product['price'] ?? '',
    'availability_date' => $product['availability_date'] ?? '',
    'in_stock' => $product ? (bool) $product['in_stock'] : false,
];
$errors = [];
$max_image_size = 2 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['name', 'description', 'price', 'availability_date'] as $field) {
        $values[$field] = isset($_POST[$field]) && is_string($_POST[$field])
            ? trim($_POST[$field])
            : '';
    }
    $values['in_stock'] = isset($_POST['in_stock']);

    if ($values['name'] === '') {
        $errors['name'] = 'Name is required.';
    } elseif (strlen($values['name']) > 255) {
        $errors['name'] = 'Name must be 255 characters or fewer.';
    }

    if (!is_numeric($values['price']) || (float) $values['price'] < 0 || (float) $values['price'] > 99999999.99) {
        $errors['price'] = 'Price must be between 0 and 99,999,999.99.';
    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $values['price'])) {
        $errors['price'] = 'Enter a valid price with up to 2 decimal places.';
    }

    if ($values['availability_date'] !== '') {
        $timestamp = strtotime($values['availability_date']);
        if ($timestamp === false || date('Y-m-d', $timestamp) !== $values['availability_date']) {
            $errors['availability_date'] = 'Enter a valid availability date.';
        }
    }

    $image_name = $product['image'] ?? null;
    $image_path = null;
    $upload = $_FILES['image'] ?? null;
    if ($upload !== null && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = 'The image could not be uploaded.';
        } elseif ($upload['size'] > $max_image_size) {
            $errors['image'] = 'Image must be 2 MB or smaller.';
        } else {
            $image_info = getimagesize($upload['tmp_name']);
            if ($image_info === false) {
                $errors['image'] = 'Choose a valid JPEG, PNG, GIF, or WebP image.';
            } else {
                $image_extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];
                if (!isset($image_extensions[$image_info['mime']])) {
                    $errors['image'] = 'Choose a valid JPEG, PNG, GIF, or WebP image.';
                } else {
                    $image_name = uniqid() . '.' . $image_extensions[$image_info['mime']];
                    $image_path = __DIR__ . '/uploads/' . $image_name;
                }
            }
        }
    }

    if (empty($errors) && $image_path !== null) {
        if (!move_uploaded_file($upload['tmp_name'], $image_path)) {
            $errors['image'] = 'The image could not be saved.';
        }
    }

    if (empty($errors)) {
        try {
            $parameters = [
                'name' => $values['name'],
                'description' => $values['description'] !== '' ? $values['description'] : null,
                'price' => $values['price'],
                'image' => $image_name,
                'availability_date' => $values['availability_date'] !== '' ? $values['availability_date'] : null,
                'in_stock' => $values['in_stock'] ? 1 : 0,
            ];

            $old_image_name = $product['image'] ?? null;
            if ($is_edit) {
                $parameters['id'] = $product_id;
                $query = 'UPDATE products
                          SET name = :name, description = :description, price = :price, image = :image,
                              availability_date = :availability_date, in_stock = :in_stock
                          WHERE id = :id';
            } else {
                $query =
                    'INSERT INTO products (name, description, price, image, availability_date, in_stock)
                     VALUES (:name, :description, :price, :image, :availability_date, :in_stock)';
            }

            $statement = $pdo->prepare($query);
            $statement->execute($parameters);

            if ($is_edit && $image_path !== null && $old_image_name) {
                $old_image_path = __DIR__ . '/uploads/' . $old_image_name;
                if (is_file($old_image_path)) {
                    unlink($old_image_path);
                }
            }

            setFlashMessage('success', $is_edit ? 'Product updated' : 'Product created');
            header('Location: index.php');
            exit;
        } catch (PDOException $exception) {
            if ($image_path !== null && is_file($image_path)) {
                unlink($image_path);
            }
            error_log($exception->getMessage());
            $errors['form'] = 'The product could not be saved. Please try again.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<main class="container">
    <h1><?= $is_edit ? 'Edit product' : 'Add product' ?></h1>

    <?php if (isset($errors['form'])): ?>
        <p class="form-error" role="alert"><?= e($errors['form']) ?></p>
    <?php endif; ?>

    <form class="product-form" method="post" action="form.php<?= $is_edit ? '?id=' . $product_id : '' ?>" enctype="multipart/form-data">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?= e($values['name']) ?>" required>
        <?php if (isset($errors['name'])): ?>
            <span class="field-error" role="alert"><?= e($errors['name']) ?></span>
        <?php endif; ?>

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= e($values['description']) ?></textarea>

        <label for="price">Price</label>
        <input id="price" name="price" type="number" step="0.01" min="0" value="<?= e($values['price']) ?>" required>
        <?php if (isset($errors['price'])): ?>
            <span class="field-error" role="alert"><?= e($errors['price']) ?></span>
        <?php endif; ?>

        <label for="image">Image</label>
        <?php if ($is_edit && !empty($product['image'])): ?>
            <img class="product-image" src="<?= e(productImage($product['image'])) ?>" alt="<?= e($product['name']) ?>">
        <?php endif; ?>
        <input id="image" name="image" type="file" accept="image/*">
        <?php if (isset($errors['image'])): ?>
            <span class="field-error" role="alert"><?= e($errors['image']) ?></span>
        <?php endif; ?>

        <label for="availability_date">Availability date</label>
        <input id="availability_date" name="availability_date" type="date" value="<?= e($values['availability_date']) ?>">
        <?php if (isset($errors['availability_date'])): ?>
            <span class="field-error" role="alert"><?= e($errors['availability_date']) ?></span>
        <?php endif; ?>

        <label for="in_stock">
            <input id="in_stock" name="in_stock" type="checkbox" value="1" <?= $values['in_stock'] ? 'checked' : '' ?>>
            In stock
        </label>

        <div class="form-actions">
            <button type="submit"><?= $is_edit ? 'Update product' : 'Save product' ?></button>
            <a href="index.php">Cancel</a>
        </div>
    </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
