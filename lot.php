<?php
require 'bootstrap.php';

if (!($id = getIntParam($_GET,'id'))) {
    http_response_code(404);
    showError('404');
}

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
$lot = getLotById($dbConnection, $id);

dbClose($dbConnection);

if (!$lot) {
    http_response_code(404);
    showError('404');
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
