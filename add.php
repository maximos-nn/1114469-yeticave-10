<?php
require_once __DIR__ . '/bootstrap.php';

if (!$sessUser) {
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

    $formData = trimItems($_POST);
    $errors = validateLotForm($formData, $catIds, $_FILES['lot-img'] ?? [], $config['image_types']);

    if (!$errors) {
        // Не проверяем расширение, так как оно должно обязательно находиться после успешной валидации.
        // Даже если оно окажется по каким-то приичнам пустым, это нас тоже вполне устраивает.
        $fileName = moveFile(
            $_FILES['lot-img']['tmp_name'],
            getFileExtension($_FILES['lot-img']['tmp_name'], $config['image_types'])
        );
        if (!$fileName) {
            $errors['lot-img'] = 'Ошибка сохранения файла';
        }
    }

    if (!$errors) {
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
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
