<?php
require 'bootstrap.php';

$errors = [];

$dbConnection = dbConnect($config['db']);
$categories = getCategories($dbConnection);

$catIds = array_column($categories, 'id');

if (($_SERVER['REQUEST_METHOD'] ?? null) === 'POST') {
    $fileName = '';

    $rules = [
        'category' => $validateCategory,
        'lot-name' => $validateLotName,
        'message' => $validateLotComment,
        'lot-rate' => $validateLotPrice,
        'lot-step' => $validateBidStep,
        'lot-date' => $validateLotExpire,
        'lot-img' => $validateImage
    ];

    $errors = validateForm($rules);

    if (!$errors) {
        $lot = [
            $_POST['lot-name'],
            $fileName,
            $_POST['lot-rate'],
            $_POST['lot-date'],
            $_POST['lot-step'],
            1,
            $_POST['category']
        ];
        $lotId = createLot($dbConnection, $lot);
        dbClose($dbConnection);
        if (!$lotId) {
            exit('Не удалось добавить новый лот');
        }
        header('Location: /lot.php?id=' . $lotId);
        exit;
    }

    if ($fileName) {
        unlink($fileName);
    }
}

dbClose($dbConnection);

$mainContent = includeTemplate(
    'add-lot.php',
    ['categories' => $categories, 'errors' => $errors]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Добавление лота',
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'categories' => $categories,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
