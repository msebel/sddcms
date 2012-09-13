<?php
// Library für Gruppen Admin
class moduleShopGroups extends commonModule {


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
	 * Rücksprungs-Url
	 * @var string
	 */
	private $myUrl;

	// Konstruieren und setzen der Rücksprungs URL
	public function __construct($nID = 0) {
        parent::__construct($nID);
		$this->myUrl = '/modules/shop/admin/groups/index.php?id='.page::menuID();
    }


	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn = & func_get_arg(0); // $Conn
		$this->Res = & func_get_arg(1); // $Res
	}

	// Artikelübersicht laden
	public function saveGroups() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$group = new shopArticlegroup(getInt($_POST['id'][$i]));
			$group->setTitle($_POST['title'][$i]);
			$group->setArticles($_POST['articles'][$i]);
			$group->setDeliveryEntity($_POST['delivery'][$i]);
			$group->setViewtype($_POST['viewtype'][$i]);
			// wenn vorhanden, Beschreibungstext auch speichern (Detailseite)
			if (isset($_POST['desc'])) $group->setDesc($_POST['desc'][$i]);
			$group->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop group');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location:'.$this->myUrl);
	}

	// Artikelgruppe hinzufügen
	public function addGroup() {
		$group = $this->newGroup();
		$group->save();
		// Erfolg ausgeben und weiterleiten
		logging::debug('created articlegroup #'.$group->getSagID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: '.$this->myUrl);
	}

	// Untergruppe erstellen
	public function addSubgroup(shopArticlegroup $group) {
		$subgroup = $this->newGroup();
		$subgroup->setParent($group->getSagID());
		$subgroup->save();
		// Erfolg ausgeben und weiterleiten
		logging::debug('created articlegroup #'.$subgroup->getSagID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: '.$this->myUrl);
	}

	// Neue Gruppe erstellen aus Ressourcen
	public function newGroup() {
		$group = new shopArticlegroup();
		$group->setManID(page::mandant());
		$group->setTitle('< '.$this->Res->html(1116, page::language()).' >');
		$group->setParent(0);
		return($group);
	}

	// Eine Gruppe löschen
	public function deleteGroup() {
		$group = new shopArticlegroup(getInt($_GET['delete']));
		if ($group->getManID() == page::mandant()) {
			// Gruppe löschen
			$group->delete();
			// Erfolg ausgeben
			logging::debug('deleted shopgroup #'.$group->getSagID());
			$this->setErrorSession($this->Res->html(57,page::language()));
		} else {
			// Fehler ausgeben
			logging::debug('error deleting shopgroup #'.$group->getSagID());
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Weiterleiten
		session_write_close();
		redirect('location: '.$this->myUrl);
	}

	/**
	 * Die Gruppe in $_GET['g'] laden und zurückgeben
	 * @return shopArticlegroup
	 */
	public function loadGroup() {
		$nSagID = getInt($_GET['g']);
		$group = new shopArticlegroup($nSagID);
		// Mandant prüfen, muss aktueller sein
		if ($group->getManID() != page::mandant()) {
			redirect('location: /error.php?type=noAccess');
		}
		// Wenn nicht, Artikel zurückgeben (Alles OK)
		return($group);
	}

	// Setter für aktuelle Modul-Url
    public function setUrl($value) {
		$this->myUrl = $value;
    }

	// Gibt den Backlink im Editmode (Index oder Subgruppen)
	public function getBacklink(shopArticlegroup $group) {
		// Schauen ob Parent oder nicht
		if ($group->getParent() == 0) {
			return('index.php?id='.page::menuID());
		} else {
			// Link zu Subgruppen des Parent
			return('subgroups.php?id='.page::menuID().'&g='.$group->getParent());
		}
	}
}