-- Ingwer und Collate beachten

CREATE TABLE IF NOT EXISTS `paper2author` (
	  `paper_id` int(11) NOT NULL,
	  `author_id` int(11) NOT NULL,
	  KEY `paper_id` (`paper_id`,`author_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;


	-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `paper2domain`
--

CREATE TABLE IF NOT EXISTS `paper2domain` (
	  `paper_id` int(11) NOT NULL,
	  `domain_id` int(11) NOT NULL,
	  KEY `paper_id` (`paper_id`,`domain_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `paper2journal`
--

CREATE TABLE IF NOT EXISTS `paper2journal` (
	  `paper_id` int(11) NOT NULL,
	  `journal_id` int(11) NOT NULL,
	  KEY `paper2journal` (`paper_id`,`journal_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `paper2press_text_type`
--

CREATE TABLE IF NOT EXISTS `paper2press_text_type` (
	  `paper_id` int(11) NOT NULL,
	  `press_text_type_id` int(11) NOT NULL,
	  KEY `paper_id` (`paper_id`,`press_text_type_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `paper2serial`
--

CREATE TABLE IF NOT EXISTS `paper2serial` (
	  `paper_id` int(11) NOT NULL,
	  `serial_id` int(11) NOT NULL,
	  KEY `paper_id` (`paper_id`,`serial_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `geopolitics_relation` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`name` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `paper2geopolitics_relation` (
	`paper_id` INT NOT NULL ,
	`geopolitics_relation_id` INT NOT NULL ,
	INDEX ( `paper_id` , `geopolitics_relation_id` )
) ENGINE = MYISAM  DEFAULT CHARSET=utf8;

lock Tables annotation write,annotation_position write,author write,category write,category_parent write,domain write,paper2author write,paper2domain write,paper2journal write,paper2press_text_type  write,paper2geopolitics_relation write,paper2serial write,geopolitics_relation write,journal write,paper write,paper_meta_data write,paper_meta_data_type write,press_text_type write,search write,search_categorie write,search_meta_data write,serial write,session write,user write;


INSERT INTO paper2author (paper_id,author_id) SELECT paper.id,paper.author_id FROM paper;
INSERT INTO paper2domain (paper_id,domain_id) SELECT paper.id,paper.domain_id FROM paper;
INSERT INTO paper2journal (paper_id,journal_id) SELECT paper.id,paper.journal_id FROM paper;
INSERT INTO paper2press_text_type (paper_id,press_text_type_id) SELECT paper.id,paper.press_text_type_id FROM paper;
INSERT INTO paper2serial (paper_id,serial_id) SELECT paper.id,paper.serial_id FROM paper;


INSERT INTO geopolitics_relation (name) SELECT value FROM `paper_meta_data` GROUP BY value;


INSERT INTO  paper2geopolitics_relation (paper_id,geopolitics_relation_id) SELECT paper_meta_data.paper_id, geopolitics_relation.id FROM paper_meta_data , geopolitics_relation  WHERE paper_meta_data.value = geopolitics_relation.name;

UPDATE `paper_meta_data_type` SET `field_name` = 'name',`table` = 'geopolitics_relation' WHERE `paper_meta_data_type`.`id` =8;
UPDATE `paper_meta_data_type` SET `meta_type` = 'string' WHERE `paper_meta_data_type`.`id` =9;

ALTER TABLE `paper` CHANGE `page` `page` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `paper` DROP `author_id` ,
DROP `journal_id` ,
DROP `domain_id` ,
DROP `press_text_type_id` ,
DROP `serial_id` ;

DROP TABLE `paper_meta_data`;

unlock Tables;

