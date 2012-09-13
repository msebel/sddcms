<?php
/**
 * View für Startseite
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewSearch extends abstractShopView {

	/**
	 * Ruft lediglich den Basiskonstruktor auf
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name) {
		parent::__construct($name);
	}

	/**
	 * Führt die View aus
	 */
	public function getContent() {
		// Suchlink erstellen
		$this->Tpl->addData(
			'SEARCH_LINK',
			'/modules/shop/view/search.php?id='.page::menuID().'&type=search'
		);
		// Suchbegriff festlegen (HTML Entfernen etc.)
		$sSearch = stringOps::getPostEscaped('search', $this->Conn);
		stringOps::noHtml($sSearch);
		// Suchergebnisse generieren
		if (!$this->createSearch($sSearch)) {
			// Wenns nicht klappte, entsprechende Meldung
			$this->Tpl->addData('SEARCH_TERM', $sSearch);
			$tPath = shopStatic::getTemplate('search-nothing');
			$sub = new templateImproved($tPath);
			$this->Tpl->addSubtemplate('LIST_SEARCHRESULT', $sub);
		}
		return($this->Tpl->output());
	}

	/**
	 * Erstellt die Suchergebnisse oder und gibt zudem
	 * true/false zurück, jenachdem ob Ergebnisse vorhanden oder nicht
	 * @param string $sSearch Suchbegriff(e)
	 * @return bool Suchergebnisse vorhanden oder nicht
	 */
	public function createSearch($sSearch) {
		$bHasResults = false;
		// SQL Statement je nach Typ definieren
		switch($_GET['type']) {
			case 'group':
				$this->Tpl->addData('SEARCH_TERM', $this->getGroupname());
				$sSQL = $this->getGroupSearch();
				break;
			case 'search':
			default:
				$this->Tpl->addData('SEARCH_TERM', $sSearch);
				$sSQL = $this->getSearch($sSearch);
				break;
		}
		// Statement ausführen und bei Bedarf Resultate einfüllen
		$tPath = shopStatic::getTemplate('article-listentry');
		$tpl = new templateImproved($tPath);
		// Liste erstellen und bestücken
		$list = new templateList($tpl);
		// Laden von Artikeln und einfügen
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$article = new shopArticle();
			$article->loadRow($row);
			// In Liste einfügen
			$Data = $article->toTemplate();
			$Data['ARTICLE_SIZE_CONTROL'] = shopStatic::addSizesControl($article);
			// In Liste einfügen
			$list->addData($Data);
			$bHasResults = true;
		}
		// Liste in das Haupttemplate einfügen
		if ($bHasResults) {
			$this->Tpl->addList('LIST_SEARCHRESULT', $list);
		}
		return($bHasResults);
	}

	/**
	 * Gibt den Namen der Gruppe oder einen Default Wert zurück
	 * @return string Name der Gruppe oder Defaultwert
	 */
	protected function getGroupname() {
		$sSQL = 'SELECT sag_Title FROM tbshoparticlegroup
		WHERE man_ID = '.page::mandant().' AND sag_ID = '.getInt($_GET['groupid']);
		$sName = $this->Conn->getFirstResult($sSQL);
		if (strlen($sName) == 0) $sName = $this->Res->html(1001,page::language());
		return($sName);
	}

	/**
	 * Sucht anhand Querystring Eingaben nach Artikeln einer Gruppe
	 * @return string SQL Query für Suche nach Gruppen
	 */
	protected function getGroupSearch() {
		return('
			SELECT tbshoparticle.sha_ID,con_ID,tbshoparticle.man_ID,sha_Image,sha_Tip,sha_Action,sha_New,
			sha_Title,sha_Price,sha_PriceAction,sha_Mwst,sha_Guarantee,sha_Articlenumber,
			sha_DeliveryEntity,sha_Purchased,sha_Removed,sha_Visited FROM tbshoparticle
			INNER JOIN tbshoparticle_articlegroup ON tbshoparticle.sha_ID = tbshoparticle_articlegroup.sha_ID
			INNER JOIN tbshoparticlegroup ON tbshoparticle_articlegroup.sag_ID = tbshoparticlegroup.sag_ID
			WHERE tbshoparticle.man_ID = '.page::mandant().' AND sha_Active = 1
			AND tbshoparticlegroup.sag_ID = '.getInt($_GET['groupid'])
		);
	}

	/**
	 * Sucht anhand Suchwort nach Artikeln im System
	 * @param string $sSearch Suchbegriff
	 * @return string SQL Query für Suche nach Begriff
	 */
	protected function getSearch($sSearch) {
		return("
			SELECT sha_ID,tbshoparticle.con_ID,tbshoparticle.man_ID,sha_Image,sha_Tip,sha_Action,sha_New,
			sha_Title,sha_Price,sha_PriceAction,sha_Mwst,sha_Guarantee,sha_Articlenumber,
			sha_DeliveryEntity,sha_Purchased,sha_Removed,sha_Visited,tbcontent.con_Content FROM tbshoparticle
			LEFT JOIN tbcontent ON tbshoparticle.con_ID = tbcontent.con_ID
			WHERE tbshoparticle.man_ID = ".page::mandant()." AND sha_Active = 1
			AND (sha_Title LIKE '%$sSearch%' OR sha_Articlenumber = '$sSearch' OR con_Content LIKE '%$sSearch%')
		");
	}
}