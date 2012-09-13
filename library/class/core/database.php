<?php
require_once(BP.'/library/interface/dbConn/dbConn.php');
require_once(BP.'/library/interface/dbStmt/dbStmt.php');
require_once(BP.'/library/class/dbConn/dbConn-pdo-mysql.php');
require_once(BP.'/library/class/dbConn/dbStmt-pdo-mysql.php');

/**
 * Datenbank Fabrik
 * @author Michael Sebel <michael@sebel.ch>
 */
class database {
	
	/**
	 * CMS Datenbankverbindung holen
	 * @return dbConn Datenbankverbindung zum CMS
	 */
	public static function getConnection() {
		return(singleton::conn());
	}
	
	/**
	 * Kunden Datenbankverbindung holen
	 * @return dbConn Datenbankverbindung zum Kunden
	 */
	public static function getCustomerConnection() {
		return(singleton::custconn());
	}
	
	/**
	 * Neue CMS Verbindung holen (Nicht Gecachte Standardverbindung)
	 * @return dbConn Datenbankverbindung zum CMS
	 */
	public static function instantiateConnection() {
		// Auswahl der SQL Klasse je nach Datenbanksystem
		switch (config::SQL_TYPE) {
			case config::SQLTYPE_PDOMYSQL:
				$Conn = new dbConnPdoMysql(); break;
			default:
				throw new sddCoreException('Unknown database driver selected');
		}
		return($Conn);
	}
}