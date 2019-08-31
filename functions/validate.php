<?php

/**
 * Проверяет, является ли переданное значение корректным целым числом.
 * Для отрицательных значений вернет null.
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
 * @param array $array Массив, содержащий ожидаемое значение
 * @param string $key Ключ ожидаемого значения
 * @return int|null
 */
function getIntValue(array $array, string $key): ?int
{
    $value = $array[$key] ?? null;
    if (!$value || !ctype_digit(strval($value)) || $value === true) {
        return null;
    }
    return (int)$value;
}

function isInList(array $list, string $item): bool
{
    return in_array($item, $list, true);
}

function validateForm(array $rules): array
{
    $errors = [];
    foreach ($rules as $key => $rule) {
        $errors[$key] = $rule();
    }
    return array_filter($errors);
}

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

$validateLotName = function()
{
    $str = $_POST['lot-name'] ?? '';
    if (!$str) {
        return 'Введите наименование лота';
    }
    // trim()? И проверка символов?
    return isLengthValid($_POST['lot-name'], 1, 255) ? '' : 'Поле нужно заполнить, и оно не должно превышать 255 символов';
};

$validateLotComment = function()
{
    return isLengthValid($_POST['message'] ?? '', 1, -1) ? '' : 'Напишите описание лота';
};

$validateLotPrice = function()
{
    if (empty($_POST['lot-rate'])) {
        return 'Введите начальную цену';
    }
    return ($val = getIntValue($_POST, 'lot-rate')) && $val > 0 ? '' : 'Цена должна быть числом больше 0';
};

$validateBidStep = function()
{
    if (empty($_POST['lot-step'])) {
        return 'Введите шаг ставки';
    }
    return ($val = getIntValue($_POST, 'lot-step')) && $val > 0 ? '' : 'Шаг ставки должен быть числом больше 0';
};

$validateCategory = function() use (&$catIds)
{
    return isInList($catIds, $_POST['category'] ?? '') ? '' : 'Выберите категорию';
};

$validateLotExpire = function()
{
    if (empty($_POST['lot-date'])) {
        return 'Введите дату завершения торгов';
    }
    if (!isDateValid($_POST['lot-date'])) {
        return 'Дата должна быть в формате "ГГГГ-ММ-ДД"';
    }
    // Дата окончания торгов включает указанный день?
    // return date_create($_POST['lot-date']) >= date_modify(date_create('today'), '2 day') ? '' : 'Дата должна быть больше текущей';
    return date_create($_POST['lot-date']) >= date_create('tomorrow') ? '' : 'Дата должна быть больше текущей';
};

$validateImage = function() use(&$fileName)
{
    $key = 'lot-img';
    $error = validateFile($key);
    if ($error) {
        return $error;
    }
    $types = ['png' => 'image/png', 'jpg' => 'image/jpeg'];
    $ext = getFileType($key, $types);
    if (!$ext) {
        return 'Неверный формат файла. Ожидалось: ' . implode(', ', array_values($types));
    }
    $file = moveFile($key, $ext);
    if (!$file) {
        return 'Ошибка сохранения файла';
    }
    $fileName = $file;
    return '';
};

function validateFile(string $key): string
{
    if (!isset($_FILES[$key]['error']) || is_array($_FILES[$key]['error'])) {
        return 'Неверные параметры запроса';
    }

    switch ($_FILES[$key]['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return 'Файл не был отправлен';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Превышен допустимый размер файла';
        default:
            return 'Неизвестная ошибка передачи файла';
    }

    // Здесь могла быть проверка размера файла

    return '';
}

function getFileType(string $key, array $types): string
{
    return array_search(
        mime_content_type($_FILES[$key]['tmp_name']),
        $types,
        true
    );
}

function moveFile(string $key, string $ext): string
{
    $newFileName = './uploads/' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($_FILES[$key]['tmp_name'], $newFileName)) {
        return '';
    }
    return $newFileName;
}
