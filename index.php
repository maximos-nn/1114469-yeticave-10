<?php
$is_auth = rand(0, 1);

$user_name = 'maximos';

date_default_timezone_set('Europe/Moscow');
require_once 'helpers.php';

if (!file_exists('config.php')) {
    showError('Создайте файл config.php на основе config.sample.php и выполните настройку.');
}
$config = require 'config.php';

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
$lots = getOpenLots($dbConnection);

$mainContent = include_template('main.php', ['categories' => $categories, 'lots' => $lots]);
$layoutContent = include_template(
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
