<?php
require 'bootstrap.php';

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
            // session_start();
            unset($userId['password']);
            $_SESSION['user'] = $userId;
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
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
