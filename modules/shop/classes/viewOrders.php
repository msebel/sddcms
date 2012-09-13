<?php
/**
 * View für den Aufträge
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewOrders extends abstractShopView {

	/**
	 * Definiert um welche View es sich handelt
	 * @var string
	 */
	private $mySearch = '';

	/**
	 * Ruft lediglich den Basiskonstruktor auf
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name,$search) {
		$this->mySearch = $search;
		parent::__construct($name);
		$this->userCheck(true);
	}

	/**
	 * Führt die View aus
	 */
	public function getContent() {
		// SQL Statement erstellen (Je nach Auswahl)
		$sSQL = $this->getSearchStatement();
		// Liste erstellen und konfigurieren
		$list = $this->getList($sSQL);
		if ($list->hasData()) {
			$this->Tpl->addList('ORDER_LIST',$list);
		} else {
			// Meldung wenn keine Daten vorhanden
			$tPath = shopStatic::getTemplate('order-list-noentry');
			$tpl = new templateImproved($tPath);
			$this->Tpl->addSubtemplate('ORDER_LIST', $tpl);
		}
		return($this->Tpl->output());
	}

	/**
	 * Erstellt die Liste der Aufräge
	 * @param string $sSQL SQL Statement um Daten zu suchen
	 */
	private function getList($sSQL) {
		// Template Liste erstellen
		$tPath = shopStatic::getTemplate('order-list-entry');
		$tpl = new templateImproved($tPath);
		$list = new templateList($tpl);
		$tab = new tabRowExtender('shopTabRowDark','shopTabRowEven');
		// Mal alles hier durchgehen
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$list->addData(array(
				'ROW_CLASS' => $tab->getSpecial(),
				'ORDER_ID' => $row['sho_ID'],
				'DATE_TIME' => $this->getDate($row['sho_Date']),
				'TOTAL' => $row['sho_Total'],
				'MENU_ID' => page::menuID()
			));
		}
		return($list);
	}

	/**
	 * Verwandelt ein SQL Date in einen Human Readable
	 * String mit Datum und Zeit inkl ausschmückungen
	 * @param string $sDate SQL Datetime String
	 * @return string Human Readable EU String
	 */
	private function getDate($sDate) {
		// Ach hier verwenden wir einfach die entsprechende Library Methode
		return(dateOps::toHumanReadable($sDate));
	}

	/**
	 * Such SQL generieren
	 */
	private function getSearchStatement() {
		// Basis erstellen
		$sSQL = 'SELECT sho_ID,shu_ID,scp_ID,man_ID,sho_Total,sho_Date,sho_Payment,
		sho_State,sho_Deliveryaddress,sho_Billingaddress
		FROM tbshoporder WHERE man_ID = '.page::mandant().'
		AND shu_ID = '.shopStatic::getLoginUser()->getShuID();
		// Einschränken auf Offen/Geschlossen
		switch ($this->mySearch) {
			case 'open':
				$sSQL .= ' AND sho_State = '.shopOrder::STATE_OPEN;
				break;
			case 'closed':
			default:
				$sSQL .= ' AND sho_State = '.shopOrder::STATE_SENT;
				break;
		}
		// Auf die letzten 50 Bestellungen einschränken
		$sSQL .= ' ORDER BY sho_Date DESC LIMIT 0,50';
		return($sSQL);
	}
}