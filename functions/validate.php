<?php

/**
 * Проверяет, является ли переданное значение корректным целым числом.
 * Для отрицательных и некорректных значений вернет null.
 * Но нас это устраивает, т.к. в схеме БД идентификаторы объявлены
 * как UNSIGNED INT, и все числовые поля являются натуральными.\
 * getIntValue(23); // bool(true)\
 * getIntValue('23'); // bool(true)\
 * getIntValue('2.3'); // bool(false)\
 * getIntValue('2e3'); // bool(false)\
 * getIntValue('-23'); // bool(false)\
 * getIntValue(-23); // bool(false)\
 * getIntValue(0); // bool(true)\
 * getIntValue(0.0); // bool(true)\
 * getIntValue('0.0'); // bool(false)\
 * getIntValue('-0.0'); // bool(false)\
 * getIntValue(-0.0); // bool(false)\
 * getIntValue(''); // bool(false)\
 * getIntValue(null); // bool(false)\
 * getIntValue('null'); // bool(false)\
 * getIntValue('test'); // bool(false)\
 * getIntValue('test3'); // bool(false)\
 * getIntValue('2test3'); // bool(false)\
 * getIntValue('true'); // bool(false)\
 * getIntValue('false'); // bool(false)\
 * getIntValue(false); // bool(false)\
 * getIntValue(true); // bool(false)
 *
 * @param mixed $value Значение для анализа
 * @return int|null Возвращает корректное значение или null
 */
function getIntValue($value): ?int
{
    if (!$value || $value === true || !ctype_digit($s = strval($value)) || $s !== ltrim($s, '0')) {
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
        $errors[$key] = $rule($data[$key] ?? '');
    }
    return array_filter($errors);
}

/**
 * Проверяет вхождение длины строки в указанный диапазон.
 * Отсутсвие ограничения длины сверху обозначается отрицательным значением $max.
 * Бессмысленные комбинации $min и $max игнорируются.
 * По умолчанию считается, что строка должна быть, т.е. $min >= 1.
 *
 * @param string $str Строка
 * @param integer $min Минимальная длина ($min >= 0)
 * @param integer $max Максимальная длина при $max >= 0, или отсутствие ограничения при $max < 0
 * @return boolean
 */
function isLengthValid(string $str, int $min = 1, int $max = -1): bool
{
    if ($min < 0) {
        $min = 0;
    }
    if ($max >= 0 && $max < $min) {
        $max = -1;
    }
    $len = mb_strlen($str, 'UTF-8');
    return $len >= $min and $max < 0 || $len <= $max;
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
 */
$validateLotName = function(string $value)
{
    if ($value === '') {
        return 'Введите наименование лота';
    }
    if (!isValidChars($value)) {
        return 'В строке присутствуют недопустимые символы';
    }
    return isLengthValid($value, 1, 255) ? '' : 'Поле нужно заполнить, и оно не должно превышать 255 символов';
};

/**
 * Правило для проверки описания лота
 */
$validateLotComment = function(string $value)
{
    return isLengthValid($value) ? '' : 'Напишите описание лота';
};

/**
 * Правило для проверки цены лота
 */
$validateLotPrice = function(string $value)
{
    if (!$value) {
        return 'Введите начальную цену';
    }
    return getIntValue($value) ? '' : 'Цена должна быть числом больше 0';
};

/**
 * Правило для проверки шага ставки
 */
$validateBidStep = function(string $value)
{
    if (!$value) {
        return 'Введите шаг ставки';
    }
    return getIntValue($value) ? '' : 'Шаг ставки должен быть числом больше 0';
};

/**
 * Правило для проверки категории лота
 */
$validateCategory = function(string $value) use (&$catIds)
{
    return in_array($value, $catIds, true) ? '' : 'Выберите категорию';
};

/**
 * Правило для проверки даты завершения торгов
 */
$validateLotExpire = function(string $value)
{
    if (!$value) {
        return 'Введите дату завершения торгов';
    }
    if (!isDateValid($value)) {
        return 'Дата должна быть в формате "ГГГГ-ММ-ДД"';
    }
    // Дата окончания торгов включает указанный день?
    // return date_create($value) >= date_modify(date_create('today'), '2 day') ? '' : 'Дата должна быть больше текущей';
    return date_create($value) >= date_create('tomorrow') ? '' : 'Дата должна быть больше текущей';
};

/**
 * Правило для проверки файла изображения лота
 */
$validateImage = function(array $data) use(&$fileName)
{
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
            return '';
        case UPLOAD_ERR_NO_FILE:
            return 'Файл не был отправлен';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Превышен допустимый размер файла';
        default:
            return 'Неизвестная ошибка передачи файла';
    }
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
        function(string $item)
        {
            return trim($item);
        },
        $data
    );
}

/**
 * Проверяет строку на наличие только допустимых символов.
 *
 * @param string $str Строка для анализа
 * @return boolean
 */
function isValidChars(string $str): bool
{
    return preg_match('/^[-а-яёa-z0-9\/ ]+$/iu', $str);
}
