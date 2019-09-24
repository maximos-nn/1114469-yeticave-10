<?php

/**
 * Проверяет, является ли переданное значение корректным натуральным числом.
 * Для отрицательных и некорректных значений вернет null.
 * Но нас это устраивает, т.к. в схеме БД идентификаторы объявлены
 * как UNSIGNED INT, и все числовые поля являются натуральными.
 *
 * @param mixed $value Значение для анализа
 * @return int|null Возвращает корректное значение или null
 */
function getIntValue($value): ?int
{
    if (!preg_match('/^[1-9][0-9]*$/u', strval($value))) {
        return null;
    }
    return (int)$value;
}

/**
 * Проверяет корректность целочисленного значения в массиве по его ключу.
 *
 * @param array $array Масив со значением
 * @param string $key Ключ значения
 * @return integer|null Возвращает корректное значение или null
 */
function getIntParam(array $array, string $key): ?int
{
    return getIntValue($array[$key] ?? null);
}

/**
 * Выполняет проверку данных по заданным правилам.
 * Принимает массивы правил и данных. Ключи в них должны совпадать.
 * Возвращает массив ошибок с теми же ключами.
 * @param array $rules Массив правил
 * @param array $data Массив данных
 * @return array Массив ошибок
 */
function validateForm(array $rules, array $data): array
{
    $errors = [];
    foreach ($rules as $key => $rule) {
        if (is_callable($rule)) {
            $errors[$key] = $rule($data[$key] ?? '');
        }
    }
    return array_filter($errors);
}

/**
 * Проверяет вхождение длины строки в указанный диапазон.
 *
 * @param string $str Строка
 * @param integer $min Минимальная длина
 * @param integer $max Максимальная длина
 * @return boolean
 */
function isLengthValid(string $str, int $min = null, int $max = null): bool
{
    if ($min < 0 || $max < 0 || $max && $min > $max) {
        exit('isLengthValid: Недопустимые параметры.');
    }

    $len = mb_strlen($str, 'UTF-8');

    if ($min && $len < $min) {
        return false;
    }

    if ($max && $len > $max) {
        return false;
    }
    return true;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'.
 * Примеры использования:\
 * is_date_valid('2019-01-01'); // true\
 * is_date_valid('2016-02-29'); // true\
 * is_date_valid('2019-04-31'); // false\
 * is_date_valid('10.10.2010'); // false\
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function isDateValid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Правило для проверки названия лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotName = function (string $value) {
    if ($value === '') {
        return 'Введите наименование лота';
    }
    if (!preg_match('/^[-а-яёa-z0-9\/.() ]+$/iu', $value)) {
        return 'В строке присутствуют недопустимые символы';
    }
    return isLengthValid($value, 1, 255) ? '' : 'Поле нужно заполнить, и оно не должно превышать 255 символов';
};

/**
 * Правило для проверки описания лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotComment = function (string $value) {
    return isLengthValid($value, 1) ? '' : 'Напишите описание лота';
};

/**
 * Правило для проверки цены лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotPrice = function (string $value) {
    if (!$value) {
        return 'Введите начальную цену';
    }
    return getIntValue($value) ? '' : 'Цена должна быть числом больше 0';
};

/**
 * Правило для проверки шага ставки
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateBidStep = function (string $value) {
    if (!$value) {
        return 'Введите шаг ставки';
    }
    return getIntValue($value) ? '' : 'Шаг ставки должен быть числом больше 0';
};

/**
 * Правило для проверки категории лота
 *
 * @param string $value Значение для проверки
 * @param array &$catIds Список допустимых вариантов категории
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateCategory = function (string $value) use (&$catIds) {
    return in_array($value, $catIds, true) ? '' : 'Выберите категорию';
};

/**
 * Правило для проверки даты завершения торгов
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotExpire = function (string $value) {
    if (!$value) {
        return 'Введите дату завершения торгов';
    }
    if (!isDateValid($value)) {
        return 'Дата должна быть в формате "ГГГГ-ММ-ДД"';
    }
    return date_create($value) > date_create('tomorrow') ? '' : 'Дата должна быть больше завтрашней';
};

/**
 * Правило для проверки файла изображения лота
 *
 * @param array $data Данные о загруженном файле
 * @param string &$fileName Путь и имя корректного файла
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateImage = function (array $data) use (&$fileName) {
    if (!isset($data['error']) || is_array($data['error'])) {
        return 'Неверные параметры запроса';
    }

    $error = validateFileError($data['error']);
    if ($error) {
        return $error;
    }

    if (empty($data['tmp_name'])) {
        return 'Отсутствует имя файла';
    }

    $types = ['png' => 'image/png', 'jpg' => 'image/jpeg'];
    $ext = array_search(
        mime_content_type($data['tmp_name']),
        $types,
        true
    );
    if (!$ext) {
        return 'Неверный формат файла. Ожидалось: ' . implode(', ', array_values($types));
    }

    $file = moveFile($data['tmp_name'], $ext);
    if (!$file) {
        return 'Ошибка сохранения файла';
    }
    $fileName = $file;
    return '';
};

/**
 * Анализирует код ошибки загрузки файла.
 *
 * @param integer $error Код ошибки
 * @return string Описание ошибки или пустая строка в случае её отсутствия
 */
function validateFileError(int $error): string
{
    switch ($error) {
        case UPLOAD_ERR_OK:
            $result = '';
            break;
        case UPLOAD_ERR_NO_FILE:
            $result = 'Файл не был отправлен';
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $result = 'Превышен допустимый размер файла';
            break;
        default:
            $result = 'Неизвестная ошибка передачи файла';
    }
    return $result;
}

/**
 * Сохраняет загруженный временный файл.
 * Возвращает путь к сохраненному файлу или пустую строку в случае неудачи.
 *
 * @param string $tmpFileName Временный файл
 * @param string $ext Расширение для нового файла
 * @return string Сохраненный файл
 */
function moveFile(string $tmpFileName, string $ext): string
{
    $newFileName = './uploads/' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($tmpFileName, $newFileName)) {
        return '';
    }
    return $newFileName;
}

/**
 * Удаляет пробелы в начале и конце каждого элемента массива.
 *
 * @param string[] $data Массив строк для обработки
 * @return string[] Массив строк
 */
function trimItems(array $data): array
{
    return array_map(
        function (string $item) {
            return trim($item);
        },
        $data
    );
}

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
 * Правило для проверки суммы ставки
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateLotBid(string $value): string
{
    if (!$value) {
        return 'Введите сумму ставки';
    }
    return getIntValue($value) ? '' : 'Сумма ставки должна быть числом больше 0';
};

/**
 * Выполняет базовые проверки конфигурации проекта.
 *
 * @param mixed $config Конфигурация проекта
 * @return void
 */
function checkConfig($config): void
{
    if (!($config['db'] ?? null) || !is_array($config['db'])) {
        exit('В конфигурации не заданы настройки подключения к БД.');
    }

    if (!($lotsPerPage = $config['lots_per_page'] ?? null)) {
        exit('В конфигурации не задано количество лотов на странице.');
    }
    if (intval($lotsPerPage) <= 0) {
        exit('В конфигурации задано некорректное количество лотов на странице.');
    }

    if (!($config['smtp'] ?? null) || !is_array($config['smtp'])) {
        exit('В конфигурации не заданы настройки подключения к SMTP-серверу.');
    }

    $smtp = $config['smtp'];
    if (
        !isset(
            $smtp['host'],
            $smtp['port'],
            $smtp['user'],
            $smtp['password'],
            $smtp['sender']
        )
        || !array_key_exists('encryption', $smtp)
        ) {
        exit('В конфигурации SMTP заданы не все необходимые параметры.');
    }

    if (!($config['base_url'] ?? null)) {
        exit('В конфигурации не задан URL сайта.');
    }
}

/**
 * Выполняет проверку формы добавления ставки.
 *
 * @param array $formData Массив данных формы
 * @param array $lot Ассоциативный массив с информацией о лоте
 * @return array Массив ошибок, возможно пустой
 */
function validateBidForm(array $formData, array $lot): array
{
    $rules = [
        'cost' => 'validateLotBid'
    ];

    $errors = validateForm($rules, $formData);
    if (!$errors) {
        $nextBid = calcNextBid(intval($lot['price']), intval($lot['step']));
        if (intval($formData['cost']) < $nextBid) {
            $errors = ['cost' => 'Ваша ставка должна быть не меньше ' . strval($nextBid)];
        }
    }
    return $errors;
}

/**
 * Проверяет возможность добавления ставки пользователем.
 *
 * @param mixed $sessUser Текущий пользователь
 * @param array $lot Лот
 * @param array $bids Ставки лота
 * @return boolean
 */
function isCanCreateBids($sessUser, array $lot, array $bids): bool
{
    $result = false;
    if ($sessUser && $lot['user'] !== $sessUser['id']) {
        $isExpiredLot = date_create($lot['expiration']) <= date_create('today');
        $isLastBidOwner = ($bids[0]['user'] ?? '') === $sessUser['id'];
        if (!$isExpiredLot && !$isLastBidOwner) {
            $result = true;
        }
    }
    return $result;
}

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

/**
 * Добавляет ставку.
 *
 * @param mysqli $dbConnection Подключение к БД
 * @param array $sessUser Текущий пользователь
 * @param array $formData Данные формы
 * @param array $lot Лот
 * @return boolean
 */
function addBid(mysqli $dbConnection, array $sessUser, array $formData, array &$lot): bool
{
    $bid = [
        $sessUser['id'],
        $lot['id'],
        $formData['cost']
    ];
    $bidId = createBid($dbConnection, $bid);
    $lot = getLotById($dbConnection, $lot['id']);
    $nextBid = calcNextBid(intval($lot['price']), intval($lot['step']));

    if (!$bidId && intval($formData['cost']) >= $nextBid) {
        exit('Не удалось добавить ставку');
    }
    return (bool)$bidId;
}
