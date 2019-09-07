<?php
require 'bootstrap.php';

if (!$sessUser) {
    http_response_code(403);
    showError('Доступ только для зарегистрированных пользователей', '403');
}

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
$bids = getBidsByUserId($dbConnection, $sessUser['id']);

dbClose($dbConnection);

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$mainContent = includeTemplate('bids.php', ['navigation' => $navigation, 'bids' => $bids]);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Мои ставки',
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
