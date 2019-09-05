<?php
require 'bootstrap.php';

if ($isAuth) {
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
        $userId = getUserId($dbConnection, $formData['email']);

        if ($userId) {
            $pass = password_verify($formData['password'], $userId['password']);
        }

        if ($pass) {
            dbClose($dbConnection);
            unset($userId['password']);
            if (session_status() === PHP_SESSION_NONE && session_start()) {
                $_SESSION['user'] = $userId;
                session_write_close();
            }
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
        'isAuth' => $isAuth,
        'userName' => $userName,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
