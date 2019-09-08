<main>
  <?= $navigation ?>
  <div class="container">
    <section class="lots">
      <?php if ($lots): ?>
      <h2>Все лоты в категории <span>«<?= $categoryName ?>»</span></h2>
      <ul class="lots__list">
        <?php foreach ($lots as $lot) : ?>
          <li class="lots__item lot">
            <div class="lot__image">
              <img src="<?= clearSpecials($lot['url']) ?>" width="350" height="260" alt="<?= clearSpecials($lot['name']) ?>">
            </div>
            <div class="lot__info">
              <span class="lot__category"><?= clearSpecials($lot['category']) ?></span>
              <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $lot['id'] ?>"><?= clearSpecials($lot['name']) ?></a></h3>
              <div class="lot__state">
                <div class="lot__rate">
                  <span class="lot__amount">Стартовая цена</span>
                  <span class="lot__cost"><?= formatPrice(clearSpecials($lot['price'])) ?></span>
                </div>
                <?php $expiration = getTimeUntil(clearSpecials($lot['expiration'])); ?>
                <div class="lot__timer timer <?= $expiration['hours'] === '00' ? 'timer--finishing' : '' ?>">
                  <?= $expiration['hours'] . ':' . $expiration['minutes'] ?>
                </div>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
      <h2>Активные лоты в категории <span>«<?= $categoryName ?>»</span> не найдены.</h2>
      <?php endif; ?>
    </section>
    <?= $pagination ?>
  </div>
</main>
