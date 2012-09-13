<?php
/**
 * Gibt Javascript Ressourcen aus.
 * @author Michael Sebel <michael@sebel.ch>
 */
class ResJavascript extends baseRequest {
	
	/**
	 * Gibt den angeforderten String in der aktuellen oder
	 * der im Request definierten Sprache aus.
	 */
	public function output() {
		$nLanguage = page::language();
		$nID = getInt($_POST['resource']);
		// Sprache holen wenn definiert
		if (isset($_POST['language'])) {
			$nLanguage = getInt($_POST['language']);
		}
		// Resourcen Request absetzen an Datenbank
		$resource = $this->Res->normal($nID,$nLanguage);
		header('Content-type: text/html; charset=ISO-8859-1');
		echo $resource;
	}
}