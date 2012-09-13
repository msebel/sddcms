<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/content') !== false) {
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: /controller.php?id='.$_GET['id']);
	exit;
}

/**
 * Viewmodul für das Content Modul
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewCentralContent extends abstractSddView {

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		if (isset($_GET['section'])) {
			return($this->view());
		} else {
			return($this->index());
		}
	}

	// Viewseite, wenn ein Popup mit dem Editor gemacht wurde
	public function view() {
		$nSection = getInt($_GET['section']);
		$out = '';
		// Simulieren des Aufrufs aus einer Schleife von
		// ContentSections, owner_ID und Typ holen
		$sSQL = "SELECT tbcontentsection.cse_ID,tbcontentsection.con_ID,
		tbcontentsection.cse_Type FROM tbcontentsection INNER JOIN
		tbmenu ON tbmenu.mnu_ID = tbcontentsection.mnu_ID
		WHERE tbcontentsection.cse_ID = $nSection
		AND tbmenu.man_ID = ".page::mandant();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$sData = $row;
		}
		contentView::getElement($sData['cse_ID'],$sData['con_ID'],$sData['cse_Type'],$out,$Conn);

		// System abschliessen
		$this->Tpl->setPopup();
		return($out);
	}

	// Indexseite, zeigt inhalte an. Standard View dieses Modules
	public function index() {
		$out = '';
		// Alle Content Sektionen holen die Aktiv sind
		$sSQL = "SELECT tbcontentsection.cse_ID,tbcontentsection.con_ID,
		tbcontentsection.cse_Type FROM tbcontentsection INNER JOIN
		tbmenu_contentsection ON tbcontentsection.cse_ID = tbmenu_contentsection.cse_ID
		WHERE tbmenu_contentsection.mnu_ID = ".page::menuID()."
		AND tbcontentsection.cse_Active = 1 ORDER BY tbcontentsection.cse_Sortorder";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			contentView::getElement(
				$row['cse_ID'],
				$row['con_ID'],
				$row['cse_Type'],
				$out,$this->Conn
			);
		}
		return($out);
	}
}