<?php
require 'bootstrap.php';

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
$lots = getActiveLots($dbConnection);

dbClose($dbConnection);

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$mainContent = includeTemplate('main.php', ['categories' => $categories, 'lots' => $lots]);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Главная',
        'isAuth' => $isAuth,
        'userName' => $userName,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
