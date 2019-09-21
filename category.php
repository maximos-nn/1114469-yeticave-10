<?php
require 'bootstrap.php';

$lotsPerPage = intval($config['lots_per_page']);
if ($lotsPerPage <= 0) {
    exit('В конфигурации задано некорректное количество лотов на странице.');
}

$currentPage = getIntParam($_GET, 'page') ?? 1;
$offset = ($currentPage - 1) * $lotsPerPage;

if (!($currentCategory = getIntParam($_GET,'category'))) {
    http_response_code(404);
    showError('404');
}

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);
if (!in_array(strval($currentCategory), array_column($categories, 'id'), true)) {
    dbClose($dbConnection);
    http_response_code(404);
    showError('404');
}

$lotsCount = getCategoryLotsCount($dbConnection, $currentCategory);
$lots = [];
if ($lotsCount) {
    $lots = getCategoryLots($dbConnection, $currentCategory, $offset, $lotsPerPage);
}

dbClose($dbConnection);

$pages = [];
$pagesCount = (int)ceil($lotsCount / $lotsPerPage);
if ($pagesCount > 1) {
    $pages = range(1, $pagesCount);
}

$categoryName = $categories[
    array_search($currentCategory, array_column($categories, 'id'))
    ]['name'];

$navigation = includeTemplate('navigation.php', ['categories' => $categories, 'currentCategory' => strval($currentCategory)]);
$pagination = includeTemplate(
    'pagination.php',
    [
        'pages' => $pages,
        'currentPage' => $currentPage,
        'script' => 'category.php',
        'queryFields' => 'category=' . $currentCategory
    ]
);
$mainContent = includeTemplate(
    'category.php',
    [
        'navigation' => $navigation,
        'pagination' => $pagination,
        'lots' => $lots,
        'categoryName' => $categoryName
    ]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Все лоты',
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent
    ]
);
print($layoutContent);
