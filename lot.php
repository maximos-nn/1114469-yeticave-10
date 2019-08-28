<?php
require 'bootstrap.php';

if (!isset($_GET['id'])) {
    header("Location: /pages/404.html");
    exit;
}
$id = intval($_GET['id']);
if (strval($id) !== $_GET['id']) {
    header("Location: /pages/404.html");
    exit;
}

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
$lot = getLotById($dbConnection, $id);

dbClose($dbConnection);

if (empty($lot)) {
    header("Location: /pages/404.html");
    exit;
}

$mainContent = includeTemplate('lot-details.php', ['categories' => $categories, 'lot' => $lot]);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => $lot['name'],
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'categories' => $categories,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
