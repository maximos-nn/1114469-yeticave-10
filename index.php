<?php
require_once __DIR__ . '/bootstrap.php';

// Скрипт определения победителья не нужно подключать здесь.
// В боевых проектах он должен вызываться по планировщику.
// А для теста при проверке мы вызываем его вручную

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
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent,
        'index' => true
    ]
);
print($layoutContent);
