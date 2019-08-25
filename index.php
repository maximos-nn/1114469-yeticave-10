<?php
$is_auth = rand(0, 1);

$user_name = 'maximos';

date_default_timezone_set('Europe/Moscow');
require_once 'helpers.php';
$config = require 'config.php';

$dbConnection = dbGetConnection($config['db']);

if (!$dbConnection) {
    showError(mysqli_connect_error());
}

mysqli_set_charset($dbConnection, 'utf8');

$categories = dbGetCategories($dbConnection);
$lots = dbGetOpenLots($dbConnection);

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
