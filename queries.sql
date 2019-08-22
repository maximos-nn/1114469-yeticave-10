-- На сервере не проверял. Вечером смогу.

-- Вставка категорий
INSERT INTO `categories` (`name`, `code`)
VALUES ('Доски и лыжи', 'boards'),
('Крепления', 'attachment'),
('Ботинки', 'boots'),
('Одежда', 'clothing'),
('Инструменты', 'tools'),
('Разное', 'other');

-- Вставка пользователей
INSERT INTO `users` (`email`, `name`, `password`)
VALUES ('ivanovii@yandex.ru', 'Иванов Иван Иванович', 'VmjXgcVb9PYtSOS4u0QO'),
('odevaet@gmail.com', 'Odevaet Shtany Nanoch', '5pSc7G6tZZ4BCkYitWuX');

-- Вставка объявлений
INSERT INTO `lots` (`title`, `image_path`, `price`, `expire_date`, `bid_step`, `user_id`, `category_id`)
VALUES
('2014 Rossignol District Snowboard',
'/img/lot-1.jpg',
-- Как хранить? В рублях или копейках?
10999,
'2019-08-14',
100,
1,
1),
('DC Ply Mens 2016/2017 Snowboard', '/img/lot-2.jpg', 159999, '2019-08-14 23:00', 1000, 1, 1),
('Крепления Union Contact Pro 2015 года размер L/XL', '/img/lot-3.jpg', 8000, '2019-08-15', 100, 1, 2),
('Ботинки для сноуборда DC Mutiny Charocal', '/img/lot-4.jpg', 10999, '2019-08-20', 100, 1, 3),
('Куртка для сноуборда DC Mutiny Charocal', '/img/lot-5.jpg', 7500, '2019-09-01', 100, 1, 4),
('Маска Oakley Canopy', '/img/lot-6.jpg', 5400, '2019-10-01', 50, 1, 6);

-- Вставка ставок
INSERT INTO `bids` (`amount`, `user_id`, `lot_id`)
VALUES (11099, 2, 1), (11200, 2, 1);

-- получить все категории
SELECT * FROM `categories`;

/*
получить самые новые, открытые лоты.
Каждый лот должен включать название, стартовую цену, ссылку на изображение, цену, название категории
*/
SELECT l.title, l.price, l.image_path,
-- Если это не ошибка, и требуется конечная цена, то
(SELECT amount FROM bids WHERE lot_id=l.id ORDER BY creation_time DESC LIMIT 1) `sum`, -- или JOIN
c.name
FROM lots l JOIN categories c ON l.category_id=c.id
WHERE l.expire_date > NOW(); -- l.winner_id IS NULL?

-- показать лот по его id. Получите также название категории, к которой принадлежит лот
SET @lot_id=1;
SELECT l.*, c.name
FROM lots l JOIN categories c ON l.category_id=c.id
WHERE l.id=@lot_id;

-- обновить название лота по его идентификатору
SET @new_title='modified title';
UPDATE lots
SET title=@new_title
WHERE id=@lot_id;

-- получить список ставок для лота по его идентификатору с сортировкой по дате
SELECT *
FROM bids
WHERE lot_id=@lot_id
ORDER BY creation_time; -- DESC?
