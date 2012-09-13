<?php
/**
 * View für Artikelliste
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewList extends abstractShopView {

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
		// Subtemplate für Titel
		$tPathTitle = shopStatic::getTemplate('shop-title');
		$subTitle = new templateImproved($tPathTitle);
		$this->Tpl->addSubtemplate('SHOP_TITLE',$subTitle);
		// Suchlink erstellen
		$this->Tpl->addData(
			'SEARCH_LINK',
			'/modules/shop/view/search.php?id='.page::menuID().'&type=search'
		);
		// Zufällige Artikel anzeigen
		$this->showArticles();
		// Suche nach Artikelgruppen / Untergruppen direkt
		$this->showGroupSearch();
		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Erstellt eine zufällige Auswahl von 5 Artikeln und
	 * verwendet dazu das Listen-Template für Artikel
	 */
	protected function showArticles() {
		// Template laden
		$tPath = shopStatic::getTemplate('article-listentry');
		$tpl = new templateImproved($tPath);
		// Liste erstellen und bestücken
		$list = new templateList($tpl);
		// Laden von Artikeln und einfügen
		$sSQL = 'SELECT sha_ID,con_ID,man_ID,sha_Image,sha_Tip,sha_Action,sha_New,
        sha_Title,sha_Price,sha_PriceAction,sha_Mwst,sha_Guarantee,sha_Articlenumber,
        sha_DeliveryEntity,sha_Purchased,sha_Removed,sha_Visited FROM tbshoparticle
        WHERE man_ID = '.page::mandant().' AND sha_Active = 1
		ORDER BY '.shopModuleConfig::LIST_SORTFIELD;
		$paging = new paging($this->Conn, 'list.php?id='.page::menuID());
		$paging->start($sSQL, shopModuleConfig::ARTICLES_PER_PAGE);
		$nRes = $this->Conn->execute($paging->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			$article = new shopArticle();
			$article->loadRow($row);
			$Data = $article->toTemplate();
			$Data['ARTICLE_SIZE_CONTROL'] = shopStatic::addSizesControl($article);
			// In Liste einfügen
			$list->addData($Data);
		}
		// Liste in das Haupttemplate einfügen
		$this->Tpl->addList('ARTICLE_LIST', $list);
		$this->Tpl->addData('PAGING',$paging->getHtml());
	}

	/**
	 * Gruppensuche mit Dropdown und Selbstzünder einfügen
	 */
	protected function showGroupSearch() {
		$sHtml = shopStatic::getGroupSearchDropdown();
		$this->Tpl->addData('GROUP_SEARCH', $sHtml);
	}
}