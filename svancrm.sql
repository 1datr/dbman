-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.5.37-log - MySQL Community Server (GPL)
-- ОС Сервера:                   Win32
-- HeidiSQL Версия:              8.3.0.4800
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица svan_crm.tbl_client
CREATE TABLE IF NOT EXISTS `tbl_client` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `FIO` text,
  `FIO_contact` text,
  `Company` text,
  `requisits` longtext,
  `phone` text,
  `email1` text,
  `email2` text,
  `site` text,
  `country_id` bigint(20) DEFAULT NULL,
  `where_find` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_country` (`country_id`),
  KEY `FK_where_find` (`where_find`),
  CONSTRAINT `FK_country` FOREIGN KEY (`country_id`) REFERENCES `tbl_countries` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_where_find` FOREIGN KEY (`where_find`) REFERENCES `tbl_where_find` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_client: ~2 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_client` DISABLE KEYS */;
INSERT INTO `tbl_client` (`Id`, `FIO`, `FIO_contact`, `Company`, `requisits`, `phone`, `email1`, `email2`, `site`, `country_id`, `where_find`) VALUES
	(1, 'Петр', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(2, 'Иван', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `tbl_client` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_countries
CREATE TABLE IF NOT EXISTS `tbl_countries` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_countries: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_countries` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_language
CREATE TABLE IF NOT EXISTS `tbl_language` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_language: ~2 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_language` DISABLE KEYS */;
INSERT INTO `tbl_language` (`Id`, `Name`) VALUES
	(1, 'Русский'),
	(2, 'Английский');
/*!40000 ALTER TABLE `tbl_language` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_paymethod
CREATE TABLE IF NOT EXISTS `tbl_paymethod` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_paymethod: ~7 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_paymethod` DISABLE KEYS */;
INSERT INTO `tbl_paymethod` (`Id`, `Name`) VALUES
	(1, 'Банковский расчет'),
	(2, 'Наличный расчет'),
	(3, 'Qiwi'),
	(4, 'PayPal'),
	(5, 'Карта Альфа-Банка'),
	(6, 'Webmoney'),
	(7, 'Карта Сбербанка');
/*!40000 ALTER TABLE `tbl_paymethod` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_remarks
CREATE TABLE IF NOT EXISTS `tbl_remarks` (
  `Id` bigint(20) NOT NULL,
  `zakaz_id` bigint(20) NOT NULL,
  `orfografia` text NOT NULL,
  `terminologia` text NOT NULL,
  `oformlenie` text NOT NULL,
  `translater_id` bigint(20) NOT NULL,
  `comment` longtext NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Id` (`Id`),
  KEY `FKZakaz` (`zakaz_id`),
  KEY `FKTranslater` (`translater_id`),
  CONSTRAINT `FKTranslater` FOREIGN KEY (`translater_id`) REFERENCES `tbl_translaters` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FKZakaz` FOREIGN KEY (`zakaz_id`) REFERENCES `tbl_zakaz` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_remarks: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_remarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_remarks` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_specif_translater
CREATE TABLE IF NOT EXISTS `tbl_specif_translater` (
  `Id` bigint(20) NOT NULL,
  `trans_id` bigint(20) NOT NULL,
  `lang_from` bigint(20) NOT NULL,
  `lang_to` bigint(20) NOT NULL,
  `pagecost` bigint(20) NOT NULL,
  `valuta` bigint(20) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `Id` (`Id`),
  KEY `FK_tbl_langpair_trans_tbl_translaters` (`trans_id`),
  CONSTRAINT `FK_tbl_langpair_trans_tbl_translaters` FOREIGN KEY (`trans_id`) REFERENCES `tbl_translaters` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_specif_translater: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_specif_translater` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_specif_translater` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_specif_zakaz
CREATE TABLE IF NOT EXISTS `tbl_specif_zakaz` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang_from` bigint(20) NOT NULL DEFAULT '0',
  `lang_to` bigint(20) NOT NULL DEFAULT '0',
  `zakaz` bigint(20) NOT NULL DEFAULT '0',
  `pagecount` bigint(20) DEFAULT NULL,
  `page_coast` bigint(20) DEFAULT NULL,
  `valuta_id` bigint(20) DEFAULT NULL,
  `document_name` text,
  `term` datetime DEFAULT NULL COMMENT 'срок',
  `itogo` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_tbl_langpair_tbl_language` (`lang_from`),
  KEY `FK_tbl_langpair_tbl_language_2` (`lang_to`),
  KEY `FK_tbl_langpair_tbl_zakaz` (`zakaz`),
  CONSTRAINT `FK_tbl_langpair_tbl_language` FOREIGN KEY (`lang_from`) REFERENCES `tbl_language` (`Id`),
  CONSTRAINT `FK_tbl_langpair_tbl_language_2` FOREIGN KEY (`lang_to`) REFERENCES `tbl_language` (`Id`),
  CONSTRAINT `FK_tbl_langpair_tbl_zakaz` FOREIGN KEY (`zakaz`) REFERENCES `tbl_zakaz` (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_specif_zakaz: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_specif_zakaz` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_specif_zakaz` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_srochnost
CREATE TABLE IF NOT EXISTS `tbl_srochnost` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` text,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_srochnost: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_srochnost` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_srochnost` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_translate
CREATE TABLE IF NOT EXISTS `tbl_translate` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_zakaz` bigint(20) DEFAULT NULL,
  `id_translater` bigint(20) DEFAULT NULL,
  `page_count` bigint(20) DEFAULT NULL,
  `stavka` bigint(20) DEFAULT NULL,
  `term` datetime DEFAULT NULL COMMENT 'Срок',
  `payed` bit(1) DEFAULT NULL,
  `filename` text,
  `diapazon` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_translate: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_translate` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_translate` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_translaters
CREATE TABLE IF NOT EXISTS `tbl_translaters` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `FIO` text,
  `email` text,
  `phone` text NOT NULL,
  `requisits` longtext NOT NULL,
  `paymethod_id` bigint(20) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `FKPaymethod` (`paymethod_id`),
  CONSTRAINT `FKPaymethod` FOREIGN KEY (`paymethod_id`) REFERENCES `tbl_paymethod` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_translaters: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_translaters` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_translaters` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_translater_type
CREATE TABLE IF NOT EXISTS `tbl_translater_type` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_translater_type: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_translater_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_translater_type` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_translation_thematic
CREATE TABLE IF NOT EXISTS `tbl_translation_thematic` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_translation_thematic: ~8 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_translation_thematic` DISABLE KEYS */;
INSERT INTO `tbl_translation_thematic` (`Id`, `name`) VALUES
	(1, 'Технический чертеж'),
	(2, 'Технический сертификат'),
	(3, 'Техническая инструкция'),
	(4, 'Технический другое'),
	(5, 'Медицинский справка'),
	(6, 'Медицинский инструкция'),
	(7, 'Медицинский рецептура'),
	(8, 'Экономический справка');
/*!40000 ALTER TABLE `tbl_translation_thematic` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_user
CREATE TABLE IF NOT EXISTS `tbl_user` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_user: ~22 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_user` DISABLE KEYS */;
INSERT INTO `tbl_user` (`Id`, `username`, `password`, `email`, `role`) VALUES
	(1, 'test1', 'pass1', 'test1@example.com', 'user'),
	(2, 'test2', 'pass2', 'test2@example.com', 'user'),
	(3, 'test3', 'pass3', 'test3@example.com', 'user'),
	(4, 'test4', 'pass4', 'test4@example.com', 'user'),
	(5, 'test5', 'pass5', 'test5@example.com', 'user'),
	(6, 'test6', 'pass6', 'test6@example.com', 'user'),
	(7, 'test7', 'pass7', 'test7@example.com', 'user'),
	(8, 'test8', 'pass8', 'test8@example.com', 'user'),
	(9, 'test9', 'pass9', 'test9@example.com', 'user'),
	(10, 'test10', 'pass10', 'test10@example.com', 'user'),
	(11, 'test11', 'pass11', 'test11@example.com', 'user'),
	(12, 'test12', 'pass12', 'test12@example.com', 'user'),
	(13, 'test13', 'pass13', 'test13@example.com', 'user'),
	(14, 'test14', 'pass14', 'test14@example.com', 'user'),
	(15, 'test15', 'pass15', 'test15@example.com', 'user'),
	(16, 'test16', 'pass16', 'test16@example.com', 'user'),
	(17, 'test17', 'pass17', 'test17@example.com', 'user'),
	(18, 'test18', 'pass18', 'test18@example.com', 'user'),
	(19, 'test19', 'pass19', 'test19@example.com', 'user'),
	(20, 'test20', 'pass20', 'test20@example.com', 'user'),
	(21, 'test21', 'pass21', 'test21@example.com', 'user'),
	(22, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@localhost', 'administrator');
/*!40000 ALTER TABLE `tbl_user` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_valuta
CREATE TABLE IF NOT EXISTS `tbl_valuta` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_valuta: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_valuta` DISABLE KEYS */;
INSERT INTO `tbl_valuta` (`Id`, `name`) VALUES
	(1, 'Рубль'),
	(2, 'Доллар'),
	(3, 'Евро');
/*!40000 ALTER TABLE `tbl_valuta` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_vh_zayavki
CREATE TABLE IF NOT EXISTS `tbl_vh_zayavki` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Info` longtext NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_vh_zayavki: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_vh_zayavki` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_vh_zayavki` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_where_find
CREATE TABLE IF NOT EXISTS `tbl_where_find` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `comment` longtext NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_where_find: ~4 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_where_find` DISABLE KEYS */;
INSERT INTO `tbl_where_find` (`Id`, `name`, `comment`) VALUES
	(1, 'Сайт', ''),
	(2, 'Телефон', ''),
	(3, 'Партнер', ''),
	(4, 'Реклама', '');
/*!40000 ALTER TABLE `tbl_where_find` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_zakaz
CREATE TABLE IF NOT EXISTS `tbl_zakaz` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20),
  `name` text,
  `description` longtext,
  `zakaz_state` bigint(20) DEFAULT NULL,
  `oplachen` bit(1) DEFAULT NULL,
  `chastichnaya_oplata` bigint(20) DEFAULT NULL,
  `gog_number` text,
  `dog_zakluychen` bit(1) DEFAULT NULL,
  `dog_otpravlen` bit(1) DEFAULT NULL,
  `dog_otpravlen_pochta` bit(1) DEFAULT NULL,
  `srochnost` bigint(20) DEFAULT NULL,
  `dog_poluchen` bit(1) DEFAULT NULL,
  `schet_N` text,
  `schet_otpravlen` bit(1) DEFAULT NULL,
  `schet_otpravlen_pochta` bit(1) DEFAULT NULL,
  `specif_N` text,
  `specif_otpravlen` bit(1) DEFAULT NULL,
  `specif_otpravlen_pochta` bit(1) DEFAULT NULL,
  `specif_poluchen` bit(1) DEFAULT NULL,
  `akts_N` text,
  `akts_poluchen` bit(1) DEFAULT NULL,
  `paymthod` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_Zakazi_client` (`client_id`),
  KEY `FK_tbl_zakaz_zakaz_state` (`zakaz_state`),
  KEY `FK_Paymethod` (`paymthod`),
  KEY `FK_Srochnost` (`srochnost`),
  CONSTRAINT `FK_Srochnost` FOREIGN KEY (`srochnost`) REFERENCES `tbl_srochnost` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_Paymethod` FOREIGN KEY (`paymthod`) REFERENCES `tbl_paymethod` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tbl_zakaz_zakaz_state` FOREIGN KEY (`zakaz_state`) REFERENCES `tbl_zakaz_state` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_Zakazi_client` FOREIGN KEY (`client_id`) REFERENCES `tbl_client` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_zakaz: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_zakaz` DISABLE KEYS */;
INSERT INTO `tbl_zakaz` (`Id`, `client_id`, `name`, `description`, `zakaz_state`, `oplachen`, `chastichnaya_oplata`, `gog_number`, `dog_zakluychen`, `dog_otpravlen`, `dog_otpravlen_pochta`, `srochnost`, `dog_poluchen`, `schet_N`, `schet_otpravlen`, `schet_otpravlen_pochta`, `specif_N`, `specif_otpravlen`, `specif_otpravlen_pochta`, `specif_poluchen`, `akts_N`, `akts_poluchen`, `paymthod`) VALUES
	(1, 2, '--**\r\n**--**\r\n--**', 'Заказ номер2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(2, 1, 'ячсс', 'фыфы', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(3, 2, 'фыыфс', '21121', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `tbl_zakaz` ENABLE KEYS */;


-- Дамп структуры для таблица svan_crm.tbl_zakaz_state
CREATE TABLE IF NOT EXISTS `tbl_zakaz_state` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы svan_crm.tbl_zakaz_state: ~5 rows (приблизительно)
/*!40000 ALTER TABLE `tbl_zakaz_state` DISABLE KEYS */;
INSERT INTO `tbl_zakaz_state` (`Id`, `Name`) VALUES
	(1, 'Висящий'),
	(2, 'В работе'),
	(3, 'Завершен'),
	(4, 'Отменен заказчиком'),
	(5, 'Перевозчик ...');
/*!40000 ALTER TABLE `tbl_zakaz_state` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
