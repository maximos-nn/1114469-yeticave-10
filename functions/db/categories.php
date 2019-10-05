<?php
/**
 * Получение списка категорий и лотов в конкретной категории.
 */

/**
 * Запрашивает категории в БД.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @return array Массив записей
 */
function getCategories(mysqli $dbConnection): array
{
    $sqlQuery = 'SELECT * FROM `categories`';
    return dbFetchData($dbConnection, $sqlQuery);
}

/**
 * Запрашивает количество активных лотов в категории.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param integer $category Идентификатор категории
 * @return integer Количество записей
 */
function getCategoryLotsCount(mysqli $dbConnection, int $category): int
{
    $sqlQuery = 'SELECT COUNT(*) total
    FROM lots
    WHERE expire_date > NOW() and category_id = ?';

    return intval(dbFetchStmtData($dbConnection, $sqlQuery, [$category])[0]['total']);
}

/**
 * Запрашивает подмножество лотов указанной категории.
 * Возвращает $limit записей со смещением $offset.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param integer $category id категории
 * @param integer $offset Смещение
 * @param integer $limit Количество записей
 * @return array
 */
function getCategoryLots(mysqli $dbConnection, int $category, int $offset, int $limit): array
{
    $sqlQuery = 'SELECT l.id, l.title `name`, l.image_path `url`, l.expire_date expiration,
    IFNULL((SELECT amount FROM bids WHERE lot_id=l.id ORDER BY id DESC LIMIT 1), l.price) price,
    c.name category
    FROM lots l JOIN categories c ON l.category_id=c.id
    WHERE l.expire_date > NOW() AND category_id = ?
    ORDER BY l.creation_time DESC, l.id DESC LIMIT ? OFFSET ?';

    return dbFetchStmtData($dbConnection, $sqlQuery, [$category, $limit, $offset]);
}
