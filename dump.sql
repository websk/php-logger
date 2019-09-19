CREATE TABLE `logger_entry` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `created_at_ts` int NOT NULL DEFAULT '0',
    `user_full_id` varchar(255) DEFAULT NULL,
    `object_full_id` varchar(255) NOT NULL,
    `serialized_object` text,
    `user_ip` varchar(255) DEFAULT NULL,
    `comment` text,
    PRIMARY KEY (`id`),
    KEY `object_full_id` (`object_full_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
