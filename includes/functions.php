<?php

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
