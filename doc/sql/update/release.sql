/* Global */


/* Instance */

ALTER TABLE  `tbmenu` ADD  `mnu_Blank` TINYINT( 1 ) NOT NULL
COMMENT  'Gibt an, ob das Menu in einem neuen Fenster geöffnet werden soll' AFTER  `mnu_Image`;

ALTER TABLE  `tbshoporder` ADD  `sho_Message` TEXT NOT NULL
COMMENT  'Hinweis des Shop Users' AFTER  `sho_Billingaddress`;
