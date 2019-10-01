<?php
/**
 * Определение победителей.
 */

/**
 * Запрашивает информацию о победителях торгов.
 * Возвращает контактную информаю победителей торгов, а также
 * информацию о соответствующих лотах.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @return array
 */
function getWinners(mysqli $dbConnection): array
{
    if (!mysqli_begin_transaction($dbConnection)) {
        exit('Не удалось запустить транзакцию.');
    }

    $lotIds = getWonLotIds($dbConnection);
    if (!$lotIds) {
        mysqli_commit($dbConnection);
        return [];
    }

    setWinners($dbConnection, $lotIds);

    mysqli_commit($dbConnection);

    $sqlQuery = 'SELECT l.id lotId, l.winner_id winnerId, l.title, u.name, u.email
    FROM lots l JOIN users u ON l.winner_id = u.id
    WHERE l.id IN (' . $lotIds . ')';

    return dbFetchData($dbConnection, $sqlQuery);
}

/**
 * Возвращает идентификаторы лотов, получивших победителей.
 * Блокирует записи выбранных лотов.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @return string Строка со списком идентификаторов
 */
function getWonLotIds(mysqli $dbConnection): string
{
    $sqlQuery = 'SELECT id
    FROM lots
    WHERE expire_date <= NOW() AND winner_id IS NULL
        AND EXISTS (
            SELECT 1 FROM bids
            WHERE lot_id = lots.id
        LIMIT 1
        )
    LOCK IN SHARE MODE';

    $lotIds = dbFetchData($dbConnection, $sqlQuery);
    if (!$lotIds) {
        return '';
    }

    return implode(', ', array_column($lotIds, 'id'));
}

/**
 * Утанавливает идентификатор победителя для заданных лотов.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $lotIds Строка со списком идентификаторов
 * @return void
 */
function setWinners(mysqli $dbConnection, string $lotIds): void
{
    $sqlQuery = 'UPDATE lots
    SET winner_id = (
        SELECT user_id
        FROM bids
        WHERE lot_id = lots.id
        ORDER BY creation_time DESC, id DESC
        LIMIT 1
      )
    WHERE id IN (' . $lotIds . ')';

    if (!mysqli_query($dbConnection, $sqlQuery)) {
        exit('Ошибка при работе с БД: ' . mysqli_error($dbConnection));
    }
}
