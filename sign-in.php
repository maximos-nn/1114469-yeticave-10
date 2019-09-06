<?php
require 'bootstrap.php';

if ($sessUser) {
    header('Location: /');
    exit;
}

$errors = [];
$formData = [];

$dbConnection = dbConnect($config['db']);

if (($_SERVER['REQUEST_METHOD'] ?? null) === 'POST') {
    $rules = [
        'email' => $validateAuthEmail,
        'password' => $validateAuthPass
    ];

    $formData = trimItems($_POST);
    $errors = validateForm($rules, $formData);

    if (!$errors) {
        $errAuth = 'Пользователя с указанными адресом и паролем не существует';
        $pass = false;
        $userInfo = getUserByEmail($dbConnection, $formData['email']);

        if ($userInfo) {
            $pass = password_verify($formData['password'], $userInfo['password']);
        }

        if ($pass) {
            dbClose($dbConnection);
            unset($userInfo['password']);
            $_SESSION['user'] = $userInfo;
            header('Location: /');
            exit;
        }

        $errors = array_merge(['email' => $errAuth], ['password' => $errAuth]);
    }
}

$categories = getCategories($dbConnection);
dbClose($dbConnection);

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$mainContent = includeTemplate(
    'sign-in.php',
    ['navigation' => $navigation, 'errors' => $errors, 'form' => $formData]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Вход',
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
