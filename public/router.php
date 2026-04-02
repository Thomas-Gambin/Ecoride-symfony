<?php

// Routeur pour le serveur web intégré PHP (équivalent dev à Symfony CLI / nginx pour les assets statiques).
if (is_file($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require_once __DIR__.'/index.php';
