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
    // if (empty($errors['email']) && !isEmailExists($dbConnection, $formData['email'])) {
    //     $errors = array_merge($errors, ['email' => 'Пользователь не найден']);
    // }

    if (!$errors) {
        $userId = getUserId($dbConnection, $formData['email']);
        dbClose($dbConnection);
        if (!$userId) {
            exit('Не удалось получить информацию о пользователе');
        }

        if (password_verify(password_hash($formData['password'], PASSWORD_BCRYPT), $userId['password'])) {
            header('Location: /');
            exit;
        }

        $errors = array_merge($errors, ['password' => 'Вы ввели неверный пароль']);
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
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
