<main class="container">
    <section class="promo">
        <h2 class="promo__title">Нужен стафф для катки?</h2>
        <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
        <ul class="promo__list">
            <?php foreach ($categories as $category): ?>
                <li class="promo__item promo__item--<?= clearSpecials($category['code']) ?>">
                    <a class="promo__link" href="category.php?category=<?= $category['id'] ?>"><?= clearSpecials($category['name']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <ul class="lots__list">
            <?php foreach ($lots as $lot): ?>
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
    </section>
</main>
