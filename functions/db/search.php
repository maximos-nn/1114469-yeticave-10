<?php
/**
 * Поиск по лотам.
 */

/**
 * Запрашивает предполагаемое количество результатов поиска.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $query Поисковая строка
 * @return integer Количество записей
 */
function getSearchResultsCount(mysqli $dbConnection, string $query): int
{
    $sqlQuery = 'SELECT COUNT(*) total
    FROM lots
    WHERE expire_date > NOW() AND MATCH(title, `description`) AGAINST(?)';

    return intval(dbFetchStmtData($dbConnection, $sqlQuery, [$query])[0]['total']);
}

/**
 * Запращивает активные лоты согласно поисковому запросу.
 * Поск производится по названию и описанию лотов.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $query Поисковая строка
 * @param integer $offset Смещение
 * @param integer $limit Количество записей
 * @return array
 */
function searchLots(mysqli $dbConnection, string $query, int $offset, int $limit): array
{
    $sqlQuery = 'SELECT l.id, l.title `name`, l.image_path `url`, l.expire_date expiration,
    IFNULL((SELECT amount FROM bids WHERE lot_id=l.id ORDER BY id DESC LIMIT 1), l.price) price,
    c.name category
    FROM lots l JOIN categories c ON l.category_id=c.id
    WHERE l.expire_date > NOW() AND MATCH(l.title, l.description) AGAINST(?)
    ORDER BY l.creation_time DESC, l.id DESC LIMIT ? OFFSET ?';

    return dbFetchStmtData($dbConnection, $sqlQuery, [$query, $limit, $offset]);
}
