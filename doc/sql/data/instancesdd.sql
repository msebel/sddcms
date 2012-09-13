-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 03. April 2011 um 14:13
-- Server Version: 5.5.8
-- PHP-Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `instancesdd`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbaccess`
--

DROP TABLE IF EXISTS `tbaccess`;
CREATE TABLE IF NOT EXISTS `tbaccess` (
  `acc_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den Zugriff',
  `ugr_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Benutzergruppe der ein Zugriff gewährt wird',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Menu, auf das Zugriff gewährt wird',
  `acc_Type` tinyint(3) unsigned DEFAULT NULL COMMENT 'Hebelt Gruppenrechte aus und verteilt neues Recht',
  PRIMARY KEY (`acc_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=304 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbaddress`
--

DROP TABLE IF EXISTS `tbaddress`;
CREATE TABLE IF NOT EXISTS `tbaddress` (
  `adr_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert eine Adresse',
  `adr_Gender` tinyint(3) unsigned DEFAULT NULL COMMENT '0 = männlich / 1 = weiblich / 2 = other',
  `adr_Addition` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Adresszusatz',
  `adr_Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Titel der Adresse (etwa Dr. Prof. Etc.)',
  `adr_Firstname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Vorname der Adresse',
  `adr_Lastname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Nachname der Adresse',
  `adr_Street` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Strasse der Adresse',
  `adr_Email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'E-Mail Adresse',
  `adr_City` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Stadt der Adresse',
  `adr_Phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Telefonnummer dieser Adresse',
  `adr_Mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Handynummer dieser Adresse',
  `adr_Postbox` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Postfach der Adresse',
  `adr_Zip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Postleitzahl der Adressen',
  PRIMARY KEY (`adr_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbblogcategory`
--

DROP TABLE IF EXISTS `tbblogcategory`;
CREATE TABLE IF NOT EXISTS `tbblogcategory` (
  `blc_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zum besitzenden Blog',
  `blc_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Titel der Kategorie',
  `blc_Desc` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Beschreibung der Kategorie (HTML)',
  PRIMARY KEY (`blc_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbblogcategory_content`
--

DROP TABLE IF EXISTS `tbblogcategory_content`;
CREATE TABLE IF NOT EXISTS `tbblogcategory_content` (
  `bcc_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `blc_ID` int(10) unsigned NOT NULL COMMENT 'Verbindungskategorie',
  `con_ID` int(10) unsigned NOT NULL COMMENT 'Verbindungsbeitrag',
  PRIMARY KEY (`bcc_ID`),
  KEY `con_ID` (`con_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=60 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbcontent`
--

DROP TABLE IF EXISTS `tbcontent`;
CREATE TABLE IF NOT EXISTS `tbcontent` (
  `con_ID` int(10) unsigned NOT NULL COMMENT 'Identifiziert den Content',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zum besitzenden Menupunkt',
  `usr_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Benutzer der den Content  zuletzt geändert / erfasst hat',
  `con_Hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Seitenaufrufe dieses Contents',
  `con_Views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Effektive Besucher dieses Contents',
  `con_Date` timestamp NULL DEFAULT NULL COMMENT 'Erfassungsdatum / Zeit des Inhaltes (Erstellung)',
  `con_Modified` timestamp NULL DEFAULT NULL COMMENT 'Datum / Zeit der letzten Änderung',
  `con_DateFrom` timestamp NULL DEFAULT NULL COMMENT 'Diesen Beitrag nur ab diesem Datum anzeigen',
  `con_DateTo` timestamp NULL DEFAULT NULL COMMENT 'Diesen Beitrag nur bis zu diesem Datum zeigen.',
  `con_Active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Nicht aktiv (nicht sichtbar) / 1 = Aktiv',
  `con_ShowName` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Name des Erfassers nicht anzeigen /anzeigen',
  `con_ShowDate` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Erstelldatum nicht anzeigen / anzeigen',
  `con_ShowModified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Änderungsdatum nicht anzeigen / anzeigen',
  `con_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Titel des Contents, vor allem für News und Blogs (wird als Tags genutzt)',
  `con_Content` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'HTML Inhalt der Seite, wird plain gespeichert, und erst bei der Nutzung decodiert',
  PRIMARY KEY (`con_ID`),
  KEY `mnu_ID` (`mnu_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbcontentsection`
--

DROP TABLE IF EXISTS `tbcontentsection`;
CREATE TABLE IF NOT EXISTS `tbcontentsection` (
  `cse_ID` int(10) unsigned NOT NULL COMMENT 'Identifiziert den Contenteintrag',
  `con_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenziert je nach Typ einen Datensatz in einer anderen Tabelle (cse_Type)',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Menu zu dem diese Section gehört',
  `cse_Sortorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Anordnung der Contents',
  `cse_Type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Typ. 1 = Content, 2 = Element, 3 = Formular',
  `cse_Active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Inaktiv / 1 = Aktiv',
  `cse_Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name des Contents für die Auflistung',
  PRIMARY KEY (`cse_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbcountdown`
--

DROP TABLE IF EXISTS `tbcountdown`;
CREATE TABLE IF NOT EXISTS `tbcountdown` (
  `cnt_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den Countdown',
  `tap_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zum Teaserapp Eintrag',
  `cnt_Eventname` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name des Events, das stattfinden wird',
  `cnt_Style` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Stylesheets um den Countdown anzupassen',
  `cnt_Enddate` timestamp NULL DEFAULT NULL COMMENT 'Datum an dem der Counter auf 0 stehen wird',
  `cnt_Size` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Grösse des Counters',
  `cnt_Active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Nicht aktiv / 1 = Aktiv',
  PRIMARY KEY (`cnt_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbdirectlink`
--

DROP TABLE IF EXISTS `tbdirectlink`;
CREATE TABLE IF NOT EXISTS `tbdirectlink` (
  `drl_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige Identifikation',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Zugehörender Mandant',
  `drl_Name` varchar(50) NOT NULL COMMENT 'Darüber kann der Link aufgerufen werden',
  `drl_Url` varchar(255) NOT NULL COMMENT 'Link der effektiv aufgerufen wird',
  PRIMARY KEY (`drl_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbdomain`
--

DROP TABLE IF EXISTS `tbdomain`;
CREATE TABLE IF NOT EXISTS `tbdomain` (
  `dom_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert eine Domain',
  `dom_Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name der Domain',
  `dom_Mandant` int(10) unsigned DEFAULT NULL COMMENT 'Bevorzugter anzuzeigender Mandant der Page, wenn NULL, page_Mandant nehmen',
  `dom_Redirect` int(11) NOT NULL DEFAULT '0' COMMENT 'Redirect direkt zu einer anderen Domain',
  `page_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zu einer Page',
  PRIMARY KEY (`dom_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=58 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbelement`
--

DROP TABLE IF EXISTS `tbelement`;
CREATE TABLE IF NOT EXISTS `tbelement` (
  `ele_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert das Element',
  `owner_ID` int(10) unsigned DEFAULT NULL COMMENT 'ID zu dem dieses Element gehört, kann aber auch NULL sein, da es selbst manchmal referenziert wird (Damit kann auch 1:1 simuliert werden). Was die owner_ID bedeutet wird je nach Modul anders sein.',
  `ele_Size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Dateigrösse in Bytes',
  `ele_Downloads` int(10) unsigned DEFAULT NULL COMMENT 'Anzahl Downloads der Datei, wird nur bei Dateien vom ele_Type = 5 gemessen',
  `ele_Links` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Anzahl Verwendungen des Elements, wird bei Bibliothekseinträgen gezählt',
  `ele_Width` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Anzeigebreite eines Elementes',
  `ele_Height` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Anzeigehöhe eines Elementes',
  `ele_Type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Dateitypen 1 - 6',
  `ele_Library` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Kein Bibliothekselement',
  `ele_Thumb` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Vom Element existiert kein Thumbnail',
  `ele_Date` timestamp NULL DEFAULT NULL COMMENT 'Datum an dem die Datei hochgeladen wurde',
  `ele_Creationdate` timestamp NULL DEFAULT NULL COMMENT 'Datum an dem die Datei entstanden ist',
  `ele_Align` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'CSS Float Align als Text',
  `ele_Skin` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Skin für FLV oder MP3 Player',
  `ele_Target` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Target der Datei (Auch Systembefehle)',
  `ele_File` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Dateiname der Datei (Ohne Pfad)',
  `ele_Desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Beschreibung für title und alt Attribute',
  `ele_Longdesc` text COLLATE utf8_unicode_ci COMMENT 'Beschreibung der Datei für longdesc Attribut',
  PRIMARY KEY (`ele_ID`),
  KEY `owner_ID` (`owner_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=388 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbfaqentry`
--

DROP TABLE IF EXISTS `tbfaqentry`;
CREATE TABLE IF NOT EXISTS `tbfaqentry` (
  `faq_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den FAQ Eintrag',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Menu zu dem der FAQ Eintrag gehört',
  `faq_Answer` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zu einem Contenteintrag, der die Antwort des FAQ beschreibt',
  `faq_Sortorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Anordnung der FAQ Einträge',
  `faq_Active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Aktiv / 1 = Inaktiv',
  `faq_Question` varchar(512) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Fragestellung',
  PRIMARY KEY (`faq_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbformfield`
--

DROP TABLE IF EXISTS `tbformfield`;
CREATE TABLE IF NOT EXISTS `tbformfield` (
  `ffi_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert das Formularfeld',
  `cse_ID` int(10) unsigned DEFAULT NULL COMMENT 'Referenz zu einer ContentSection um die Felder zu gruppieren, darf NULL sein, wenn das Formular nicht in einer Contentsection erstellt wird',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zu einem Menu, falls mal direkt Formulare geplant wären',
  `ffi_Width` int(10) unsigned DEFAULT NULL COMMENT 'Breite des Feldes',
  `ffi_Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Zeigt auf ob das Feld ausgefüllt sein muss',
  `ffi_Sortorder` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Anordnung der Elemente',
  `ffi_Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name des Formularfeldes',
  `ffi_Desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Beschreibung des Feldes',
  `ffi_Type` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Typ des Feldes "checkbox", "radio", "text",  "hidden", oder "select".',
  `ffi_Class` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Spezielle Klasse für das Feld',
  `ffi_Value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Vordefinierter Wert des Feldes',
  `ffi_Email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Email Adresse an welche die eingegebenen Daten gesendet werden sollen',
  `ffi_Options` text COLLATE utf8_unicode_ci COMMENT 'Options, Trennzeichen getrennt für Selects.',
  PRIMARY KEY (`ffi_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=635 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbgallery`
--

DROP TABLE IF EXISTS `tbgallery`;
CREATE TABLE IF NOT EXISTS `tbgallery` (
  `gal_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zum entsprechenden Menupunkt',
  `gal_Text` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Beschreibung des Gallery Files',
  `gal_File` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Beschriebener Dateiname',
  PRIMARY KEY (`gal_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbimpersonation`
--

DROP TABLE IF EXISTS `tbimpersonation`;
CREATE TABLE IF NOT EXISTS `tbimpersonation` (
  `imp_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID der Impersonierung',
  `usr_ID` int(10) unsigned NOT NULL COMMENT 'Impersonierter User',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Mandant ID, damit man die Eindeutuigkeit pro Mandant machen kann',
  `imp_Access` tinyint(3) unsigned NOT NULL COMMENT 'Zugriffstyp',
  `imp_Active` tinyint(1) unsigned NOT NULL COMMENT 'Gibt an, ob der User einloggen darf',
  `imp_Alias` varchar(100) NOT NULL COMMENT 'Alias des Users',
  `imp_Security` varchar(80) NOT NULL COMMENT 'Security String (Passwort)',
  `imp_Email` varchar(80) NOT NULL COMMENT 'E-Mail Adresse der Impersonation',
  `imp_Activation` varchar(80) NOT NULL COMMENT 'Aktivierungscode',
  PRIMARY KEY (`imp_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbkalender`
--

DROP TABLE IF EXISTS `tbkalender`;
CREATE TABLE IF NOT EXISTS `tbkalender` (
  `cal_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den Kalendereintrag',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Besitzender Menupunkt',
  `ele_ID` int(10) unsigned NOT NULL COMMENT 'Element fÃ¼r Flyer Upload etc.',
  `cal_Type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Auftritt / 1 = Andere Termine',
  `cal_Active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Inaktiv / 1 = Aktiv',
  `cal_Start` timestamp NULL DEFAULT NULL COMMENT 'Beginn des Termins',
  `cal_End` timestamp NULL DEFAULT NULL COMMENT 'Ende des Termins',
  `cal_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Titel des Termins (Wenn kein Auftritt',
  `cal_Location` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Ort / Lokal an dem der Termin stattfindet',
  `cal_City` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Stadt / Dorf in dem der Termin stattfindet',
  `cal_Text` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Freitext mit HTML fÃ¼r nÃ¤here Beschreibung',
  PRIMARY KEY (`cal_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=64 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbkalender_contentsection`
--

DROP TABLE IF EXISTS `tbkalender_contentsection`;
CREATE TABLE IF NOT EXISTS `tbkalender_contentsection` (
  `ccs_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Verbindungs ID',
  `cal_ID` int(10) unsigned NOT NULL COMMENT 'Kalendereintrag',
  `cse_ID` int(10) unsigned NOT NULL COMMENT 'Section mit Formular',
  PRIMARY KEY (`ccs_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=67 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbkeyword`
--

DROP TABLE IF EXISTS `tbkeyword`;
CREATE TABLE IF NOT EXISTS `tbkeyword` (
  `key_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `owner_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zu einem Besitzer',
  `key_Keyword` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Das zu Gruppierende Schlüsselwort',
  PRIMARY KEY (`key_ID`),
  KEY `owner_ID` (`owner_ID`),
  KEY `key_Keyword` (`key_Keyword`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=176 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbkommentar`
--

DROP TABLE IF EXISTS `tbkommentar`;
CREATE TABLE IF NOT EXISTS `tbkommentar` (
  `com_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den Kommentar',
  `owner_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zu einem Thread, Blog oder News',
  `usr_ID` int(10) unsigned DEFAULT NULL COMMENT 'Benutzer der den Kommentar erfasste',
  `ele_ID` int(10) unsigned DEFAULT NULL COMMENT 'Für Forenbeiträge, Attachment',
  `com_Active` tinyint(1) NOT NULL COMMENT '0 = Inaktiv / 1 = Aktiv (sichtbar)',
  `com_Time` timestamp NULL DEFAULT NULL COMMENT 'Zeit der Erfassung des Kommentars',
  `com_Name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Alternativer Name, für nicht eingeloggte (speziell bei Blogs und News, wo login nicht erforderlich.',
  `com_IP` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'IP des Erfassers (wird nicht immer geloggt)',
  `com_Content` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Inhalt des Kommantars',
  PRIMARY KEY (`com_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbkonfig`
--

DROP TABLE IF EXISTS `tbkonfig`;
CREATE TABLE IF NOT EXISTS `tbkonfig` (
  `cfg_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert die Konfiguration',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zum konfigurierten Menu',
  `cfg_Numeric` int(11) DEFAULT NULL COMMENT 'Inhalt der Option, alternativ in Ganzzahlenform',
  `cfg_Type` tinyint(3) unsigned NOT NULL COMMENT 'Typ der Konfiguration 1 = value, 2 = numeric, 3 = text',
  `cfg_Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name der Konfiguration (sollte möglichst auch den Menutyp im Namen haben zur Unterscheidung)',
  `cfg_Value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Inhalt der Option, auch individuell mehrere mit zB Semikolon getrennt möglich.',
  `cfg_Text` text COLLATE utf8_unicode_ci COMMENT 'Konfiguration als voller Text',
  PRIMARY KEY (`cfg_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=247 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblink`
--

DROP TABLE IF EXISTS `tblink`;
CREATE TABLE IF NOT EXISTS `tblink` (
  `lnk_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den Link',
  `mnu_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Menupunkt zu dem der Link gehört',
  `lnc_ID` int(10) unsigned NOT NULL COMMENT 'Eventuel zugewiesene Link Kategorie',
  `lnk_Clicks` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Zählt die Klicks auf den Link',
  `lnk_Active` tinyint(1) unsigned NOT NULL COMMENT 'Gibt an ob Link sichtbar ist',
  `lnk_Date` timestamp NULL DEFAULT NULL COMMENT 'Erfassungsdatum des Links',
  `lnk_Sortorder` tinyint(3) unsigned NOT NULL COMMENT 'Anordnung der Links',
  `lnk_Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name des Links',
  `lnk_Target` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Target des Links (Blank, Parent etc)',
  `lnk_URL` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'URL des Links',
  `lnk_Desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Beschreibung des Links',
  PRIMARY KEY (`lnk_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblinkcategory`
--

DROP TABLE IF EXISTS `tblinkcategory`;
CREATE TABLE IF NOT EXISTS `tblinkcategory` (
  `lnc_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Menu zu dem die Kategorien gehören',
  `lnc_Order` smallint(5) unsigned NOT NULL COMMENT 'Sortierungsnummer',
  `lnc_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Titel der Kategorie',
  PRIMARY KEY (`lnc_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblocation`
--

DROP TABLE IF EXISTS `tblocation`;
CREATE TABLE IF NOT EXISTS `tblocation` (
  `mlc_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID einer Location',
  `map_ID` int(10) unsigned NOT NULL COMMENT 'Karte die damit verbunden ist',
  `mlc_Type` tinyint(3) unsigned NOT NULL COMMENT '1 = location, 2 = routestart, 3 = via, 4 = routeend',
  `mlc_Sortorder` tinyint(3) unsigned NOT NULL COMMENT 'Sortierung (Nur für Routenpunkte)',
  `mlc_Name` varchar(255) NOT NULL COMMENT 'Name der Location (Adminbereiche)',
  `mlc_Latitude` double NOT NULL COMMENT 'Latitude der Location',
  `mlc_Longitude` double NOT NULL COMMENT 'Longitude der Location',
  `mlc_Query` varchar(255) NOT NULL COMMENT 'Ursprüngliche Suchanfrage',
  `mlc_Icon` varchar(100) NOT NULL COMMENT 'URL zu einem speziellen Icon',
  `mlc_Html` text NOT NULL COMMENT 'HTML Code für die Location (Optional)',
  PRIMARY KEY (`mlc_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tblogging`
--

DROP TABLE IF EXISTS `tblogging`;
CREATE TABLE IF NOT EXISTS `tblogging` (
  `log_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'ID of the mandant',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Menu ID where it happened',
  `usr_ID` int(10) unsigned NOT NULL COMMENT 'User ID to resolve',
  `log_Type` tinyint(3) unsigned NOT NULL COMMENT 'Type of the error',
  `log_Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date and time on which the error occured',
  `log_Userinfo` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Resolved menu name',
  `log_Menuinfo` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Alias, Full Name and ID of the user who caused the error',
  `log_Error` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Eventually stack trace, error or exception message (as possible)',
  `log_Referer` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Information about the refering site',
  `log_Urlinfo` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Current browser url',
  `log_Postdata` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Full POST data stack',
  `log_Getdata` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Full GET data stack',
  `log_Sessiondata` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Stack of Session data',
  PRIMARY KEY (`log_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5115 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbmandant`
--

DROP TABLE IF EXISTS `tbmandant`;
CREATE TABLE IF NOT EXISTS `tbmandant` (
  `man_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert einen Mandanten',
  `page_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Seite dieses Mandanten',
  `tas_ID` int(10) unsigned DEFAULT NULL COMMENT 'Standardteaser',
  `man_Start` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Startmenupunkt des Mandanten',
  `ugr_AdminID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID der Standardadmingruppe des Mandanten',
  `man_Allwidth` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Breite Content und Teaser',
  `man_Contentwidth` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Breite des Contentbereichs',
  `man_Language` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Sprachnummer (0,1,2,3) des Mandanten',
  `man_Inactive` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Wenn 1, wird die Webseite nicht angezeigt',
  `man_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Standardtitel für Metatags',
  `man_Metadesc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Beschreibung für Metatags (auf jeder Seite)',
  `man_Metakeys` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Keywörter für Metatags (auf jeder Seite)',
  `man_Metaauthor` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Meta Tag Author',
  `man_Verify` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`man_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=27 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbmap`
--

DROP TABLE IF EXISTS `tbmap`;
CREATE TABLE IF NOT EXISTS `tbmap` (
  `map_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID der Karte',
  `map_Class` varchar(50) NOT NULL COMMENT 'Ursprungsklasse',
  `map_Name` varchar(255) NOT NULL COMMENT 'Name der Karte (Für Adminbereich)',
  `map_Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Erstelldatum der Karte',
  `map_Zoom` tinyint(3) unsigned NOT NULL COMMENT 'Zoomfaktor der Karte',
  PRIMARY KEY (`map_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbmap_menu`
--

DROP TABLE IF EXISTS `tbmap_menu`;
CREATE TABLE IF NOT EXISTS `tbmap_menu` (
  `mam_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Verbindungs ID',
  `map_ID` int(10) unsigned NOT NULL COMMENT 'Karte der Verbindung',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Menu der Verbindung',
  `mam_Sortorder` tinyint(3) unsigned NOT NULL COMMENT 'Sortierung',
  PRIMARY KEY (`mam_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbmenu`
--

DROP TABLE IF EXISTS `tbmenu`;
CREATE TABLE IF NOT EXISTS `tbmenu` (
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Identifiziert einen Menupunkt',
  `man_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zum besitzenden Mandanten',
  `typ_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zum Menutyp dieses Punktes',
  `tas_ID` int(10) unsigned DEFAULT NULL COMMENT 'Definiert den zum Menu gehörenden Teaser',
  `mnu_Index` int(11) NOT NULL DEFAULT '0' COMMENT 'Index innerhalb der Ordnung (zB 1.2 – 1.8)',
  `mnu_Redirect` int(10) unsigned DEFAULT NULL COMMENT 'Öffnet einen anderen Menupunkt',
  `mnu_Active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Nicht aktiv / 1  = Aktive Seite',
  `mnu_Invisible` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Nicht sichtbar / 1 = Sichtbar',
  `mnu_Secured` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Keine CUG / 1 = CUG',
  `mnu_Image` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Imagemenu (Bilder nehmen)',
  `mnu_Item` varchar(36) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Ordnungselement dieses Elements (zB 1.1.2)',
  `mnu_Parent` varchar(36) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Elternelement dieses Elements',
  `mnu_Shorttag` varchar(36) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Kurztag zum Aufrufen des Menu',
  `mnu_Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name des Menus',
  `mnu_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mnu_External` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Externer Direktlink',
  `mnu_Metakeys` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mnu_Metadesc` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`mnu_ID`),
  KEY `man_ID` (`man_ID`),
  KEY `mnu_Parent` (`mnu_Parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbmenutyp`
--

DROP TABLE IF EXISTS `tbmenutyp`;
CREATE TABLE IF NOT EXISTS `tbmenutyp` (
  `typ_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Identifiziert ein Menu',
  `typ_Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name des Menutyps',
  `typ_Adminpath` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Pfad zur Startseite des Admin des Moduls',
  `typ_Viewpath` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Pfad zur Startseite für die View des Moduls',
  `typ_Type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 = Normal / 1 = Nur Admin / 2 = Nur View',
  `page_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Da individueller Typ, nur für bestimmten Kunden gültig (Bestimmte Page, nicht Mandant)',
  PRIMARY KEY (`typ_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbmenu_contentsection`
--

DROP TABLE IF EXISTS `tbmenu_contentsection`;
CREATE TABLE IF NOT EXISTS `tbmenu_contentsection` (
  `mcs_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert die Verknüpfung',
  `cse_ID` int(10) unsigned NOT NULL COMMENT 'Contentsection der zentralen Verknüpfung',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Menu dem die zentrale Verknüpfung gehört',
  `mcs_Sort` tinyint(3) unsigned NOT NULL COMMENT 'Sortierung der Verknüpfungen per Menu ID',
  PRIMARY KEY (`mcs_ID`),
  KEY `mnu_ID` (`mnu_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbmenu_impersonation`
--

DROP TABLE IF EXISTS `tbmenu_impersonation`;
CREATE TABLE IF NOT EXISTS `tbmenu_impersonation` (
  `mni_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID der Verbindung',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'Menu der Verbindung',
  `imp_ID` int(10) unsigned NOT NULL COMMENT 'Impersonation der Verbindung',
  PRIMARY KEY (`mni_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tboptions`
--

DROP TABLE IF EXISTS `tboptions`;
CREATE TABLE IF NOT EXISTS `tboptions` (
  `opt_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert eine Option',
  `man_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Zugehöriger Mandant',
  `opt_Field` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Feldname der Option',
  `opt_Value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Inhalt / Definition der Option',
  PRIMARY KEY (`opt_ID`),
  KEY `opt_Field` (`opt_Field`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbowner`
--

DROP TABLE IF EXISTS `tbowner`;
CREATE TABLE IF NOT EXISTS `tbowner` (
  `owner_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige Besitzer ID',
  PRIMARY KEY (`owner_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1488 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbowner_wiki`
--

DROP TABLE IF EXISTS `tbowner_wiki`;
CREATE TABLE IF NOT EXISTS `tbowner_wiki` (
  `oww_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID für die Verbindung',
  `owner_ID` int(10) unsigned NOT NULL COMMENT 'Owner der Verbindung',
  `wki_ID` int(10) unsigned NOT NULL COMMENT 'Wiki Verbindung',
  PRIMARY KEY (`oww_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbpage`
--

DROP TABLE IF EXISTS `tbpage`;
CREATE TABLE IF NOT EXISTS `tbpage` (
  `page_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert eine Webseite',
  `adr_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz definiert die verantwortliche Person',
  `design_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Design der Seite',
  `page_Mandant` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Standardmässiger Mandant der Webseite',
  `page_Admindesign` int(10) unsigned NOT NULL COMMENT 'Alternative Design ID für den Adminbereich',
  `page_News` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Kontaktperson bekommt News über Neuerungen',
  `page_Stats` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Kontaktperson bekommt Besucherstatistiken',
  `page_Individual` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Absoluter Pfad zu einem speziellen Template, welches als Introstartseite verwendet werden kann',
  `page_Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name der Webseite (zur internen Erkennung)',
  PRIMARY KEY (`page_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbresource`
--

DROP TABLE IF EXISTS `tbresource`;
CREATE TABLE IF NOT EXISTS `tbresource` (
  `res_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Identifiziert den Sprachinhalt mit res_Language',
  `res_Language` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Identifiziert den Sprachinhalt mit res_ID',
  `res_Text` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Inhalt der Resource selbst',
  PRIMARY KEY (`res_ID`,`res_Language`),
  KEY `res_ID` (`res_ID`),
  KEY `res_Language` (`res_Language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbroute`
--

DROP TABLE IF EXISTS `tbroute`;
CREATE TABLE IF NOT EXISTS `tbroute` (
  `mrt_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID der Route',
  `map_ID` int(10) unsigned NOT NULL COMMENT 'Karte die damit verbunden ist',
  `mrt_Name` varchar(255) NOT NULL COMMENT 'Name der Route (Für Adminbereiche)',
  PRIMARY KEY (`mrt_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbroute_location`
--

DROP TABLE IF EXISTS `tbroute_location`;
CREATE TABLE IF NOT EXISTS `tbroute_location` (
  `mrl_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Verbindungs ID',
  `mrt_ID` int(10) unsigned NOT NULL COMMENT 'Route',
  `mlc_ID` int(10) unsigned NOT NULL COMMENT 'Location',
  PRIMARY KEY (`mrl_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopaddition`
--

DROP TABLE IF EXISTS `tbshopaddition`;
CREATE TABLE IF NOT EXISTS `tbshopaddition` (
  `sac_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ÏD der Konfiguration',
  `shc_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zur Shopkonfiguration',
  `sac_Field` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name des Konfigurationswerts',
  `sac_Value` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Wert für die Konfiguration',
  PRIMARY KEY (`sac_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopaddress`
--

DROP TABLE IF EXISTS `tbshopaddress`;
CREATE TABLE IF NOT EXISTS `tbshopaddress` (
  `sad_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Besitzender Mandant',
  `sad_Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Anrede',
  `sad_Firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Vorname',
  `sad_Lastname` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nachname',
  `sad_Street` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Strasse',
  `sad_Email` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Email Adresse',
  `sad_City` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Stadt',
  `sad_Phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Telefonnummer',
  `sad_Zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Postleitzahl',
  PRIMARY KEY (`sad_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoparticle`
--

DROP TABLE IF EXISTS `tbshoparticle`;
CREATE TABLE IF NOT EXISTS `tbshoparticle` (
  `sha_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID des Artikels',
  `con_ID` int(10) unsigned NOT NULL COMMENT 'ID des Content für Freitext',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Besitzender Mandant',
  `sha_Image` int(10) unsigned NOT NULL COMMENT 'Hauptbild ID',
  `sha_Tip` tinyint(1) NOT NULL COMMENT '0 = Nichts / 1 = Artikel des Tages',
  `sha_Action` tinyint(1) NOT NULL COMMENT '0 = Nichts / 1 = Aktionsartikel',
  `sha_New` tinyint(1) NOT NULL COMMENT '0 = Nichts / 1 = Neuer Artikel',
  `sha_Active` tinyint(1) NOT NULL COMMENT 'Gibt an ob der Artikel gekauft werden kann',
  `sha_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Titel des Artikels',
  `sha_Price` decimal(10,2) NOT NULL COMMENT 'Preis des Artikels',
  `sha_PriceAction` decimal(10,2) NOT NULL COMMENT 'Preis während einer Aktion',
  `sha_Mwst` decimal(2,2) NOT NULL COMMENT 'Mehrwertsteuersatz',
  `sha_Guarantee` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Gibt Garantiedefinitionen an',
  `sha_Articlenumber` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Externe Artikelnummer (Standard = sha_ID)',
  `sha_DeliveryEntity` int(10) unsigned NOT NULL COMMENT 'Anzahl Gewichtseinheiten für Versand (Überschreibt Gruppenkonfig)',
  `sha_Purchased` int(10) unsigned NOT NULL COMMENT 'Anzahl käufe dieses Artikels',
  `sha_Removed` int(10) unsigned NOT NULL COMMENT 'So oft wurde der Artikel aus dem Warenkobr entfernt',
  `sha_Visited` int(10) unsigned NOT NULL COMMENT 'Anzahl aufrufe dieses Artikels',
  PRIMARY KEY (`sha_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoparticlegroup`
--

DROP TABLE IF EXISTS `tbshoparticlegroup`;
CREATE TABLE IF NOT EXISTS `tbshoparticlegroup` (
  `sag_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID der Gruppe',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Gehört zu diesem Mandant',
  `sag_Parent` int(10) unsigned NOT NULL COMMENT 'Übergeordnetes Element (0 = Hauptelement)',
  `sag_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Titel der Gruppe',
  `sag_Desc` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Beschreibung der Gruppe',
  `sag_Image` int(10) unsigned NOT NULL COMMENT 'Elementen ID für ein Bild',
  `sag_Articles` int(10) unsigned NOT NULL COMMENT 'Wieviele Artikel pro Seite zeigt die Kategorie an',
  `sag_Viewtype` tinyint(3) unsigned NOT NULL COMMENT '0 = Alle Artikel zeigen / 1 = ABC Register',
  `sag_DeliveryEntity` int(10) unsigned NOT NULL COMMENT 'Anzahl Liefereinheiten der Artikel in der Gruppe',
  PRIMARY KEY (`sag_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoparticlesize`
--

DROP TABLE IF EXISTS `tbshoparticlesize`;
CREATE TABLE IF NOT EXISTS `tbshoparticlesize` (
  `saz_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID der Artikelgrösse',
  `sha_ID` int(10) unsigned NOT NULL COMMENT 'Grösse für diesen Artikel',
  `saz_Value` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name der Grösse z.B XXL',
  `saz_Priceadd` decimal(10,2) NOT NULL COMMENT 'Preis der Draufgeschlagen wird',
  `saz_Primary` tinyint(1) NOT NULL COMMENT '0 = Sekundärwahl/ 1 = Vorauswahl',
  PRIMARY KEY (`saz_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoparticle_articlegroup`
--

DROP TABLE IF EXISTS `tbshoparticle_articlegroup`;
CREATE TABLE IF NOT EXISTS `tbshoparticle_articlegroup` (
  `saa_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Verbindungs ID',
  `sha_ID` int(10) unsigned NOT NULL COMMENT 'Artikel der Verbindung',
  `sag_ID` int(10) unsigned NOT NULL COMMENT 'Artikelgruppe der Verbindung',
  PRIMARY KEY (`saa_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoparticle_stockarea`
--

DROP TABLE IF EXISTS `tbshoparticle_stockarea`;
CREATE TABLE IF NOT EXISTS `tbshoparticle_stockarea` (
  `sas_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID Der Verbindung',
  `sha_ID` int(10) unsigned NOT NULL COMMENT 'Artikel der Verbindung',
  `ssa_ID` int(10) unsigned NOT NULL COMMENT 'Lager der Verbindung',
  `sas_Stock` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Anzahl Artikel an Lager',
  `sas_Ontheway` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Anzahl Artikel auf dem Weg',
  `sas_Remark` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Bemerkungen zum Artikel (Lieferzeit etc.)',
  PRIMARY KEY (`sas_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopconfig`
--

DROP TABLE IF EXISTS `tbshopconfig`;
CREATE TABLE IF NOT EXISTS `tbshopconfig` (
  `shc_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID der Konfiguration',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zum Besitzenden Mandanten',
  `usr_ID` int(10) unsigned NOT NULL COMMENT 'User ID zum erstellen von Impersonationen',
  `shc_Maillogo` int(10) unsigned NOT NULL COMMENT 'Referenz zu einem Element mit dem Logo drin',
  `shc_Loginmenu` int(10) unsigned NOT NULL COMMENT 'Menupunkt an dem sich die Impersonation Logins orientieren',
  `shc_IBAN` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'IBAN Nummer des Shopanbieters',
  `shc_Post` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Postkonto des Shopanbieters',
  `shc_Payment` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Weitere Kontoangaben, wenn nötig',
  `shc_Deliverycost` decimal(10,2) NOT NULL COMMENT 'Lieferkosten statisch (wenn Delivery = 0)',
  `shc_Tipoftheday` tinyint(1) NOT NULL COMMENT '0 = Ausgeschaltet / 1 = Eingeschaltet',
  `shc_Stockdata` tinyint(1) NOT NULL COMMENT '0 = Ausgeschaltet / 1 = Eingeschaltet',
  `shc_Stockconfig` tinyint(1) NOT NULL COMMENT '0 = Beide / 1 = Nur Versandlager',
  `shc_Templates` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Pfad zu den Templates (HTML)',
  `shc_Mails` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Pfad zu den Mailvorlagen',
  `shc_MwstConfig` tinyint(1) NOT NULL COMMENT '0 = Auf allen Preisen ist Mwst drauf / 1 = Mwst anhand Artikel zugerechnet',
  `shc_Delivery` tinyint(1) NOT NULL COMMENT '0 = DeliveryCost nehmen / 1 = Staffelkonfiguration',
  `shc_BillMaximum` int(10) unsigned NOT NULL COMMENT 'Maximalwert ab dem Rechnung nicht mehr erlaubt ist',
  `shc_BillActive` tinyint(1) NOT NULL COMMENT '0 = Auf Rechnung geht nicht / 1 = Auf Rechnung, wenn Kunde aktiviert ist',
  `shc_PaypalActive` tinyint(1) NOT NULL COMMENT '0 = Kein PayPal / 1 = PayPal möglich (Konfig über shopaddition)',
  `shc_GlobalMwst` float NOT NULL COMMENT 'Globale Mehrwertsteuer für Artikel, welche keine haben',
  `shc_Conditioning` tinyint(3) unsigned NOT NULL COMMENT '0 = Keine Konditionen / 1 = Mengenrabatt primär / 2 = Kundenrabatt primär / 3 = Beide Rabatte',
  PRIMARY KEY (`shc_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopcoupon`
--

DROP TABLE IF EXISTS `tbshopcoupon`;
CREATE TABLE IF NOT EXISTS `tbshopcoupon` (
  `scp_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige interne ID',
  `shu_ID` int(10) unsigned NOT NULL COMMENT 'User dem der Gutschein gehört',
  `scp_Number` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Gutscheinnummer zur Eingabe',
  `scp_Value` decimal(10,2) NOT NULL COMMENT 'Währungswert des Gutscheins',
  `scp_Activated` tinyint(1) NOT NULL COMMENT '0 = Nicht benutzt / 1 = Genutzt, abgelaufen',
  `scp_Validuntil` timestamp NULL DEFAULT NULL COMMENT 'Ablaufdatum des Gutscheins',
  PRIMARY KEY (`scp_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopdeliveryentity`
--

DROP TABLE IF EXISTS `tbshopdeliveryentity`;
CREATE TABLE IF NOT EXISTS `tbshopdeliveryentity` (
  `sde_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID der Versandkostenkonfiguration',
  `shc_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zur Shopkonfiguration',
  `sde_Order` int(10) unsigned NOT NULL COMMENT 'Sortierung',
  `sde_Entities` int(10) unsigned NOT NULL COMMENT 'Ab wie vielen Einheiten gilt der Preis',
  `sde_Cost` decimal(10,2) NOT NULL COMMENT 'Versandpreis (Höchster Order = Maximaler Preis)',
  PRIMARY KEY (`sde_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopdynamicdata`
--

DROP TABLE IF EXISTS `tbshopdynamicdata`;
CREATE TABLE IF NOT EXISTS `tbshopdynamicdata` (
  `sdd_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `sha_ID` int(10) unsigned NOT NULL COMMENT 'Artikel zu dem dieser dynamische Wert gehört',
  `sdf_ID` int(10) unsigned NOT NULL COMMENT 'Besitzer mit Metaddaten des Feldes',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Mandant',
  `sdd_Value` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Wert des Feldes',
  PRIMARY KEY (`sdd_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopdynamicfield`
--

DROP TABLE IF EXISTS `tbshopdynamicfield`;
CREATE TABLE IF NOT EXISTS `tbshopdynamicfield` (
  `sdf_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Mandant, dem das Feld gehört',
  `sdf_Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name des Feldes',
  `sdf_Default` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Defaultwert (Wert oder ID von tbshopdynamicvalue)',
  `sdf_Type` tinyint(3) unsigned NOT NULL COMMENT '0 = Text, 1 = Singleselect (Dropdown, Radio), 3 = Multiple (Checkboxen), 4 = Upload',
  PRIMARY KEY (`sdf_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopdynamicvalue`
--

DROP TABLE IF EXISTS `tbshopdynamicvalue`;
CREATE TABLE IF NOT EXISTS `tbshopdynamicvalue` (
  `sdv_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `sdf_ID` int(10) unsigned NOT NULL COMMENT 'Gehört als Vorgabewert zu diesem Feld',
  `sdv_Value` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Wert für Anzeige',
  `sdv_Order` int(10) unsigned NOT NULL COMMENT 'Sortierung der Werte',
  PRIMARY KEY (`sdv_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopmasscondition`
--

DROP TABLE IF EXISTS `tbshopmasscondition`;
CREATE TABLE IF NOT EXISTS `tbshopmasscondition` (
  `smc_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `shc_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zur Konfiguration',
  `smc_Order` int(10) unsigned NOT NULL COMMENT 'Sortierung der Rabatte',
  `smc_Amount` decimal(10,2) NOT NULL COMMENT 'Menge (Preismenge in CHF)',
  `smc_Condition` tinyint(3) unsigned NOT NULL COMMENT 'Rabatt in Prozent',
  PRIMARY KEY (`smc_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoporder`
--

DROP TABLE IF EXISTS `tbshoporder`;
CREATE TABLE IF NOT EXISTS `tbshoporder` (
  `sho_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID der Bestellung',
  `shu_ID` int(10) unsigned NOT NULL COMMENT 'Benutzer der die Bestellung machte',
  `scp_ID` int(10) unsigned NOT NULL COMMENT 'Eingelöster Gutschein (0, wenn keiner)',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Besitzender Mandant',
  `sho_Total` decimal(10,2) NOT NULL COMMENT 'Total der Rechnung',
  `sho_Date` timestamp NULL DEFAULT NULL COMMENT 'Datum der Bestellung',
  `sho_Payment` tinyint(3) unsigned NOT NULL COMMENT 'Art der Zahlung (Vorauskasse / Rechnung)',
  `sho_State` tinyint(3) unsigned NOT NULL COMMENT '0 = Warenkorb / 1 = Offen (Bestellt) / 2 = Bezahlt / 3 = Abgesendet',
  `sho_Deliveryaddress` int(10) unsigned NOT NULL COMMENT 'Lieferadresse (Referenz)',
  `sho_Billingaddress` int(10) unsigned NOT NULL COMMENT 'Rechnungsadresse für die Bestellung',
  PRIMARY KEY (`sho_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=774 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoporderarticle`
--

DROP TABLE IF EXISTS `tbshoporderarticle`;
CREATE TABLE IF NOT EXISTS `tbshoporderarticle` (
  `soa_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `sho_ID` int(10) unsigned NOT NULL COMMENT 'Gehört zu dieser Bestellung',
  `sha_ID` int(10) unsigned NOT NULL COMMENT 'Referenz zum Originalartikel',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Besitzender Mandant',
  `soa_Title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Titel des Artikels',
  `soa_Size` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Grössenangabe (Wenn vorhanden)',
  `soa_Price` decimal(10,2) NOT NULL COMMENT 'Preis des Artikels',
  `soa_Mwst` decimal(2,2) NOT NULL COMMENT 'Mehrwertsteuersatz',
  `soa_Guarantee` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Gibt Garantiedefinitionen an',
  `soa_Articlenumber` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Externe Artikelnummer (Standard = sha_ID)',
  `soa_DeliveryEntity` int(10) unsigned NOT NULL COMMENT 'Lieferentität (Kopie)',
  PRIMARY KEY (`soa_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6134 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshoppurchaseamount`
--

DROP TABLE IF EXISTS `tbshoppurchaseamount`;
CREATE TABLE IF NOT EXISTS `tbshoppurchaseamount` (
  `spv_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `sha_ID` int(10) unsigned NOT NULL COMMENT 'Zugehörender Artikel',
  `spv_Value` int(10) unsigned NOT NULL COMMENT 'Mögliche Kaufgrösse',
  PRIMARY KEY (`spv_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopstockarea`
--

DROP TABLE IF EXISTS `tbshopstockarea`;
CREATE TABLE IF NOT EXISTS `tbshopstockarea` (
  `ssa_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID des Lagers',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Zugehöriger Mandant',
  `ssa_Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name des Lagers',
  `ssa_Opening` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Öffnungszeiten',
  `ssa_Delivery` tinyint(1) NOT NULL COMMENT '0 = Normal / 1 = Versandlager',
  PRIMARY KEY (`ssa_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopuser`
--

DROP TABLE IF EXISTS `tbshopuser`;
CREATE TABLE IF NOT EXISTS `tbshopuser` (
  `shu_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `man_ID` int(10) unsigned NOT NULL COMMENT 'Besitzender Mandant',
  `imp_ID` int(10) unsigned NOT NULL COMMENT 'ID der Impersonation',
  `shu_Billable` tinyint(1) NOT NULL COMMENT '0 = Rechnung nicht erlaubt / 1 = Kann auf Rechnung bestellen',
  `shu_Condition` int(10) unsigned NOT NULL COMMENT 'Rabatt des Users (Default = 0)',
  `shu_Active` tinyint(1) NOT NULL COMMENT '0 = Benutzer deaktivier / 1 = Benutzer kann Shop nutzen',
  PRIMARY KEY (`shu_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbshopuser_address`
--

DROP TABLE IF EXISTS `tbshopuser_address`;
CREATE TABLE IF NOT EXISTS `tbshopuser_address` (
  `sua_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `shu_ID` int(10) unsigned NOT NULL COMMENT 'Besitzender User',
  `sad_ID` int(10) unsigned NOT NULL COMMENT 'Lieferadresse',
  `sua_Type` tinyint(3) unsigned NOT NULL COMMENT '1 = Rechnungsadresse / 2 = Lieferadresse',
  `sua_Primary` tinyint(1) NOT NULL COMMENT '0 = Ja, Hauptadresse / 1 = Zusatzadresse',
  PRIMARY KEY (`sua_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbteaser`
--

DROP TABLE IF EXISTS `tbteaser`;
CREATE TABLE IF NOT EXISTS `tbteaser` (
  `tap_ID` int(10) unsigned NOT NULL COMMENT 'Identifiziert den Teasereintrag',
  `man_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Besitzender Mandant',
  `tty_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Typ der Teaser applikation',
  `tap_Title` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Titel der Teaserapplikation',
  PRIMARY KEY (`tap_ID`),
  KEY `man_ID` (`man_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbteaserentry`
--

DROP TABLE IF EXISTS `tbteaserentry`;
CREATE TABLE IF NOT EXISTS `tbteaserentry` (
  `ten_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den Teasereintrag',
  `tap_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zum Teaserapplikationseintrag',
  `con_ID` int(10) unsigned DEFAULT NULL COMMENT 'Referenz zu einem weiterführenden Content',
  `ele_ID` int(10) unsigned DEFAULT NULL COMMENT 'Referenz zu einem bild, welches angezeigt wird',
  `mnu_ID` int(10) unsigned DEFAULT NULL COMMENT 'Referenz zu einem vorhandenen Menupunkt',
  `ten_Date` timestamp NULL DEFAULT NULL COMMENT 'Datum, welches angezeigt werden soll (Für Headlines wirds immer angezeigt, für Content nie)',
  `ten_Content` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Inhalt des Teasereintrages',
  PRIMARY KEY (`ten_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbteaserkonfig`
--

DROP TABLE IF EXISTS `tbteaserkonfig`;
CREATE TABLE IF NOT EXISTS `tbteaserkonfig` (
  `cfg_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert die Konfiguration',
  `tap_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Referenz zum konfigurierten Teaser',
  `cfg_Numeric` int(11) DEFAULT NULL COMMENT 'Inhalt der Option, alternativ in Ganzzahlenform',
  `cfg_Type` tinyint(3) unsigned NOT NULL COMMENT 'Typ der Konfiguration 1 = value, 2 = numeric, 3 = text',
  `cfg_Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name der Konfiguration',
  `cfg_Value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Inhalt der Option, auch individuell mehrere mit zB Semikolon getrennt möglich.',
  `cfg_Text` text COLLATE utf8_unicode_ci COMMENT 'Konfiguration als voller Text',
  PRIMARY KEY (`cfg_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbteasersection`
--

DROP TABLE IF EXISTS `tbteasersection`;
CREATE TABLE IF NOT EXISTS `tbteasersection` (
  `tas_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert einen Teaserbereich',
  `man_ID` int(10) unsigned NOT NULL,
  `tas_Desc` varchar(100) CHARACTER SET latin1 NOT NULL COMMENT 'Beschreibt den Teaserbereich',
  PRIMARY KEY (`tas_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbteasersection_teaser`
--

DROP TABLE IF EXISTS `tbteasersection_teaser`;
CREATE TABLE IF NOT EXISTS `tbteasersection_teaser` (
  `tsa_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert die Verbindung von Section / App',
  `tas_ID` int(10) unsigned NOT NULL COMMENT 'Section der Verbindung',
  `tap_ID` int(10) unsigned NOT NULL COMMENT 'App der Verbindung',
  `tsa_Sortorder` tinyint(3) unsigned NOT NULL COMMENT 'Sortierung der Elemente innerhalb Verbindung',
  `tsa_Active` tinyint(3) unsigned NOT NULL COMMENT 'Gibt an ob das Element Aktiv ist',
  `tsa_Imported` tinyint(3) unsigned NOT NULL COMMENT 'Gibt an, ob das Element importiert ist (1)',
  PRIMARY KEY (`tsa_ID`),
  KEY `tap_ID` (`tap_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=48 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbteasertyp`
--

DROP TABLE IF EXISTS `tbteasertyp`;
CREATE TABLE IF NOT EXISTS `tbteasertyp` (
  `tty_ID` int(10) unsigned NOT NULL COMMENT 'Identifiziert einen Teasertypen',
  `tty_Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name des Typs',
  `tty_Adminpath` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Pfad zur Startseite des Admin des Moduls',
  `tty_Viewpath` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Pfad zur Startseite für die View des Moduls',
  `tty_Classname` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name der zu ladenen Objektinstanz',
  `page_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Da individueller Typ, nur für bestimmten Kunden gültig (Bestimmte Page, nicht Mandant)',
  PRIMARY KEY (`tty_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbuser`
--

DROP TABLE IF EXISTS `tbuser`;
CREATE TABLE IF NOT EXISTS `tbuser` (
  `usr_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert den Benutzer',
  `adr_ID` int(10) unsigned DEFAULT NULL COMMENT 'Eventuelle Adresse dieser Benutzers',
  `man_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Mandant für den dieses Login gilt',
  `usr_Start` int(10) unsigned DEFAULT NULL COMMENT 'Startmenupunkt des Benutzers',
  `usr_Access` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `usr_Alias` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Benutzername des Benutzers zum einloggen',
  `usr_Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Benutzername des Benutzers zum anzeigen',
  `usr_Security` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Hash Passwort 32, Hach User 32 und halber Hash Salz 16 = 80 Zeichen für das Login',
  PRIMARY KEY (`usr_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=140 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbuseraccess`
--

DROP TABLE IF EXISTS `tbuseraccess`;
CREATE TABLE IF NOT EXISTS `tbuseraccess` (
  `uac_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID',
  `mnu_ID` int(10) unsigned NOT NULL COMMENT 'MEnupunkt dem Rechte eingeräumt werden',
  `usr_ID` int(10) unsigned NOT NULL COMMENT 'USer dem Rechte eingeräumt werden',
  `uac_Type` tinyint(3) unsigned NOT NULL COMMENT 'Rechtetyp, 0 = CUG / 1 = Admin',
  PRIMARY KEY (`uac_ID`),
  KEY `mnu_ID` (`mnu_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbusergroup`
--

DROP TABLE IF EXISTS `tbusergroup`;
CREATE TABLE IF NOT EXISTS `tbusergroup` (
  `ugr_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifikation der Benutzergruppe',
  `man_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `ugr_Desc` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Name der Gruppe',
  `ugr_Start` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Startmenupunkt der Gruppe, kann durch die Startseite pro User überschrieben werden.',
  PRIMARY KEY (`ugr_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=60 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbuser_usergroup`
--

DROP TABLE IF EXISTS `tbuser_usergroup`;
CREATE TABLE IF NOT EXISTS `tbuser_usergroup` (
  `uug_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiziert  Verbindung zwischen User / Gruppe',
  `usr_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Benutzer der Verbindung',
  `ugr_ID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Benutzergruppe der Verbindung',
  PRIMARY KEY (`uug_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=78 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbwiki`
--

DROP TABLE IF EXISTS `tbwiki`;
CREATE TABLE IF NOT EXISTS `tbwiki` (
  `wki_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID des Wiki',
  `wki_Adminuser` int(10) unsigned NOT NULL COMMENT 'Admin User für impersonation',
  `wki_Cuguser` int(10) unsigned NOT NULL COMMENT 'CUG User für impersonation',
  `wki_Open` tinyint(1) NOT NULL COMMENT 'Gibt an, ob die Registrierung offen ist',
  `wki_Title` varchar(100) NOT NULL COMMENT 'Titel des Wiki für Startseite',
  `wki_Text` text NOT NULL COMMENT 'Einleitungstext für Wiki auf Startseite',
  PRIMARY KEY (`wki_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tbwikientry`
--

DROP TABLE IF EXISTS `tbwikientry`;
CREATE TABLE IF NOT EXISTS `tbwikientry` (
  `wke_ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Eindeutige ID des Wiki Eintrages',
  `con_ID` int(10) unsigned NOT NULL COMMENT 'VErbindung zum eigentlichen Content',
  `imp_ID` int(10) unsigned NOT NULL COMMENT 'Impersonierter User der den Eintrage erstellt/bearbeitet hat',
  `wki_ID` int(10) unsigned NOT NULL COMMENT 'Zu diesem Wiki gehört der Eintrag',
  `wke_Version` int(10) unsigned NOT NULL COMMENT 'Version des Eintrages',
  `wke_Parent` int(10) unsigned NOT NULL COMMENT 'Vorherige Version (con_ID)',
  `wke_Session` char(32) NOT NULL COMMENT 'Session die den Eintrag zuletzt bearbeitete',
  PRIMARY KEY (`wke_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;