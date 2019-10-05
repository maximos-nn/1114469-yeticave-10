<?php

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
    $errors = [];
    if ($error = validateLotBid($formData['cost'])) {
        $errors['cost'] = $error;
    }
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
