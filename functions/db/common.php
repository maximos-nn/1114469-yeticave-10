<?php
/**
 * Общие операции с БД.
 */


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
