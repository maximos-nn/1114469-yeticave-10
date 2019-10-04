<?php

/**
 * Правило для проверки контактов аккаунта
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateAuthContacts = function (string $value) {
    return isLengthValid($value, 1) ? '' : 'Напишите как с вами связаться';
};

/**
 * Правило для проверки имени пользователя
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateAuthName = function (string $value) {
    if ($value === '') {
        return 'Введите имя';
    }
    return isLengthValid($value, 1, 255) ? '' : 'Поле не должно быть длиннее 255 символов';
};

/**
 * Правило для проверки пароля
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateAuthPass = function (string $value) {
    if ($value === '') {
        return 'Введите пароль';
    }
    return isLengthValid($value, 8, 255) ? '' : 'Поле должно быть от 8 до 255 символов';
};

/**
 * Правило для проверки email
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateAuthEmail = function (string $value) {
    if ($value === '') {
        return 'Введите e-mail';
    }
    if (!isLengthValid($value, 1, 255)) {
        return 'Поле не должно быть длиннее 255 символов';
    }
    return filter_var($value, FILTER_VALIDATE_EMAIL) ? '' : 'Некорректный адрес электронной почты';
};

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
