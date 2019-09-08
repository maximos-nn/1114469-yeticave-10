<?php if ($pages): ?>
<ul class="pagination-list">
  <li class="pagination-item pagination-item-prev"><a>Назад</a></li>
  <?php foreach ($pages as $page): ?>
  <li class="pagination-item <?= $currentPage === $page ? 'pagination-item-active' : '' ?>">
    <a <?= $currentPage !== $page ? $script . '?page=' . $page . ($queryFields ? '&' . $queryFields : '') : '' ?>><?= $page ?></a>
  </li>
  <?php endforeach; ?>
  <li class="pagination-item pagination-item-next"><a href="#">Вперед</a></li>
</ul>
<?php endif; ?>
