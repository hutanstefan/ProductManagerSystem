<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Product Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="page-layout">
    <header class="site-header">
        <nav class="site-nav container">
            <a class="site-brand" href="index.php">Product Manager</a>
            <a href="index.php">Products</a>
        </nav>
    </header>
    <?php
    $flash_message = getFlashMessage();
    ?>
    <?php if ($flash_message): ?>
        <div class="container">
            <p class="flash-message <?= e($flash_message['type']) ?>" role="alert">
                <?= e($flash_message['text']) ?>
            </p>
        </div>
    <?php endif; ?>
