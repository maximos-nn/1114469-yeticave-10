<nav class="nav">
  <ul class="nav__list container">
    <?php foreach ($categories as $category): ?>
      <li class="nav__item <?= ($currentCategory ?? '') === $category['id'] ? 'nav__item--current' : '' ?>">
        <a href="category.php?category=<?= $category['id'] ?>"><?= clearSpecials($category['name']) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
