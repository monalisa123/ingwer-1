#!/usr/bin/env perl

use strict;
use warnings;
use Switch;
use Data::Dumper;

my $version = "0.1";
my $projektVerz= "/home/ingwer/demo";
my @Metafelder;
push @Metafelder,{name=>'Medium',type=>'list',table=>'journal'}; #TODO Zeitung Medium = tbJournal wird im moment noch von Ingwer benötigt
push @Metafelder,{name=>'Titel',type=>'string',field_name=>'title'};#field_name wird von Ingwer benötigt
push @Metafelder,{name=>'Resort',type=>'string'};
push @Metafelder,{name=>'Datum',type=>'date',field_name=>'release_date'};#field_name wird von Ingwer benötigt
push @Metafelder,{name=>'Autor',type=>'multilist'};
push @Metafelder,{name=>'Bezug',type=>'list'};
push @Metafelder,{name=>'Textsorte',type=>'list'};

%config = (
dbhost => 'localhost',
dbname => 'Ingwerdemo',
dbuser => 'Ingwerdemo',
dbpass => 'dE5kl1aax',
httpUser => 'ingweruser',
httpPass => 'zoslOvb42',
dbTablePrefix => '',
templatePath => 'tpl/',
uploadPath => 'tmp/',
corpusPath => "$projektVerz/cwbcorpora",
treeTagger => '/usr/local/bin/tree-tagger-german-utf8',
lexico2Xml => "$projektVerz/include/lexico2xml.pl",
perl => '/usr/bin/perl',
diff => '/usr/bin/diff -i',
cwbEncode => '/usr/local/bin/cwb-encode',
cwbMakeall => '/usr/local/bin/cwb-makeall',
webserveruser => 'www-data',
webservergroup => 'www-data'
);


################################## AB HIER Bitte nichts Verändern #########################################
my $constants= "$projektVerz/include/constants.php"; 
my $dbStruktur= "$projektVerz/DatenbankstrukturInstall.sql";
my $dbStrukturNeu= "$projektVerz/DatenbankstrukturInstallGeneriert.sql";


if ($ARGV[0] && $ARGV[0] eq "-v") {
	print "Das ist install.pl Version $version\n\n";
	exit(0);
}

if (!$ARGV[0] || $ARGV[0] eq "-h" || $ARGV[0] eq "--help") {
	print "Verwendung von install.pl:\nMetadatenfelder(\@Metafelder),  Config(\%config)  und Projektverzeichniss(\$projektVerz) in skript anpassen. Dann ausführen:\n\ninstall.pl mysqlAdmin\n\n";
	exit(0);
}


my $dbAdmin=$ARGV[0];

writeConstants();
ergaenzeMeta();
createSql();
createDB();
createHttpAccess();
installCQPweb();

sub installCQPweb {
	my $datadir = substr ($config{corpusPath},1);
	`svn export http://svn.code.sf.net/p/cwb/code/gui/cqpweb/trunk cqpweb`;       #cqweb hohlen
	rename 'cqpweb','cwb';
	open CWBCONFIG, "> $projektVerz/cwb/lib/config.inc.php" or die "kann nicht $projektVerz/cwb/lib/config.inc.php schreiben\n"; #config von cqweb schreiben
	print CWBCONFIG <<CWBC;
<?php

/* SYSTEM CONFIG SETTINGS : the same for every corpus, and CANNOT be overridden */

/* these settings should never be alterable from within CQPweb (would risk transmitting them as plaintext) */


/* adminstrators' usernames, separated by | (as in BNCweb) */
\$superuser_username = '$config{httpUser}';


/* mySQL username and password */
\$mysql_webuser = '$config{dbuser}';
\$mysql_webpass = '$config{dbpass}';
\$mysql_schema  = '$config{dbname}';
\$mysql_server  = '$config{dbhost}';



/* ---------------------- */
/* server directory paths */
/* ---------------------- */

/* variables do require a '/' before and after */
\$path_to_cwb = 'usr/local/bin';
\$path_to_apache_utils = 'usr/bin';
\$path_to_perl = 'usr/bin';

\$cqpweb_tempdir = 'usr/local/share/cqp/users';
\$cqpweb_accessdir = 'usr/local/share/cqp/users';
\$cqpweb_uploaddir = 'usr/local/share/cqp/upload';


\$cwb_datadir = '$datadir';
\$cwb_registry = 'usr/local/share/cwb/registry';

/* if mySQL returns ???? instead of proper UTF-8 symbols, change this setting true -> false or false -> true */
\$utf8_set_required = true;

?>
CWBC
	close CWBCONFIG; #Ende config schreiben
	#recht fuer verzeichnisse setzen
	`chown -R $config{webserveruser}:$config{webservergroup} $projektVerz/cwb`; #or die "kann recht nicht setzen $projektVerz/cwb";
	`chown -R $config{webserveruser}:$config{webservergroup} $projektVerz/cwbcorpora`;# or die "kann recht nicht setzen $projektVerz/cwbcorpora";

	#cwb/adm .htaccess schreiben
	open CWBHTTP, "> $projektVerz/cwb/adm/.htaccess" or die "kann nicht $projektVerz/cwb/adm/.htaccess schreiben\n"; #config von cqweb schreiben
	print CWBHTTP <<CWBH;
AuthUserFile /usr/local/share/cqp/users/.htpasswd
AuthGroupFile /usr/local/share/cqp/users/.htgroup
AuthName CQPweb
AuthType Basic
deny from all
require group superusers
satisfy any
CWBH
	close CWBHTTP;
	#superuser in dateien anlegen
	`htpasswd -b /usr/local/share/cqp/users/.htpasswd $config{httpUser} $config{httpPass}`;
	`sed -i 's/\(^superusers:.*\$\)/\1 $config{httpUser}/' /usr/local/share/cqp/users/.htgroup`;	
	#`wget http://ingwer.semtracks.org/jsorg/cwb/adm/index.php?thisF=mysqlRestore&uT=y`;
	}

sub createDB {
	print "Passwort für mysqlAdmin\n";
	`mysql --default-character-set=utf8 -h $config{dbhost} -u $dbAdmin -p < $dbStrukturNeu`;
	print "\n";
	#open MYSQL, "| mysql -h $config{dbhost} -u $dbAdmin -p < $dbStrukturNeu" or die "kann mysql -h $config{dbhost} -u $dbAdmin -p nicht öffnen";
	#print MYSQL "$dbAdminPass\n";
	#close MYSQL;
}
sub createHttpAccess{
	open HTACCESS, "> $projektVerz/.htaccess" or die "kann nicht $projektVerz/.htaccess schreiben\n";
	print HTACCESS <<HTAC;
<Files ~ "^\\.(htaccess|htpasswd)\$">
deny from all
</Files>
Options Indexes
AuthUserFile $projektVerz/.htpasswd
AuthGroupFile /dev/null
AuthName "Bitte authentifizieren Sie sich!"
AuthType Basic
require valid-user
order deny,allow
HTAC
	close HTACCESS;
	print "Passwort für http hinufügen\n";
	system("htpasswd -bc $projektVerz/.htpasswd $config{'httpUser'} $config{httpPass}"); 
}
sub writeConstants{
	open(CONSTANTS,"> $constants") or die "kann $constants nicht schreiben";
	print CONSTANTS <<PHP;
<?php
defined( '_VALID_INGWER' ) or die( 'Restricted access' );
define(DEBUG,0);

PHP

	my @co = keys %config;
	foreach my $ck ( @co){
		print CONSTANTS "\$$ck = '".$config{$ck} . "';\n";
	}
	print CONSTANTS "\n\$xmlLexico3Map = array (\n\t'DOCUMENT'=>'1',\n";
	foreach my $m (@Metafelder){
		my $field = uc replaceSql($m->{name});
		print CONSTANTS "\t'$field' => '$m->{name}',\n";
	}
	
	print CONSTANTS "\t'TEXT'=>'Textkörper'\n\t);\n";
	print CONSTANTS <<PHP2;
/* zu ueberpruefende Zeichensaetze bei Dateiupload, im Progamm alles utf8 */
\$charsetList = array("cp1252","latin1","utf8");

include_once("table.php");
?>
PHP2
	close CONSTANTS;
}
sub ergaenzeMeta {
	unshift @Metafelder, {name=>'id',type=>'int',options=>1};
	push @Metafelder, {name=>'word_count',type=>'int',options=>5};
	push @Metafelder, {name=>'lemma_count',type=>'int',options=>5};
	push @Metafelder, {name=>'Textkörper',type=>'content',options=>3,field_name=>'content'};
	my $id=0;
	foreach my $m (@Metafelder){
		if (!$m->{field_name}) {$m->{field_name} = replaceSql($m->{name});}
		$m->{id}=$id;
		$id++;
		if (!$m->{options}) {$m->{options}=7;}
		if ($m->{type} eq "list" or $m->{type} eq "multilist"){
			$m->{table}=$m->{field_name} if (!$m->{table});
			$m->{field_name}="name";
		}
		else {$m->{table}="paper";}
	}
}

sub replaceSql{
	my $str = shift;
	$str=lc $str;
	$str=~ s/ä/ae/g;
	$str=~ s/ö/oe/g;
	$str=~ s/ü/ue/g;
	$str=~ s/[^a-z0-9_]/_/g;
	return $str;
}

sub createSql{
	open(GEN,"> $dbStrukturNeu") or die "kann $dbStrukturNeu nicht schreiben";
	print GEN <<SQL;
CREATE USER '$config{dbuser}'\@'$config{dbhost}' IDENTIFIED BY '$config{dbpass}';

GRANT USAGE ON * . * TO '$config{dbuser}'\@'$config{dbhost}' IDENTIFIED BY '$config{dbpass}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

CREATE DATABASE `$config{dbname}` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

GRANT ALL PRIVILEGES ON `$config{dbname}` . * TO '$config{dbuser}'\@'$config{dbhost}';
SET PASSWORD FOR '$config{dbuser}'\@'$config{dbhost}' = PASSWORD( '$config{dbpass}' );

FLUSH PRIVILEGES;


USE `$config{dbname}`;

SQL
	open DBSTRUCTUR, "< $dbStruktur";
	print GEN <DBSTRUCTUR>;
	close DBSTRUCTUR;
	foreach my $m (@Metafelder){
		print GEN "($m->{id}, '$m->{field_name}', '$m->{name}', '$m->{type}', '$m->{table}', $m->{options})";
		print GEN ",\n" if( $m->{type} ne "content");
		print GEN ";\n" if( $m->{type} eq "content");
	}
	foreach my $m (@Metafelder){
		if ($m->{type} eq "list" or $m->{type} eq "multilist"){
			print GEN <<SQL;

CREATE TABLE IF NOT EXISTS `$m->{table}` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `paper2$m->{table}` (
	  `paper_id` int(11) NOT NULL,
	  `$m->{table}_id` int(11) NOT NULL,
	  KEY `paper_id` (`paper_id`,`$m->{table}_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL

		}
	}
print GEN <<SQL4;
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `paper`
--

CREATE TABLE IF NOT EXISTS `paper` (
SQL4
	foreach my $m (@Metafelder){
  		print GEN "  `id` int(10) unsigned NOT NULL auto_increment,\n" if ($m->{field_name} eq "id");
		print GEN "  `$m->{field_name}` date NOT NULL,\n" if( $m->{type} eq "date");
		print GEN "  `$m->{field_name}` int(10) unsigned NOT NULL,\n" if( $m->{type} eq "int" && $m->{field_name} ne "id");
		print GEN "  `$m->{field_name}` text NOT NULL,\n" if( $m->{type} eq "string");
		print GEN "  `$m->{field_name}` mediumtext NOT NULL,\n" if( $m->{type} eq "content");
	}
print GEN <<SQL3;
  `change_date` date NOT NULL,
  `content_checksum` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `release_date` (`release_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQL3
}

