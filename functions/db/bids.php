<?php
/**
 * Получение информации о ставках, создание ставки.
 */

/**
 * Добавляет новую ставку.
 * Ожидаемая последовательность полей:
 * - id владельца
 * - id лота
 * - сумма ставки
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param array $bid Массив со знечениями полей записи
 * @return integer|null Возвращает идентификатор новой записи или null в случае неудачи
 */
function createBid(mysqli $dbConnection, array $bid): ?int
{
    $sqlQuery = 'INSERT INTO bids (user_id, lot_id, amount)
    SELECT ?, ?, ? FROM lots l
    WHERE l.id = ?
    AND ? >= l.bid_step + IFNULL(
        (SELECT amount FROM bids WHERE lot_id=l.id ORDER BY id DESC LIMIT 1), l.price
    )';

    $values = array_merge($bid, [$bid[1], $bid[2]]);
    if (!dbManipulateStmtData($dbConnection, $sqlQuery, $values)) {
        return null;
    }
    return mysqli_insert_id($dbConnection);
}

/**
 * Запрашивает историю ставок для указанного лота.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param integer $id Идентификатор лота
 * @return array Ассоциативный массив записей
 */
function getBidsByLotId(mysqli $dbConnection, int $id): array
{
    $sqlQuery = 'SELECT u.id user, u.name, b.amount, b.creation_time creation
    FROM bids b JOIN users u ON b.user_id = u.id
    WHERE b.lot_id = ?
    ORDER BY b.creation_time DESC, b.id DESC';

    return dbFetchStmtData($dbConnection, $sqlQuery, [$id]);
}

/**
 * Запрашивает подробную информацию о ставках указанного пользователя.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param integer $id Идентификатор пользователя
 * @return array Ассоциативный массив записей
 */
function getBidsByUserId(mysqli $dbConnection, int $id): array
{
    $sqlQuery = 'SELECT l.id, l.title `name`, l.expire_date expiration, l.image_path `url`,
        b.amount, b.creation_time creation, c.name category, IFNULL(u.contact, "") contact
    FROM
        bids b JOIN lots l ON b.lot_id = l.id
        JOIN categories c ON l.category_id = c.id
        left JOIN users u ON l.user_id = u.id AND l.winner_id = b.user_id
    WHERE b.user_id = ?
    ORDER BY b.creation_time DESC, b.id DESC';

    return dbFetchStmtData($dbConnection, $sqlQuery, [$id]);
}
