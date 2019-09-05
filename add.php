<?php
require 'bootstrap.php';

if (!$isAuth) {
    http_response_code(403);
    showError('Доступ только для зарегистрированных пользователей', '403');
}

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
        if (empty($sessUser['id'])) {
            exit('Некорректный идентификатор пользователя');
        }
        $lot = [
            $formData['lot-name'],
            $fileName,
            $formData['lot-rate'],
            $formData['lot-date'],
            $formData['lot-step'],
            $sessUser['id'],
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
    ['navigation' => $navigation, 'categories' => $categories, 'errors' => $errors, 'form' => $formData]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Добавление лота',
        'isAuth' => $isAuth,
        'userName' => $userName,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
