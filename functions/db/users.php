<?php
/**
 * Получение информации о пользователе, создание пользователя.
 */

/**
 * Добавляет нового пользователя.
 * Ожидаемая последовательность полей:
 * - email
 * - имя
 * - хэш пароля
 * - контакты
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param array $user Массив со знечениями полей записи
 * @return int|null Возвращает идентификатор новой записи или null в случае неудачи
 */
function createUser(mysqli $dbConnection, array $user): ?int
{
    $sqlQuery = 'INSERT INTO `users` (`email`, `name`, `password`, `contact`) VALUES (?, ?, ?, ?)';

    if (!dbManipulateStmtData($dbConnection, $sqlQuery, $user)) {
        return null;
    }
    return mysqli_insert_id($dbConnection);
}

/**
 * Проверяет наличие пользователя с указанным адресом электронной почты.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $email email для проверки
 * @return boolean
 */
function isEmailExists(mysqli $dbConnection, string $email): bool
{
    $sqlQuery = 'SELECT 1 FROM `users` WHERE `email`=?';
    return (bool)dbFetchStmtData($dbConnection, $sqlQuery, [$email]);
}

/**
 * Запрашивает идентификационную информацию о пользователе.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $email Email пользователя
 * @return array Запись в виде ассоциативного массива
 */
function getUserByEmail(mysqli $dbConnection, string $email): array
{
    $sqlQuery = 'SELECT id, `name`, avatar_path, `password` FROM users WHERE email=?';
    return dbFetchStmtData($dbConnection, $sqlQuery, [$email])[0] ?? [];
}

