<?php
/**
 * Получение информации о лотах, создание лота.
 */

/**
 * Запрашивает открытые лоты в БД.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @return array Массив записей
 */
function getActiveLots(mysqli $dbConnection): array
{
    $sqlQuery = 'SELECT l.id, l.title `name`, l.image_path `url`, l.expire_date expiration,
        l.price, c.name category
    FROM lots l JOIN categories c ON l.category_id=c.id
    WHERE l.expire_date > NOW()
    ORDER BY l.creation_time DESC, l.id DESC LIMIT 9';

    return dbFetchData($dbConnection, $sqlQuery);
}

/**
 * Запрашивает информацию о лоте в БД по идентификатору.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param int $id Идентификатор лота
 * @return array Запись в виде ассоциативного массива
 */
function getLotById(mysqli $dbConnection, int $id): array
{
    $sqlQuery = 'SELECT l.id, l.title `name`, l.image_path `url`, l.expire_date expiration, l.user_id user,
    IFNULL((SELECT amount FROM bids WHERE lot_id=l.id ORDER BY id DESC LIMIT 1), l.price) price,
    l.bid_step step, IFNULL(l.description, "") `description`, c.name category
    FROM lots l JOIN categories c ON l.category_id=c.id
    WHERE l.id=?';

    return dbFetchStmtData($dbConnection, $sqlQuery, [$id])[0] ?? [];
}

/**
 * Добавляет новый лот в БД.
 * Ожидаемая последовательность полей:
 * - название
 * - путь к файлу изображения
 * - цена
 * - дата завершения
 * - шаг ставки
 * - id владельца
 * - id категории
 * - описание лота
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param array $lot Массив со знечениями полей записи
 * @return int|null Возвращает идентификатор новой записи или null в случае неудачи
 */
function createLot(mysqli $dbConnection, array $lot): ?int
{
    $sqlQuery = 'INSERT INTO `lots`
    (`title`, `image_path`, `price`, `expire_date`, `bid_step`, `user_id`, `category_id`, `description`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    if (!dbManipulateStmtData($dbConnection, $sqlQuery, $lot)) {
        return null;
    }
    return mysqli_insert_id($dbConnection);
}
