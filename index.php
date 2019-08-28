<?php
require 'bootstrap.php';

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
$lots = getActiveLots($dbConnection);

dbClose($dbConnection);

$mainContent = includeTemplate('main.php', ['categories' => $categories, 'lots' => $lots]);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Главная',
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'categories' => $categories,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
