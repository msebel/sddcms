<?php
class config {
	// SQL Serverzugriff
	Const SQL_PREFIX		= '';
	Const SQL_SERVER 		= 'localhost';
	Const SQL_USER 			= 'sdd_user1';
	Const SQL_PASSWORD 		= 'password';
	Const SQL_INSTANCE 		= 'instancesdd';
	Const SQL_GLOBAL 		= 'globalsdd';
	// Art des SQL Servers
	Const SQL_TYPE 			= config::SQLTYPE_PDOMYSQL;
	Const CDN_URL = 'http://cdn.sdd1.local';
	
	// FTP Zugriff, User muss chmod Rechte haben
	Const FTP_USER 			= 'sddftp';
	Const FTP_PASSWORD 		= 'password';
	
	// Typen von SQL Datenbanken
	Const SQLTYPE_PDOMYSQL	= 3;
	
	// Installer
	Const INSTALLER_ACTIVE 	= true;
}