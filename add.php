<?php
require 'bootstrap.php';

$errors = [];
$formData = [];

$dbConnection = dbConnect($config['db']);
$categories = getCategories($dbConnection);


if (($_SERVER['REQUEST_METHOD'] ?? null) === 'POST') {
    $catIds = array_column($categories, 'id');
    $fileName = '';

    $rules = [
        'category' => $validateCategory,
        'lot-name' => $validateLotName,
        'message' => $validateLotComment,
        'lot-rate' => $validateLotPrice,
        'lot-step' => $validateBidStep,
        'lot-date' => $validateLotExpire
    ];

    $formData = trimItems($_POST);
    $errors = validateForm($rules, $formData);
    $imageError = $validateImage($_FILES['lot-img'] ?? []);
    if ($imageError) {
        $errors = array_merge($errors, ['lot-img' => $imageError]);
    }

    if (!$errors) {
        $lot = [
            $formData['lot-name'],
            $fileName,
            $formData['lot-rate'],
            $formData['lot-date'],
            $formData['lot-step'],
            1,
            $formData['category'],
            $formData['message']
        ];
        $lotId = createLot($dbConnection, $lot);
        dbClose($dbConnection);
        if (!$lotId) {
            exit('Не удалось добавить новый лот');
        }
        header('Location: /lot.php?id=' . (string)$lotId);
        exit;
    }

    if ($fileName) {
        unlink($fileName);
    }
}

dbClose($dbConnection);

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$mainContent = includeTemplate(
    'add-lot.php',
    ['navigation' => $navigation, 'errors' => $errors, 'form' => $formData]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Добавление лота',
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
