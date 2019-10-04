Рекомендации по рефакторингу процесса валидации

1. Вызов фунции validateForm
```
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
```
Унифицирование решение для вызова функций валидации - не всегда хорошо.
Например:
1) при добавлении данных лота в валидатор нужно передать список категорий.
Архитектура твоих функций валидации не позволяет реализовать передачу
дополнительных данных через аргументы и ты вынужден в самом валидаторе
использовать use(...), что не есть хорошо, так как твоя фунция будет жестко
зависеть от окружения. Лучше реализовать передачу через аргументы.
2) ты вынужден отдельно проверять корректность заполнения полей
и корректность значений. По хорошему лучше это делать в рамках одной функции.

Лушче под каждую форму сделать функцию:

validateLoginForm(...)
validateRegisterForm(...)
validateLotForm(...)
validateBidForm(...)

А уже внутри этих функций вызывать валидаторы.
В тех функциях валидаторах, где требуются дополнительные данные,
сделать получения данных через агрументы.

Например, вместо:
```
$validateCategory = function (string $value) use (&$catIds) {
    return in_array($value, $catIds, true) ? '' : 'Выберите категорию';
};
```

Сделать функцию:
```
function validateLotCategory(int $id, array $catIds) {
    return in_array($value, $catIds, true) ? '' : 'Выберите категорию';
};
```

Рекомендую сделать процесс валидации по следующей схеме:
например, валидация формы добавления лота

Сделать отдельный файл, в котором будет функции валидации формы добавления лота.
Вызывать ее так

$errors = validateLotForm($formData, $fileData, $categories);

```
function validateLotForm(...) {
    $errors = [];
    if ($error = validateLotName($lotForm['name'])) {
        $errors['name'] = $error;
    }
    if ($error = validateLotCatogory($lotForm['category'])) {
        $errors['category'] = $error;
    }
    if ($error = validateLotDescription($lotForm['description'])) {
        $errors['description'] = $error;
    }
    if ($error = validateLotImg($fileData)) {
        $errors['lot_img'] = $error;
    }
    // ... итд
    // если массив ошибок не пуст - значит валидация не прошла
    return $errors;
}

function validateLotName(string $name) {
    // вызываем функции валидаторы для проверки заполненности / длины поля, итд
}

function validateLotCatogory(id $category_id, array $catIds) {
    // вызываем функции валидаторы для проверки заполненности / длины поля, итд
}
```
Все функции, отвечающие за валидацию формы лота поместить в отдельный файл
functions/validate/lot.php

В итоге, вся логика валидации будет в одном файле, все функции валидации
полей будут вызываться из validateLotForm. Логика валидации для каждого
поля будет отдельной названной функцией и с внятно названными аргументами.

Валидаторы заполненности и длины можно оставить как есть и вызывать их из функций
валидации полей
