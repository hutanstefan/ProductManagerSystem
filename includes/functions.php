<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function setFlashMessage($type, $text)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'text' => $text,
    ];
}

function getFlashMessage()
{
    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return $message;
}

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatPrice($price)
{
    return '$' . number_format($price, 2);
}

function formatDate($date)
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? '-' : date('F j, Y', $timestamp);
}

function productImage($image)
{
    if ($image === null || $image === '') {
        return 'assets/images/product-placeholder.jpg';
    }

    return 'uploads/' . basename($image);
}

function pageUrl($page, $page_size)
{
    $query_params = $_GET;
    $query_params['page'] = $page;
    $query_params['per_page'] = $page_size;

    return '?' . http_build_query($query_params);
}
