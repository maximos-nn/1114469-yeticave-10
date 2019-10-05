<?php
require_once __DIR__ . '/bootstrap.php';

if ($sessUser) {
    header('Location: /');
    exit;
}

$errors = [];
$formData = [];

$dbConnection = dbConnect($config['db']);

if (($_SERVER['REQUEST_METHOD'] ?? null) === 'POST') {
    $formData = trimItems($_POST);
    $errors = validateSignUpForm($dbConnection, $formData);

    if (!$errors) {
        $user = [
            $formData['email'],
            $formData['name'],
            password_hash($formData['password'], PASSWORD_BCRYPT),
            $formData['message']
        ];
        $userId = createUser($dbConnection, $user);
        dbClose($dbConnection);
        if (!$userId) {
            exit('Не удалось добавить пользователя');
        }
        header('Location: sign-in.php');
        exit;
    }
}

$categories = getCategories($dbConnection);
dbClose($dbConnection);

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$mainContent = includeTemplate(
    'sign-up.php',
    ['navigation' => $navigation, 'errors' => $errors, 'form' => $formData]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Регистрация',
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
