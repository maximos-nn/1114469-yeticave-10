<?php
require 'bootstrap.php';

$lotsPerPage = intval($config['lots_per_page']);

$currentPage = getIntParam($_GET, 'page') ?? 1;

$query = $_GET['search'] ?? '';

$dbConnection = dbConnect($config['db']);

$categories = getCategories($dbConnection);

$lots = [];
$pagesCount = 0;
if ($query) {
    $lotsCount = getSearchResultsCount($dbConnection, $query);
    $pagesCount = (int)ceil($lotsCount / $lotsPerPage);
    if ($pagesCount && $currentPage > $pagesCount) {
        $currentPage = $pagesCount;
    }
    if ($lotsCount) {
        $offset = ($currentPage - 1) * $lotsPerPage;
        $lots = searchLots($dbConnection, $query, $offset, $lotsPerPage);
    }
}

dbClose($dbConnection);

$pages = [];
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
