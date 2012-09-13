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
class viewCmsContent extends abstractSddView {

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		$out = '';
		// Alle Content Sektionen holen die Aktiv sind
		$sSQL = "SELECT cse_ID,con_ID,cse_Type FROM tbcontentsection
		WHERE mnu_ID = ".page::menuID()." AND cse_Active = 1 ORDER BY cse_Sortorder";
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