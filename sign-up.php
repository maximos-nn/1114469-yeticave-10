<?php
require 'bootstrap.php';

$errors = [];
$formData = [];

$dbConnection = dbConnect($config['db']);

if (($_SERVER['REQUEST_METHOD'] ?? null) === 'POST') {
    $rules = [
        'email' => $validateAuthEmail,
        'password' => $validateAuthPass,
        'name' => $validateAuthName,
        'message' => $validateAuthContacts
    ];

    $formData = trimItems($_POST);
    $errors = validateForm($rules, $formData);
    if (empty($errors['email']) && isEmailExists($dbConnection, $formData['email'])) {
        $errors = array_merge($errors, ['email' => 'Пользователь уже существует']);
    }

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
        header('Location: /pages/login.html');
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
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
