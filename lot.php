<?php
require 'bootstrap.php';

if (!($id = getIntParam($_GET,'id'))) {
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
    $rules = [
        'cost' => $validateLotBid
    ];

    $formData = trimItems($_POST);
    $errors = validateForm($rules, $formData);

    if (!$errors) {
        $nextBid = calcNextBid(intval($lot['price']), intval($lot['step']));
        if (intval($formData['cost']) < $nextBid) {
            $errors = ['cost' => 'Ваша ставка должна быть не меньше ' . strval($nextBid)];
        }
    }

    if (!$errors) {
        $bid = [
            $sessUser['id'],
            $lot['id'],
            $formData['cost']
        ];
        $bidId = createBid($dbConnection, $bid);
        $lot = getLotById($dbConnection, $id);
        $nextBid = calcNextBid(intval($lot['price']), intval($lot['step']));

        if (!$bidId && intval($formData['cost']) >= $nextBid) {
            exit('Не удалось добавить ставку');
        }

        if (!$bidId) {
            $errors = ['Была добавлена другая ставка'];
        }
    }
}

$categories = getCategories($dbConnection);
$bids = getBidsByLotId($dbConnection, $lot['id']);

dbClose($dbConnection);

$canCreateBids = false;
if ($sessUser && $lot['user'] !== $sessUser['id']) {
    $isExpiredLot = date_create($lot['expiration']) <= date_create('today');
    $isLastBidOwner = ($bids[0]['user'] ?? '') === $sessUser['id'];
    if (!$isExpiredLot && !$isLastBidOwner) {
        $canCreateBids = true;
    }
}

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
