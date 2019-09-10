<?php
require 'bootstrap.php';

$lotsPerPage = intval($config['lots_per_page']);
if ($lotsPerPage <= 0) {
    exit('В конфигурации задано некорректное количество лотов на странице.');
}

$currentPage = getIntParam($_GET, 'page') ?? 1;
$offset = ($currentPage - 1) * $lotsPerPage;

$query = $_GET['search'] ?? '';

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);

$lots = [];
$lotsCount = 0;
if ($query) {
    $lotsCount = getSearchResultsCount($dbConnection, $query);
    if ($lotsCount) {
        $lots = searchLots($dbConnection, $query, $offset, $lotsPerPage);
    }
}

dbClose($dbConnection);

$pages = [];
$pagesCount = ceil($lotsCount / $lotsPerPage);
if ($pagesCount > 1) {
    $pages = range(1, $pagesCount);
}

$navigation = includeTemplate('navigation.php', ['categories' => $categories]);
$pagination = includeTemplate(
    'pagination.php',
    [
        'pages' => $pages,
        'currentPage' => $currentPage,
        'script' => 'search.php',
        'queryFields' => 'search=' . $query
    ]
);
$mainContent = includeTemplate(
    'search.php',
    [
        'navigation' => $navigation,
        'pagination' => $pagination,
        'lots' => $lots,
        'query' => $query
    ]
);
$layoutContent = includeTemplate(
    'layout.php',
    [
        'pageTitle' => 'Результаты поиска',
        'isAuth' => (bool)$sessUser,
        'userName' => $sessUser['name'] ?? '',
        'navigation' => $navigation,
        'mainContent' => $mainContent,
        'searchQuery' => $query
    ]
);
print($layoutContent);
