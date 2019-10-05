<?php
require_once __DIR__ . '/bootstrap.php';

$lotsPerPage = intval($config['lots_per_page']);

$currentPage = getIntParam($_GET, 'page') ?? 1;

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
$pagesCount = (int)ceil($lotsCount / $lotsPerPage);
if ($pagesCount && $currentPage > $pagesCount) {
    $currentPage = $pagesCount;
}

$lots = [];
if ($lotsCount) {
    $offset = ($currentPage - 1) * $lotsPerPage;
    $lots = getCategoryLots($dbConnection, $currentCategory, $offset, $lotsPerPage);
}

dbClose($dbConnection);

$pages = [];
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
        'script' => '/category.php',
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
