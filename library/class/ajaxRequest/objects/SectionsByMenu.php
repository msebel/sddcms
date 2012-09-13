<?php
/**
 * Gibt Optionen für Select Felder aus, für
 * Contentsections innerhalb eines Menus
 * @author Michael Sebel <michael@sebel.ch>
 */
class SectionsByMenu extends baseRequest {
	
	/**
	 * Gibt Optionen für Select Felder aus, für
 	 * Contentsections innerhalb eines Menus
	 */
	public function output() {
		$out = '<xml>';
		$nMenuID = getInt($_POST['menu']);
		$nNoMail = getInt($_POST['nomail']);
		// Sections dieses Content Menus holen
		$sSQL = "SELECT cse_ID,cse_Type,cse_Name FROM tbcontentsection
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbcontentsection.mnu_ID
		WHERE tbmenu.mnu_ID = $nMenuID AND tbmenu.man_ID = ".page::mandant();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			if (!($nNoMail == 1 && $row['cse_Type'] == contentView::TYPE_FORM)) {
				$out .= '<option value="'.$row['cse_ID'].'">'.$row['cse_Name'].' '.$this->getTypeDesc($row['cse_Type']).'</option>';
			}
		}
		// Header und Daten ausgeben
		header('Content-type: text/html; charset=ISO-8859-1');
		echo $out.'</xml>';
	}
	
	/**
	 * Gibt die Beschreibung eines Contenttyps zurück
	 * @param int nType, Typ anhand der contentView Konstanten
	 * @return string Beschreibender String
	 */
	private function getTypeDesc($nType) {
		$nType = getInt($nType);
		$sDesc = '';
		switch($nType) {
			case contentView::TYPE_CONTENT:
				$sDesc = '('.$this->Res->html(825,page::language()).')';
				break;
			case contentView::TYPE_FORM:
				$sDesc = '('.$this->Res->html(827,page::language()).')';
				break;
			case contentView::TYPE_MEDIA:
				$sDesc = '('.$this->Res->html(826,page::language()).')';
				break;
		}
		return($sDesc);
	}
}