<?php
/**
 * View für Artikelgruppen
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewGroups extends abstractShopView {

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

		// Subtemplate für Einleitung HTML
		$tPathText = shopStatic::getTemplate('groups-text');
		$subText = new templateImproved($tPathText);
		$this->Tpl->addSubtemplate('SHOP_DESCRIPTION', $subText);

		// Liste für Artikelgruppen erstellen
		$this->getGrouplist();

		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Erstellt eine Liste aller Artikelgruppen
	 */
	protected function getGrouplist() {
		$tPathList = shopStatic::getTemplate('groups-listentry');
		$tplEntry = new templateImproved($tPathList);
		$listTpl = new templateList($tplEntry);

		// Alle Artikelgruppen laden, diese einfügen
		$sSQL = 'SELECT sag_ID,man_ID,sag_Parent,sag_Title,sag_Desc,sag_Image,
		sag_Articles,sag_Viewtype,sag_DeliveryEntity FROM tbshoparticlegroup
		WHERE man_ID = '.page::mandant().' AND sag_Parent = 0
		ORDER BY sag_Title ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$group = new shopArticlegroup();
			$group->loadRow($row);
			// Daten hinzufügen
			$listTpl->addData($group->toTemplate());
		}

		// Die Liste hinzufügen
		$this->Tpl->addList('LIST_GROUPS', $listTpl);
	}
}