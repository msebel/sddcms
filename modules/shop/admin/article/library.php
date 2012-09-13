<?php
class moduleShopArticles extends commonModule {

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
	 * SQL Statement für die Suche
	 * @var string
	 */
	private $SearchSQL = '';
	/**
	 * Suchbegriff
	 * @var string
	 */
	private $SearchTerm = '';

	// Im Destruktor die Suche speichern
	public function __destruct() {
		// Such SQL speichern
		sessionConfig::set('SearchSQL', $this->SearchSQL);
		sessionConfig::set('SearchTerm', $this->SearchTerm);
	}

	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn = & func_get_arg(0); // $Conn
		$this->Res = & func_get_arg(1); // $Res
		// Initialisieren der Suche
		$this->initializeSearch();
	}

	// Artikelübersicht laden
	public function saveArticles() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nShaID = getInt($_POST['id'][$i]);
			$Article = new shopArticle($nShaID);
			$Article->setTitle($_POST['title'][$i]);
			$Article->setPrice($_POST['price'][$i]);
			$Article->setActive($_POST['active_'.$nShaID]);
			$Article->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop articles');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/index.php?id='.page::menuID());
	}

	// Neuen leeren Artikel hinzufügen
	public function addArticle() {
		$article = new shopArticle();
		// Nötigste Daten setzen
		$article->setManID(page::mandant());
		$article->setContent($this->Res->html(1020, page::language()));
		$article->setTitle('< '.$this->Res->html(1021, page::language()).' >');
		$article->save();
		// Erfolg ausgeben und weiterleiten
		logging::debug('added new shop article');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/index.php?id='.page::menuID());
	}

	// Stammdaten des aktuellen Artikel speichern
	public function saveArticle(shopArticle $article) {
		$article->setTitle($_POST['shaTitle']);
		$article->setGuarantee($_POST['shaGuarantee']);
		$article->setActive($_POST['shaActive']);
		$article->setTip($_POST['shaTip']);
		$article->setNew($_POST['shaNew']);
		$article->setAction($_POST['shaAction']);
		$article->setArticlenumber($_POST['shaArticlenumber']);
		$article->setContent($_POST['conContent']);

		// Preis je nach Aktion anzeigen
		if ($article->getAction() == 1) {
			$article->setPriceAction($_POST['shaPriceAction']);
		} else {
			$article->setPrice($_POST['shaPrice']);
		}

		// Lieferentität, wenn so eingeschaltet
		if (shopConfig::Delivery()) {
			$article->setDeliveryEntity($_POST['shaDeliveryEntity']);
		}
		// Artikel speichern
		$article->save();
		// Erfolg melden und weiterleiten
		logging::debug('saved shop article '.$nShaID);
		$this->setErrorSession($this->Res->html(57,page::language()));
		$this->resetPaging();
		session_write_close();
		redirect('location: /modules/shop/admin/article/edit.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Den Artikel in $_GET['a'] laden und zurückgeben
	public function loadArticle() {
		$nShaID = getInt($_GET['a']);
		$article = new shopArticle($nShaID);
		// Mandant prüfen, muss aktueller sein
		if ($article->getManID() != page::mandant()) {
			redirect('location: /error.php?type=noAccess');
		}
		// Wenn nicht, Artikel zurückgeben (Alles OK)
		return($article);
	}

	// Artikel löschen (wenn möglich)
	public function deleteArticle() {
		$nShaID = getInt($_GET['delete']);
		$sSQL = 'SELECT COUNT(sha_ID) FROM tbshoparticle
		WHERE man_ID = '.page::mandant().' AND sha_ID = '.$nShaID;
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$article = new shopArticle($nShaID);
			$article->delete();
			// Erfolg melden und weiterleiten
			logging::debug('deleted shop article '.$nShaID);
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting shop article '.$nShaID);
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Auf Aktuelle Seite weiterleiten
		session_write_close();
		redirect('location: /modules/shop/admin/article/index.php?id='.page::menuID());
	}

	// Gibt den aktuellen Suchbegriff zurück
	public function getSearch() {
		return($this->SearchTerm);
	}

	// Lädt die Artikel
	public function loadArticles(array &$articles) {
		$paging = new paging($this->Conn,'index.php?id='.page::menuid());
		$paging->start($this->SearchSQL, 15);
		$nRes = $this->Conn->execute($paging->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			$article = new shopArticle();
			$article->loadRow($row);
			array_push($articles,$article);
		}
		// Paging Objekt für HTML zurückgeben
		return($paging);
	}

	// Suche initialisieren
	public function initializeSearch() {
		$this->SearchSQL = sessionConfig::get('SearchSQL', '');
		$this->SearchTerm = sessionConfig::get('SearchTerm', '');
		// Wenn leer, Standard Suche nehmen
		if (strlen($this->SearchSQL) == 0) {
			$this->resetSearch();
		}
	}

	// Standard Suche anwenden (und auf Startseite des Moduls)
	public function resetSearch() {
		$this->SearchTerm = '';
		// SQL Statement definieren
		$this->SearchSQL = 'SELECT sha_ID,con_ID,man_ID,sha_Image,sha_Tip,sha_Action,sha_Active,
        sha_New,sha_Title,sha_Price,sha_PriceAction,sha_Mwst,sha_Guarantee,sha_Articlenumber,
        sha_DeliveryEntity,sha_Purchased,sha_Removed,sha_Visited FROM tbshoparticle
		WHERE man_ID = '.page::mandant().' ORDER BY sha_Title ASC';
		// Redirect auf die Startseite machen
		redirect('location: /modules/shop/admin/article/index.php?id='.page::menuID());
	}

	// Suche durchführen
	public function setSearch() {
		$this->SearchTerm = stringOps::getPostEscaped('searchTerm', $this->Conn);
		// SQL Statement definieren
		$this->SearchSQL = 'SELECT sha_ID,con_ID,man_ID,sha_Image,sha_Tip,sha_Action,sha_Active,
    sha_New,sha_Title,sha_Price,sha_PriceAction,sha_Mwst,sha_Guarantee,sha_Articlenumber,
    sha_DeliveryEntity,sha_Purchased,sha_Removed,sha_Visited FROM tbshoparticle
		WHERE man_ID = '.page::mandant().' AND sha_Title LIKE \'%'.$this->SearchTerm.'%\'
		ORDER BY sha_Title ASC';
    $this->resetPaging();
		// Redirect auf die Startseite machen
		redirect('location: /modules/shop/admin/article/index.php?id='.page::menuID());
	}
}