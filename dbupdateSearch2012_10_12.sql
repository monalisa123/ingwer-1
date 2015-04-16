-- --------------------------------------------------------
-- Ingwer update Ingwer Version 1.7.0
--
-- Tabellenstruktur für Tabelle `search_list`
--

CREATE TABLE IF NOT EXISTS `search_list` (
	  `search_id` smallint(6) NOT NULL,
	  `paper_id` int(11) NOT NULL,
	  KEY `search_id_index` (`search_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur für Tabelle `search` ändern
--

ALTER TABLE `search` ADD `type` varchar(255) NOT NULL;

--
-- Tabellenstruktur für Tabelle `paper_meta_data_type` ändern
--

ALTER TABLE `paper_meta_data_type` ADD `options` INT NULL;

--
-- Daten für Tabelle `paper_meta_data_type`
--

UPDATE `paper_meta_data_type` SET `options` = 7;
UPDATE `paper_meta_data_type` SET `options` = 5 WHERE `paper_meta_data_type`.`name` = 'word_count' ;
UPDATE `paper_meta_data_type` SET `options` = 5 WHERE `paper_meta_data_type`.`name` = 'lemma_count';
UPDATE `paper_meta_data_type` SET `options` = 3 WHERE `paper_meta_data_type`.`name` = 'Textkörper' ;

INSERT INTO `paper_meta_data_type` (`id`, `field_name`, `name`, `meta_type`, `table`, `options`) VALUES (0, 'id', 'id', 'int', 'paper', 1);

