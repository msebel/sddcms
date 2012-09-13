<?php
/**
 * View für Artikelgruppe und deren Artikel
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewGroup extends abstractShopView {

	/**
	 * Artikelgruppe die gerade angeschaut wird
	 * @var shopArticlegroup
	 */
	private $Group = NULL;

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
		// Gruppen instanzieren und Zugriff prüfen
		$this->Group = new shopArticlegroup($_GET['g']);
		if ($this->Group->getManID() != page::mandant()) {
			redirect('location: /error.php?type=noAccess');
		}

		// Subtemplate für Titel
		$tPathTitle = shopStatic::getTemplate('shop-title');
		$subTitle = new templateImproved($tPathTitle);
		$this->Tpl->addSubtemplate('SHOP_TITLE',$subTitle);
		// Liste für Artikelgruppen erstellen
		$this->getSubgroups();
		// Liste der Artikel hinzufügen
		$this->getArticles();

		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Artikel dieser Gruppe auflisten (Vorerst mit Paging)
	 */
	protected function getArticles() {
		// Wie auflisten? A/B/C Register ist noch nicht umgesetzt, daher
		// ist der Switch theoretisch wirklungslos, aber vorbereitet
		switch($this->Group->getViewtype()) {
			case shopArticlegroup::VIEWTYPE_LIST:
				$this->getArticleList();
				break;
			case shopArticlegroup::VIEWTYPE_TABS:
				//$this->getArticlesTabbed();
				$this->getArticleList();
				break;
		}
	}

	/**
	 * Zeigt die Artikel mit Paging an und handelt die Requests
	 */
	protected function getArticleList() {
		// Subtemplate laden
		$tPath = shopStatic::getTemplate('group-articles');
		$tpl = new templateImproved($tPath);
		$tpl->addData('GROUP_NAME',$this->Group->getTitle());
		// Template für Listeneinträge laden
		$tListPath = shopStatic::getTemplate('article-listentry');
		$listTpl = new templateImproved($tListPath);
		// Liste erstellen und bestücken
		$list = new templateList($listTpl);
		// Laden von Artikeln und einfügen
		$sSQL = 'SELECT tbshoparticle.sha_ID,con_ID,man_ID,sha_Image,sha_Tip,sha_Action,
    sha_New,sha_Title,sha_Price,sha_PriceAction,sha_Mwst,sha_Guarantee,
    sha_Articlenumber,sha_DeliveryEntity,sha_Purchased,sha_Removed,sha_Visited
		FROM tbshoparticle INNER JOIN tbshoparticle_articlegroup ON
		tbshoparticle_articlegroup.sha_ID = tbshoparticle.sha_ID
    WHERE man_ID = '.page::mandant().' AND sha_Active = 1
		AND sag_ID = '.$this->Group->getSagID().'
		ORDER BY '.shopModuleConfig::LIST_SORTFIELD;
		$paging = new paging(
			$this->Conn,
			'group.php?id='.page::menuID().'&g='.$this->Group->getSagID()
		);
		// Seitengrösse einstellen (Artikelgruppe oder Standard wenn nicht vorhanden)
		$nPageSize = getInt($this->Group->getArticles());
		if ($nPageSize == 0) $nPageSize = shopModuleConfig::ARTICLES_PER_PAGE;
		$paging->start($sSQL,$nPageSize,false);
		$nRes = $this->Conn->execute($paging->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			$article = new shopArticle();
			$article->loadRow($row);
			$Data = $article->toTemplate();
			$Data['ARTICLE_SIZE_CONTROL'] = shopStatic::addSizesControl($article);
			// In Liste einfügen
			$list->addData($Data);
		}
		
		// Liste in das Haupttemplate einfügen wenn Daten vorhanden
		if ($list->hasData()) {
			$tpl->addList('ARTICLE_LIST', $list);
			$tpl->addData('PAGING',$paging->getHtml());
			$this->Tpl->addSubtemplate('LIST_ARTICLES', $tpl);
		} else {
			// Meldung ausgeben, dass es hier keine Artikel gibt
			$this->Tpl->addData(
				'LIST_ARTICLES',
				'<p>'.$this->Res->html(1149, page::language()).'</p>'.shopStatic::getBackLink()
			);
		}
	}

	/**
	 * Zeigt die Artikel in einem A/B/C Register an und handelt die Requests
	 */
	protected function getArticlesTabbed() {
		throw new sddStandardException('getArticlesTabbed not yet implemented');
	}

	/**
	 * Erstellt eine Liste aller Artikelgruppen
	 */
	protected function getSubgroups() {
		// Subtemplate, wenn Gruppen vorhanden sind
		$tPath = shopStatic::getTemplate('group-subgroups');
		$tpl = new templateImproved($tPath);
		// Listentemplate für die Gruppen
		$tPathList = shopStatic::getTemplate('groups-listentry');
		$tplEntry = new templateImproved($tPathList);
		$listTpl = new templateList($tplEntry);

		// Alle Artikelgruppen laden, diese einfügen
		$sSQL = 'SELECT sag_ID,man_ID,sag_Parent,sag_Title,sag_Desc,sag_Image,
		sag_Articles,sag_Viewtype,sag_DeliveryEntity FROM tbshoparticlegroup
		WHERE man_ID = '.page::mandant().' AND sag_Parent = '.$this->Group->getSagID().'
		ORDER BY sag_Title ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$group = new shopArticlegroup();
			$group->loadRow($row);
			// Daten hinzufügen
			$listTpl->addData($group->toTemplate());
		}

		// Die Liste hinzufügen
		$tpl->addList('LIST_SUBGROUPS', $listTpl);
		// Wenn die Liste Daten hat, nur dann ins Haupttemplate einfügen
		if ($listTpl->hasData()) {
			$this->Tpl->addSubtemplate('LIST_GROUPS', $tpl);
		} else {
			$this->Tpl->addData('LIST_GROUPS','');
		}
	}
}