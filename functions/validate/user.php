<?php

/**
 * Правило для проверки контактов аккаунта
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateAuthContacts(string $value): string
{
    return isLengthValid($value, 1) ? '' : 'Напишите как с вами связаться';
}

/**
 * Правило для проверки имени пользователя
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateAuthName(string $value): string
{
    if ($value === '') {
        return 'Введите имя';
    }
    return isLengthValid($value, 1, 255) ? '' : 'Поле не должно быть длиннее 255 символов';
}

/**
 * Правило для проверки пароля
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateAuthPass(string $value): string
{
    if ($value === '') {
        return 'Введите пароль';
    }
    return isLengthValid($value, 8, 255) ? '' : 'Поле должно быть от 8 до 255 символов';
}

/**
 * Правило для проверки email
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateAuthEmail(string $value): string
{
    if ($value === '') {
        return 'Введите e-mail';
    }
    if (!isLengthValid($value, 1, 255)) {
        return 'Поле не должно быть длиннее 255 символов';
    }
    return filter_var($value, FILTER_VALIDATE_EMAIL) ? '' : 'Некорректный адрес электронной почты';
}

/**
 * Производит аутентификацию пользователя.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param array $formData Данные формы входа
 * @return boolean
 */
function authenticate(mysqli $dbConnection, array $formData): array
{
    $userInfo = getUserByEmail($dbConnection, $formData['email']);
    if ($userInfo && password_verify($formData['password'], $userInfo['password'])) {
        return $userInfo;
    }
    return [];
}

/**
 * Производит проверку полей формы входа.
 *
 * @param array $formData Данные формы
 * @return array Массив ошибок валидации, может быть пустым
 */
function validateSignInForm(array $formData): array
{
    $errors = [];
    if ($error = validateAuthEmail($formData['email'] ?? '')) {
        $errors['email'] = $error;
    }
    if ($error = validateAuthPass($formData['password'] ?? '')) {
        $errors['password'] = $error;
    }
    return $errors;
}

/**
 * Производит проверку полей формы регистрации.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param array $formData Данные формы
 * @return array Массив ошибок валидации, может быть пустым
 */
function validateSignUpForm(mysqli $dbConnection, array $formData): array
{
    $errors = [];
    if ($error = validateAuthEmail($formData['email'] ?? '')) {
        $errors['email'] = $error;
    }
    if ($error = validateAuthPass($formData['password'] ?? '')) {
        $errors['password'] = $error;
    }
    if ($error = validateAuthName($formData['name'] ?? '')) {
        $errors['name'] = $error;
    }
    if ($error = validateAuthContacts($formData['message'] ?? '')) {
        $errors['message'] = $error;
    }
    if (empty($errors['email']) && isEmailExists($dbConnection, $formData['email'])) {
        $errors['email'] = 'Пользователь уже существует';
    }
    return $errors;
}
