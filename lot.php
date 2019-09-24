<?php
require_once __DIR__ . '/bootstrap.php';

$id = getIntParam($_GET,'id');
if (!$id) {
    http_response_code(404);
    showError('404');
}

$dbConnection = dbConnect($config['db']);

$lot = getLotById($dbConnection, $id);
if (!$lot) {
    dbClose($dbConnection);
    http_response_code(404);
    showError('404');
}

$errors = [];
$formData = [];

if (($_SERVER['REQUEST_METHOD'] ?? null) === 'POST' && $sessUser) {

    $formData = trimItems($_POST);
    $errors = validateBidForm($formData, $lot);

    if (!$errors && !addBid($dbConnection, $sessUser, $formData, $lot)) {
        $errors['cost'] = 'Была добавлена другая ставка';
    }
}

$categories = getCategories($dbConnection);
$bids = getBidsByLotId($dbConnection, $lot['id']);

dbClose($dbConnection);

$canCreateBids = isCanCreateBids($sessUser, $lot, $bids);

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$mainContent = includeTemplate(
    'lot-details.php',
    [
        'navigation' => $navigation,
        'lot' => $lot,
        'bids' => $bids,
        'canCreateBids' => $canCreateBids,
        'errors' => $errors,
        'form' => $formData
    ]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => $lot['name'],
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
