<?php 
class blogCategory extends commonModule {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	/**
	 * Prefix für Session Namen
	 * @var string
	 */
	private $SessionName = 'blogCategories_';
	/**
	 * Referenz zum Kategorienobjekt
	 * @var blogCategory
	 */
	public $Categories = NULL;
	
	// DB Verbindung erhalten und Daten laden,
	// wenn dies nicht schon passiert ist
	public function __construct(&$Conn) {
		$this->Conn = $Conn;
		$this->SessionName .= page::menuID();
		if (!$this->loaded()) {
			$this->loadInitial();
		} else {
			$this->Categories = $_SESSION[$this->SessionName];
		}
	}
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Zerstören des Objekts, alles in Session speichern
	public function __destruct() {
		$_SESSION[$this->SessionName] = $this->Categories;
	}
	
	// Zugriff auf Kategorie prüfen
	public function checkAccess() {
		$nBlcID = getInt($_GET['category']);
		$sSQL = "SELECT COUNT(blc_ID) FROM tbblogcategory
		WHERE blc_ID = $nBlcID AND mnu_ID = ".page::menuID();
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Eine Kategorie zurückgeben
	public function get($nID) {
		return($this->Categories['c'.$nID]);
	}
	
	// Kategorie hinzufügen
	public function addCategory() {
		$nCurrentEntry = getInt($_GET['entry']);
		$sTitle = $this->Res->html(632,page::language());
		$sSQL = "INSERT INTO tbblogcategory (mnu_ID,blc_Title)
		VALUES (".page::menuID().",'< $sTitle >')";
		$this->Conn->command($sSQL);
		// Ressourcen neu laden
		$this->loadInitial();
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/categories/index.php?id='.page::menuID().'&entry='.$nCurrentEntry);
	}
	
	// Alle Kategorien speichern
	public function saveCategories() {
		$nCurrentEntry = getInt($_GET['entry']);
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nBlcID = getInt($_POST['id'][$i]);
			$sTitle = $_POST['title'][$i];
			stringOps::noHtml($sTitle);
			$this->Conn->escape($sTitle);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbblogcategory SET
			blc_Title = '$sTitle' WHERE blc_ID = $nBlcID";
			// Titel auch im RAM anpassen
			$this->Conn->command($sSQL);
		}
		// RAM in Session speichern, vor dem schliessen
		$this->loadInitial();
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved blog categories');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/categories/index.php?id='.page::menuID().'&entry='.$nCurrentEntry);
	}
	
	// Eine Kategorie speichern
	public function saveCategory() {
		$nCurrentEntry = getInt($_GET['entry']);
		$nBlcID = getInt($_GET['category']);
		// Daten validieren
		$sTitle = $_POST['blcTitle'];
		stringOps::noHtml($sTitle);
		$this->Conn->escape($sTitle);
		$sDesc = $_POST['blcDesc'];
		stringOps::htmlEntRev($sDesc);
		$this->Conn->escape($sDesc);
		// Statement erstellen und abfeuern
		$sSQL = "UPDATE tbblogcategory SET
		blc_Title = '$sTitle', blc_Desc = '$sDesc'
		WHERE blc_ID = $nBlcID";
		$this->Conn->command($sSQL);
		// RAM in Session speichern, vor dem schliessen
		$this->loadInitial();
		// Erfolg melden und weiterleiten
		logging::debug('saved blog category');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/categories/edit.php?id='.page::menuID().'&category='.$nBlcID.'&entry='.$nCurrentEntry);
	}
	
	// Kategorie löschen
	public function deleteCategory() {
		$nCurrentEntry = getInt($_GET['entry']);
		// Content löschen
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(blc_ID) FROM tbblogcategory
		WHERE blc_ID = $nDeleteID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tbblogcategory WHERE blc_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Ressourcen neu laden
			$this->loadInitial();
			// Erfolg melden und weiterleiten
			logging::debug('deleted blog category');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
			redirect('location: /admin/blog/categories/index.php?id='.page::menuID().'&entry='.$nCurrentEntry); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting blog category');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/blog/categories/index.php?id='.page::menuID().'&entry='.$nCurrentEntry); 
		}
	}
	
	// Gibt zurück, ob die Blogkategorien in der Session sind
	private function loaded() {
		return(isset($_SESSION[$this->SessionName]));
	}
	
	// Lädt initial alle Kategorieninfos aus der Datenbank
	private function loadInitial() {
		$sSQL = "SELECT blc_ID,mnu_ID,blc_Title,blc_Desc 
		FROM tbblogcategory WHERE mnu_ID = ".page::menuID()."
		ORDER BY blc_Title ASC";
		$nRes = $this->Conn->execute($sSQL);
		// RAM Array und Session Array erneuern
		$this->Categories = array();
		while ($row = $this->Conn->next($nRes)) {
			$this->Categories['c'.$row['blc_ID']] = $row;
		}
		$_SESSION[$this->SessionName] = $this->Categories;
	}
}