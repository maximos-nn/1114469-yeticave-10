<?php if ($pages): ?>
<ul class="pagination-list">
  <li class="pagination-item pagination-item-prev">
    <a <?= ($currentPage - 1) ? 'href="' . $script . '?page=' .  ($currentPage - 1) . ($queryFields ? '&' . $queryFields : '') . '"' : '' ?>>
      Назад
    </a>
  </li>
  <?php foreach ($pages as $page): ?>
  <li class="pagination-item <?= $currentPage === $page ? 'pagination-item-active' : '' ?>">
    <a <?= $currentPage !== $page ? 'href="' . $script . '?page=' . $page . ($queryFields ? '&' . $queryFields : '') . '"' : '' ?>>
      <?= $page ?>
    </a>
  </li>
  <?php endforeach; ?>
  <li class="pagination-item pagination-item-next">
    <a <?= isset($pages[$currentPage]) ? 'href="' . $script . '?page=' .  ($currentPage + 1) . ($queryFields ? '&' . $queryFields : '') . '"' : '' ?>>
      Вперед
    </a>
  </li>
</ul>
<?php endif; ?>
