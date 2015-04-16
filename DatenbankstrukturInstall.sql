SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
--
-- Datenbank: `Ingwer`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `annotation`
--

CREATE TABLE IF NOT EXISTS `annotation` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `paper_id` int(10) unsigned NOT NULL,
  `category_id` smallint(5) unsigned NOT NULL,
  `user_id` smallint(6) NOT NULL,
  `comment` mediumtext NOT NULL,
  `last_edit` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `paper_id` (`paper_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `annotation`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `annotation_position`
--

CREATE TABLE IF NOT EXISTS `annotation_position` (
  `annotation_id` bigint(20) unsigned NOT NULL,
  `offset` int(10) unsigned NOT NULL,
  `length` int(10) unsigned NOT NULL,
  `lemma` varchar(100) NOT NULL,
  KEY `offset` (`offset`,`length`),
  KEY `annotation_id` (`annotation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `annotation_position`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `color` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `category`
--

INSERT INTO `category` (`id`, `name`, `comment`, `color`) VALUES
(1, 'POS', 'Part of Speech', '');


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `category_parent`
--

CREATE TABLE IF NOT EXISTS `category_parent` (
  `category_id_parent` smallint(5) unsigned NOT NULL,
  `category_id_child` smallint(5) unsigned NOT NULL,
  KEY `category_id_parent` (`category_id_parent`,`category_id_child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `category_parent`
--
INSERT INTO `category_parent` (`category_id_parent`, `category_id_child`) VALUES
(0, 1);


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `paper_meta_data_type`
--

CREATE TABLE IF NOT EXISTS `paper_meta_data_type` (
  `id` smallint(5) unsigned NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `meta_type` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  `options` int(11) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `search`
--

CREATE TABLE IF NOT EXISTS `search` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `search_date` date NOT NULL,
  `search_text` text NOT NULL,
  `include` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------
--
-- Tabellenstruktur für Tabelle `search_list`
--

CREATE TABLE IF NOT EXISTS `search_list` (
	  `search_id` smallint(6) NOT NULL,
	  `paper_id` int(11) NOT NULL,
	  KEY `search_id_index` (`search_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `search_categorie`
--

CREATE TABLE IF NOT EXISTS `search_categorie` (
  `category_id` smallint(5) unsigned NOT NULL,
  `search_id` smallint(6) NOT NULL,
  `include` tinyint(1) NOT NULL,
  KEY `search_id` (`search_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `search_meta_data`
--

CREATE TABLE IF NOT EXISTS `search_meta_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `search_id` smallint(5) unsigned NOT NULL,
  `paper_meta_data_type_id` smallint(5) unsigned NOT NULL,
  `search` varchar(255) NOT NULL,
  `search2` varchar(255) NOT NULL,
  `include` tinyint(1) NOT NULL,
  `relation` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;


--
-- Daten für Tabelle `search_meta_data`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `last` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=54 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `user`
--


--
-- Daten für Tabelle `paper_meta_data_type`
--

INSERT INTO `paper_meta_data_type` (`id`, `field_name`, `name`, `meta_type`, `table`,`options`) VALUES
