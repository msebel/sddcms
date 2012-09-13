<?php
/**
 * Gibt den HTML Code für eine gegebene Section zurück
 * @author Michael Sebel <michael@sebel.ch>
 */
class GetSection extends baseRequest {
	
	/**
	 * Gibt den HTML Code für eine gegebene Section zurück
	 */
	public function output() {
		$nSection = getInt($_POST['section']);
		$out = '';
		// Sections dieses Content Menus holen
		$sSQL = "SELECT cse_ID,cse_Type,con_ID FROM tbcontentsection
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbcontentsection.mnu_ID
		WHERE tbcontentsection.cse_ID = $nSection 
		AND tbmenu.man_ID = ".page::mandant();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			contentView::getElement(
				$row['cse_ID'],
				$row['con_ID'],
				$row['cse_Type'],
				$out,$this->Conn
			);
		}
		// Header und Daten ausgeben
		header('Content-type: text/html; charset=ISO-8859-1');
		echo $out;
	}
}