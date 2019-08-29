<?php
require 'bootstrap.php';

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
// $lot = getLotById($dbConnection, $id);

dbClose($dbConnection);

$mainContent = includeTemplate('add-lot.php', ['categories' => $categories]);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Добавление лота',
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'categories' => $categories,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
