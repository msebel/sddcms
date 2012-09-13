<?php
/**
 * Klasse für Serverseitige FTP Verbindungen.
 * Nutzt die Credentials aus der Konfiguration um eine Verbindung
 * mit einem FTP Server herzustellen und bieten verschiedene
 * Methoden um auf diesem zu arbeiten. Derzeit wird nur der lokale
 * FTP Server unterstützt. Für externe kann diese Klasse geerbt werden.
 * @author Michael Sebel <michael@sebel.ch>
 */
class ftpConn {
	
	/**
	 * Aktuell verarbeitetes File
	 * @var string
	 */
	private $File;
	/**
	 * Benutzer der den FTP Server bedient
	 * @var string
	 */
	private $User;
	/**
	 * FTP Passwort des Benutzers
	 * @var string
	 */
	private $Pass;
	/**
	 * Identifier für die FTP Connection
	 * @var resource
	 */
	private $Stream = false;
	/**
	 * Gibt an ob das Login am FTP Server erfolgreich war
	 * @var boolean
	 */
	private $Login = false;
	
	/**
	 * FTP Objekt erstellen.
	 * Es wird direkt die Methode aufgerufen um eine 
	 * Verbindung mit dem Server herzustellen
	 */
	public function __construct() {
		$this->User = config::FTP_USER;
		$this->Pass = config::FTP_PASSWORD;
		$this->connect();
	}
	
	/**
	 * Kappt die Verbindung zum FTP Server
	 */
	public function __destruct() {
		$this->disconnect();
	}
	
	/**
	 * Wechselt in einen anderen Ordner auf dem FTP Server
	 * @param string sFolder, zu wechselnder Ordner
	 */
	public function setFolder($sFolder) {
		$this->File = $sFolder;
		$this->sanitize();
		ftp_chdir($this->Stream,$sFolder);
	}
	
	/**
	 * Ändert die Rechte einer Datei
	 * @param string sFile, Das zu verändernde File
	 * @param integer nMode, CHMOD (z.b. 667) REchte
	 */
	public function setChmod($sFile,$nMode) {
		ftp_chmod($this->Stream,$this->File.$sFile,$nMode);
	}
	
	/**
	 * Basispfad aus dem zu verarbeitenden File entfernen
	 */
	private function sanitize() {
		// Basepath entfernen
		$this->File = str_replace(BP,'',$this->File);
	}
	
	/**
	 * Verbindung zum lokalen FTP Server herstellen.
	 * Achtung: Das Skript wird mit einer Fehlermeldung
	 * getötet, wenn die Verbindung nicht funktioniert.
	 */
	private function connect() {
		$this->Stream = ftp_connect('localhost');
		if ($this->Stream != false) {
			$this->Login = ftp_login(
				$this->Stream,
				$this->User,
				$this->Pass
			);
		} else {
			die('ERROR WHILE FTP TRANSACTION');
		}
	}
	
	/**
	 * Schliesst eine geöffnete FTP Verbindung
	 */
	private function disconnect() {
		ftp_close($this->Stream);
	}
	
}