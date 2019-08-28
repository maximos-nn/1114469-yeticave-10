<?php
$is_auth = rand(0, 1);

$user_name = 'maximos';

date_default_timezone_set('Europe/Moscow');
require_once 'functions/template.php';
require_once 'functions/db.php';

if (!file_exists('config.php')) {
    exit('Создайте файл config.php на основе config.sample.php и выполните настройку.');
}
$config = require 'config.php';

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
