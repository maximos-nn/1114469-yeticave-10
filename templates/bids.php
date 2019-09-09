<main>
  <?= $navigation ?>
  <section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
      <?php foreach($bids as $bid): ?>
      <?php
        if ($bid['contact']) {
            $ratesItemClass = 'rates__item--win';
            $timerClass = 'timer timer--win';
        } elseif (date_create($bid['expiration']) <= date_create()) {
            $ratesItemClass = 'rates__item--end';
            $timerClass = 'timer timer--end';
        } else {
            $ratesItemClass = '';
            $timerClass = '';
        }
      ?>
      <tr class="rates__item <?= $ratesItemClass ?>">
        <td class="rates__info">
          <div class="rates__img">
            <img src="<?= clearSpecials($bid['url']) ?>" width="54" height="40" alt="Фотография лота">
          </div>
          <h3 class="rates__title"><a href="lot.php?id=<?= $bid['id'] ?>"><?= clearSpecials($bid['name']) ?></a></h3>
          <?= $bid['contact'] ? '<p>' . clearSpecials($bid['contact']) . '</p>' : '' ?>
        </td>
        <td class="rates__category">
          <?= clearSpecials($bid['category']) ?>
        </td>
        <td class="rates__timer">
          <?php $expiration = getTimeUntil(clearSpecials($bid['expiration'])); ?>
          <div class="timer <?= $timerClass ?? ($expiration['hours'] === '00' ? 'timer--finishing' : '') ?>">
            <?= $bid['contact'] ? 'Ставка выиграла' : $expiration['hours'] . ':' . $expiration['minutes'] ?>
          </div>
        </td>
        <td class="rates__price">
          <?= formatPrice(clearSpecials($bid['amount'])) ?>
        </td>
        <td class="rates__time">
          <?= getTimeSince($bid['creation']) ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </section>
</main>
