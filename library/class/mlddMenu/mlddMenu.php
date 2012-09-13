<?php
/**
 * Diese Klasse stellt grundlegende Menufunktionen.
 * Wird verwendet zum darstellen und verarbeiten des Menus.
 * Eine Instanz $Menu wird automatisch erstellt und ist
 * im globalen Scope jederzeit verfügbar.
 * Dieses Menu bietet Javascript Dropdowns.
 * @author Michael Sebel <michael@sebel.ch>
 */
class mlddMenu extends listMenu {
	
	/**
	 * Das Menu als HTML laden.
	 * Hier werden auch die Optionen für individuelle menu-HTML-Daten geladen
	 * @return string HTML Code mit dem Menu drin
	 */
	public function getMenu() {
		$out = '';
		$nLastLevel = 1;
		// Menu Rekursiv laden
		$this->getMenuRecursive('0',$out,'class');
		// Menuanfang ersetzen
		$startlist = '<ul class="mlddm" params="1,-1,500,slide,200,h">';
		$out = $startlist.substr($out,4);
		// Wenn eingeloggt, logout link zeigen (letztes ul ersetzen)
		if ($this->Access->isLogin() == true) {
			$out = substr($out,0,strlen($out)-5).'<li><a href="/?logout">Logout</a></li></ul>';
		}
		return($out);
	}
}