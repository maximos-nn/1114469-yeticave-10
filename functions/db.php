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
function dbGetPrepareStmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
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
            } elseif (is_string($value)) {
                $type = 's';
            } elseif (is_double($value)) {
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
        l.price, c.name category
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
function dbConnect(array $dbConfig): mysqli
{
    $dbConnection = mysqli_connect(
        $dbConfig['host'] ?? null,
        $dbConfig['user'] ?? null,
        $dbConfig['password'] ?? null,
        $dbConfig['database'] ?? null
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
    $sqlQuery = 'SELECT l.id, l.title `name`, l.image_path `url`, l.expire_date expiration, l.user_id user,
    IFNULL((SELECT amount FROM bids WHERE lot_id=l.id ORDER BY id DESC LIMIT 1), l.price) price,
    l.bid_step step, IFNULL(l.description, "") `description`, c.name category
    FROM lots l JOIN categories c ON l.category_id=c.id
    WHERE l.id=?';

    return dbFetchStmtData($dbConnection, $sqlQuery, [$id])[0] ?? [];
}

/**
 * Выполняет подготавливаемый запрос на выборку и возвращает результат в виде ассоциативного массива.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $sqlQuery Шаблон запроса с псевдопеременными
 * @param array $data Данные для псевдопеременных
 * @return array
 */
function dbFetchStmtData(mysqli $dbConnection, string $sqlQuery, array $data = []): array
{
    $result = [];
    $stmt = dbGetPrepareStmt($dbConnection, $sqlQuery, $data);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt);
    mysqli_free_result($res);
    return $result;
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

/**
 * Выполняет вставку, изменение или удаление данных с помощью подготавливаемого запроса.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param string $sqlQuery Шаблон запроса с псевдопеременными
 * @param array $data Данные для псевдопеременных
 * @return boolean
 */
function dbManipulateStmtData(mysqli $dbConnection, string $sqlQuery, array $data = []): bool
{
    $stmt = dbGetPrepareStmt($dbConnection, $sqlQuery, $data);
    $res = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $res;
}

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
