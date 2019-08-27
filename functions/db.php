<?php

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Ошибка при работе с БД. Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        exit($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Ошибка при работе с БД. Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            exit($errorMsg);
        }
    }

    return $stmt;
}

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
 * Запрашивает открытые лоты в БД.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @return array Массив записей
 */
function getActiveLots(mysqli $dbConnection): array
{
    $sqlQuery = 'SELECT l.id, l.title `name`, l.image_path `url`, l.expire_date expiration,
    IFNULL((SELECT amount FROM bids WHERE lot_id=l.id ORDER BY id DESC LIMIT 1), l.price) price,
    c.name category
    FROM lots l JOIN categories c ON l.category_id=c.id
    WHERE l.expire_date > NOW()
    ORDER BY l.creation_time DESC, l.id DESC LIMIT 9';

    return dbFetchData($dbConnection, $sqlQuery);
}

/**
 * Выполняет запрос к БД и возвращает результат в виде ассоциативного массива.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $sqlQuery Строка запроса
 * @return array Массив записей
 */
function dbFetchData(mysqli $dbConnection, string $sqlQuery): array
{
    $sqlResult = mysqli_query($dbConnection, $sqlQuery);

    if (!$sqlResult) {
        exit('Ошибка при работе с БД: ' . mysqli_error($dbConnection));
    }

    return mysqli_fetch_all($sqlResult, MYSQLI_ASSOC);
}

/**
 * Устанавливает соединение с БД.
 * Принимает массив параметров с ключами 'host', 'user', 'password', 'database'.
 *
 * @param string[] $dbConfig Массив параметров подключения
 * @return mysqli Объект подключения к БД
 */
function dbConnect($dbConfig): mysqli
{
    if (empty($dbConfig)) {
        exit('Некорректная конфигурация подключения к БД.');
    }

    $dbConnection = mysqli_connect(
        $dbConfig['host'],
        $dbConfig['user'],
        $dbConfig['password'],
        $dbConfig['database']
    );

    if (!$dbConnection) {
        exit('Ошибка при подключении к БД: ' . mysqli_connect_error());
    }

    mysqli_set_charset($dbConnection, 'utf8');

    return $dbConnection;
}

/**
 * Закрывает соединение с БД.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @return boolean Результат операции
 */
function dbClose(mysqli $dbConnection): bool
{
    return mysqli_close($dbConnection);
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
    $sqlQuery = 'SELECT l.id, l.title `name`, l.image_path `url`, l.expire_date expiration,
    IFNULL((SELECT amount FROM bids WHERE lot_id=l.id ORDER BY id DESC LIMIT 1), l.price) price,
    l.bid_step step, IFNULL(l.description, "") `description`, c.name category
    FROM lots l JOIN categories c ON l.category_id=c.id
    WHERE l.id=' . $id;

    return mysqli_fetch_assoc(mysqli_query($dbConnection, $sqlQuery)) ?? [];
}
