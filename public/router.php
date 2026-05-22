<?php

if (is_file($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

if (!isset($_SERVER['HTTP_AUTHORIZATION']) && is_array($headers = getallheaders())) {
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    if (is_string($authorization) && $authorization !== '') {
        $_SERVER['HTTP_AUTHORIZATION'] = $authorization;
    }
}

require_once __DIR__.'/index.php';
