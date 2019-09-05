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

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$mainContent = includeTemplate(
    'lot-details.php',
    [
        'navigation' => $navigation,
        'lot' => $lot,
        'isAuth' => $isAuth
    ]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => $lot['name'],
        'isAuth' => $isAuth,
        'userName' => $userName,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
