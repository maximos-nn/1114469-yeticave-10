<main>
  <?= $navigation ?>
  <section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
      <?php foreach($bids as $bid): ?>
      <?php
        $expiration = getTimeUntil($bid['expiration']);
        if ($bid['contact']) {
            $ratesItemClass = 'rates__item--win';
            $timerClass = 'timer--win';
            $timerContent = 'Ставка выиграла';
        } elseif (date_create($bid['expiration']) <= date_create()) {
            $ratesItemClass = 'rates__item--end';
            $timerClass = 'timer--end';
            $timerContent = 'Торги окончены';
        } else {
            $ratesItemClass = '';
            $timerClass = $expiration['hours'] === '00' ? 'timer--finishing' : '';
            $timerContent = $expiration['hours'] . ':' . $expiration['minutes'];
        }
      ?>
      <tr class="rates__item <?= $ratesItemClass ?>">
        <td class="rates__info">
          <div class="rates__img">
            <img src="<?= clearSpecials($bid['url']) ?>" width="54" height="40" alt="Фотография лота">
          </div>
          <div>
            <h3 class="rates__title"><a href="lot.php?id=<?= $bid['id'] ?>"><?= clearSpecials($bid['name']) ?></a></h3>
            <p><?= clearSpecials($bid['contact']) ?></p>
          </div>
        </td>
        <td class="rates__category">
          <?= clearSpecials($bid['category']) ?>
        </td>
        <td class="rates__timer">
          <div class="timer <?= $timerClass ?>"><?= $timerContent ?></div>
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
