<?php
require 'bootstrap.php';

$errors = [];

$dbConnection = dbConnect($config['db']);
$categories = getCategories($dbConnection);

$catIds = array_column($categories, 'id');

if (($_SERVER['REQUEST_METHOD'] ?? null) === 'POST') {
    $rules = [
        'category' => $validateCategory,
        'lot-name' => $validateLotName,
        'message' => $validateLotComment,
        'lot-rate' => $validateLotPrice,
        'lot-step' => $validateBidStep,
        'lot-date' => $validateLotExpire
    ];

    $errors = validateForm($rules);

    $fileName = '';
    $errors['lot-img'] = validateFile('lot-img');
    if (!$errors['lot-img']) {
        $types = ['png' => 'image/png', 'jpg' => 'image/jpeg'];
        $ext = getFileType('lot-img', $types);
        if (!$ext) {
            $errors['lot-img'] = 'Неверный формат файла. Ожидалось: ' . implode(', ', array_values($types));
        } else {
            $fileName = moveFile('lot-img', $ext);
            if (!$fileName) {
                $errors['lot-img'] = 'Ошибка сохранения файла';
            }
        }
        $errors = array_filter($errors);
    }

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
        header('Location: /lot.php?id=' . $lotId);
        exit;
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
