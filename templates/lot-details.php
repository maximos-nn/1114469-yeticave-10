<main>
    <?= $navigation ?>
    <section class="lot-item container">
      <h2><?= clearSpecials($lot['name']) ?></h2>
      <div class="lot-item__content">
        <div class="lot-item__left">
          <div class="lot-item__image">
            <img src="<?= clearSpecials($lot['url']) ?>" width="730" height="548" alt="Фотография лота">
          </div>
          <p class="lot-item__category">Категория: <span><?= clearSpecials($lot['category']) ?></span></p>
          <p class="lot-item__description"><?= clearSpecials($lot['description']) ?></p>
        </div>
        <div class="lot-item__right">
            <div class="lot-item__state">
                <?php $expiration = getTimeUntil(clearSpecials($lot['expiration'])); ?>
                <div class="lot-item__timer timer <?= $expiration['hours'] === '00' ? 'timer--finishing' : '' ?>">
                        <?= $expiration['hours'] . ':' . $expiration['minutes'] ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= formatPrice(clearSpecials($lot['price'])) ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= formatPrice(strval(calcNextBid(intval($lot['price']), intval($lot['step'])))) ?></span>
                        </div>
                    </div>
            <?php if ($canCreateBids): ?>
            <form class="lot-item__form" action="lot.php?id=<?= $lot['id'] ?>" method="post" autocomplete="off">
              <p class="lot-item__form-item form__item <?= isset($errors['cost']) ? 'form__item--invalid' : '' ?>">
                <label for="cost">Ваша ставка</label>
                <input id="cost" type="text" name="cost" placeholder="<?= formatPrice(strval(calcNextBid(intval($lot['price']), intval($lot['step'])))) ?>">
                <span class="form__error"><?= $errors['cost'] ?? '' ?></span>
              </p>
              <button type="submit" class="button">Сделать ставку</button>
            </form>
            <?php endif; ?>
          </div>
          <?php if ($bids): ?>
          <div class="history">
            <h3>История ставок (<span><?= count($bids) ?></span>)</h3>
            <table class="history__list">
              <?php foreach ($bids as $bid): ?>
              <tr class="history__item">
                <td class="history__name"><?= clearSpecials($bid['name']) ?></td>
                <td class="history__price"><?= formatPrice(clearSpecials($bid['amount'])) ?></td>
                <td class="history__time"><?= getTimeSince($bid['creation']) ?></td>
              </tr>
              <?php endforeach; ?>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
</main>
