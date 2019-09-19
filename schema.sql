CREATE DATABASE IF NOT EXISTS yeticave
CHARACTER SET utf8;

USE yeticave;

DROP TABLE IF EXISTS `bids`;
DROP TABLE IF EXISTS `lots`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
    id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) NOT NULL UNIQUE KEY,
    `code` varchar(255) NOT NULL UNIQUE KEY
)  CHARSET=utf8;

CREATE TABLE `users` (
    `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `creation_time` datetime NOT NULL DEFAULT NOW(),
    `email` varchar(255) NOT NULL UNIQUE,
    `name` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `avatar_path` varchar(255),
    `contact` TEXT
)  CHARSET=utf8;

CREATE TABLE `lots` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `creation_time` datetime NOT NULL DEFAULT NOW(),
  `title` varchar(255) NOT NULL,
  `description` text,
  `image_path` varchar(255) NOT NULL,
  `price` int unsigned NOT NULL,
  `expire_date` datetime NOT NULL,
  `bid_step` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `winner_id` int unsigned DEFAULT NULL,
  `category_id` int unsigned NOT NULL,
  KEY `idx_expire_date` (`expire_date`),
  KEY `idx_fk_lots_user` (`user_id`),
  KEY `idx_fk_lots_winner` (`winner_id`),
  KEY `idx_fk_lots_cat` (`category_id`),
  FULLTEXT KEY `idx_lots_title_descr` (`title`, `description`),
  CONSTRAINT `fk_lots_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lots_winner` FOREIGN KEY (`winner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lots_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) CHARSET=utf8;

CREATE TABLE `bids` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `creation_time` datetime NOT NULL DEFAULT NOW(),
  `amount` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `lot_id` int unsigned NOT NULL,
  KEY `idx_fk_bids_user` (`user_id`),
  KEY `idx_fk_bids_lot` (`lot_id`),
  CONSTRAINT `fk_bids_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_bids_lot` FOREIGN KEY (`lot_id`) REFERENCES `lots` (`id`)
) CHARSET=utf8;

INSERT INTO `categories` (`name`, `code`)
VALUES ('Доски и лыжи', 'boards'),
('Крепления', 'attachment'),
('Ботинки', 'boots'),
('Одежда', 'clothing'),
('Инструменты', 'tools'),
('Разное', 'other');

DROP procedure IF EXISTS `determine_winners`;

DELIMITER $$
CREATE PROCEDURE `determine_winners` ()
BEGIN

START TRANSACTION;

CREATE TEMPORARY TABLE new_winners_lots
SELECT id
FROM lots
WHERE expire_date <= NOW() AND winner_id IS NULL
	AND EXISTS (
		SELECT 1 FROM bids
		WHERE lot_id = lots.id
    LIMIT 1
	)
LOCK IN SHARE MODE;

UPDATE lots
SET winner_id = (
    SELECT user_id
    FROM bids
    WHERE lot_id = lots.id
    ORDER BY creation_time DESC, id DESC
    LIMIT 1
  )
WHERE EXISTS (
	SELECT 1 FROM new_winners_lots
  WHERE id = lots.id
);

COMMIT;

SELECT l.id lotId, l.winner_id winnerId, l.title, u.name, u.email
FROM lots l JOIN users u ON l.winner_id = u.id
	JOIN new_winners_lots nwl ON l.id = nwl.id;

DROP TABLE new_winners_lots;

END$$

DELIMITER ;

