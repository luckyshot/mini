CREATE TABLE `user` (
	`id` int(11) NOT NULL,
	`username` varchar(30) NOT NULL,
	`full_name` varchar(100) NOT NULL,
	`email` varchar(50) NOT NULL,
	`avatar` varchar(250) NOT NULL,
	`session_token` char(64) NOT NULL,
	`oauth_token` varchar(100) NOT NULL,
	`oauth_token_secret` varchar(100) NOT NULL,
	`date_added` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `user`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `username` (`username`),
	ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `user`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
