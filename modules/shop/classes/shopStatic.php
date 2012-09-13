<?php
/**
 * Statische Funktionen des Shops
 * @author Michael Sebel <michael@sebel.ch
 */
class shopStatic {

	/**
	 * Gibt Pfad zu Standard oder individuellem Template
	 * @param string $name Name des gewünschten Templates
	 * @return string Pfad zum individuellen Template, oder Standard
	 */
	public static function getTemplate($name) {
		// Erst im Templatepfad schauen
		$sPath = shopConfig::Templates().$name.'.html';
		// Wenn nicht vorhanden, Standard nehmen
		if (!file_exists($sPath)) {
			$sPath = BP.shopModuleConfig::TEMPLATE_PATH.$name.'.html';
		}
		return($sPath);
	}

	/**
	 * Gibt Pfad zu Standard oder individuellem Template (Mails)
	 * @param string $name Name des gewünschten Templates
	 * @return string Pfad zum individuellen Template, oder Standard
	 */
	public static function getMailTemplate($name) {
		// Erst im Templatepfad schauen
		$sPath = shopConfig::Mails().$name.'.html';
		// Wenn nicht vorhanden, Standard nehmen
		if (!file_exists($sPath)) {
			$sPath = BP.shopModuleConfig::MAIL_PATH.$name.'.html';
		}
		return($sPath);
	}

	/**
	 * Elementpfad anhand einer Element ID zurückgeben
	 * @param int $nEleID ID eines Elements
	 */
	public static function getElementPath($nEleID) {
		$Conn = database::getConnection();
		$sSQL = 'SELECT ele_File FROM tbelement WHERE ele_ID = '.$nEleID;
		$sFile = $Conn->getFirstResult($sSQL);
		// Pfad erstellen
		$sPath = self::getElementFolder($nEleID);
		// Pfad so zurückgeben
		return($sPath.$sFile);
	}

	/**
	 * Elementordner anhand einer Element ID zurückgeben
	 * @param int $nEleID ID eines Elements
	 */
	public static function getElementFolder($nEleID) {
		// Pfad erstellen
		$sPath = shopModuleConfig::ELEMENT_PATH;
		$sPath = str_replace('{PAGE}', page::id(), $sPath);
		$sPath = str_replace('{ELEMENT}', $nEleID, $sPath);
		// Pfad so zurückgeben
		return($sPath);
	}

	/**
	 * Erstellt in der zugehörigen Variable das Dropdown sowie den
	 * JS Code um die Artikelgrösse zu wählen. Es erscheint nichts,
	 * wenn der Artikel keine Grössen hat
	 */
	public static function addSizesControl(shopArticle $Article) {
		$sBasename = 'ArtEntryPrice-'.$Article->getShaID().'_';
		$sHtml = '';
		// Nur etwas machen, wenn der Artikel Grössen anbietet
		if ($Article->hasSizes()) {
			$sHtml = '<select id="'.$sBasename.'Select"  class="cArticleSizeSelect">';
			// Optionen abbilden
			foreach ($Article->getSizesArray() as $size) {
				$sSelected = checkDropDown(1, $size->getPrimary());
				$Data = str_replace('"', "##", json_encode(array(
					'Price' => $size->getPriceadd(),
					'ID' => $size->getSazID()
				)));
				$sPrice = numericOps::getDecimal(
					$size->getPriceadd() + $Article->getCurrentPrice(), 2
				);
				$sHtml .= '
					<option value="'.$Data.'"'.$sSelected.'>
						'.$size->getValue().' ('.$sPrice.' CHF)
					</option>
				';
			}
			// Select am Ende schliessen
			$sHtml .= '</select>';
		} else {
			// Als Fallback, Feld für leeres JSON Objekt mit selbem Namen
			$sHtml = '<input type="hidden" value="{}" id="'.$sBasename.'Select" class="cArticleSizeSelect">';
		}
		// Immer ein Hidden Feldmit der Grösse
		$sHtml .= '<input type="hidden" id="'.$sBasename.'Size">';
		// In die Liste einfüllen
		return($sHtml);
	}

	/**
	 * Ein Artikelset nehmen und den aktuellen Artikel darin suchen
	 * Preis und Anzahl mergen, wenn sha_ID und soa_Size gleich sind
	 * @param array $set Set von Artikeln (Kann auch leer sein)
	 * @param array $article Artikel Daten mit min. sha_ID, soa_Size
	 */
	public static function mergeArticles(&$set,$row) {
		// Das ganze Set durchgehen
		for ($i = 0;$i < count($set);$i++) {
			// Ist der aktuelle Artikel gleich?
			if ($set[$i]['sha_ID'] == $row['sha_ID'] && $set[$i]['soa_Size'] == $row['soa_Size']) {
				// Artikelzähler nach oben
				$set[$i]['soa_Times']++;
				// Preis hinzurechnen
				$set[$i]['soa_Price'] += $row['soa_Price'];
				// Gefunden, daher true zurückgeben und beenden
				return(true);
			}
		}
		// Wenn wir durch die schlaufe kommen, wurde nicht gemergt
		return(false);
	}

	/**
	 * Gibt den eingeloggten User anhand der ImpersonationSecurity zurück
	 * oder NULL, wenn niemand eingeloggt ist
	 * @return shopUser Instanz eines Shopusers, oder NULL
	 */
	public static function getLoginUser() {
		$user = NULL;
		$Conn = singleton::conn();
		$sSecurity = $_SESSION['SessionConfig'][shopConfig::LoginMenuID().'_ImpersonationSecurity'];
		$nImpID = impersonation::getIdBySecurity($sSecurity, $Conn);
		// Recordset anhand der Impersonation laden
		$sSQL = 'SELECT shu_ID,tbimpersonation.man_ID,tbimpersonation.imp_ID,
		shu_Billable,shu_Condition,shu_Active FROM tbshopuser INNER JOIN tbimpersonation
		ON tbimpersonation.imp_ID = tbshopuser.imp_ID
		WHERE tbimpersonation.man_ID = '.page::mandant().'
		AND tbimpersonation.imp_ID = '.$nImpID.'
		AND tbimpersonation.imp_Active = 1';
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) {
			$user = new shopUser();
			$user->loadRow($row);
		}
		// User oder Null zurückliefern
		return($user);
	}

	/**
	 * Gibt an, ob der aktuelle User eingeloggt ist
	 * @return bool true/false wenn Login oder nicht
	 */
	public static function isUserLogin() {
		return($_SESSION['SessionConfig'][shopConfig::LoginMenuID().'_ImpersonationSecurity']);
	}

	/**
	 * Erstellt ein Rekursives Dropdown aller Artikelgruppen und gibt
	 * den entsprechenden HTML Code zurück. Registriert direkt eine Javascript
	 * Funktion im js/system.js um den Seitenaufruf "onChange" auszulösen
	 * @return string HTML und etwas JS
	 */
	public static function getGroupSearchDropdown() {
		// Objekte laden
		$Res = singleton::resources();
		$Conn = singleton::conn();
		$Groups = array();
		// Alle Gruppen laden und lokale zwischenspeichern
		$sSQL = 'SELECT sag_ID,sag_Parent,sag_Title FROM
		tbshoparticlegroup WHERE man_ID = '.page::mandant().'
		ORDER BY sag_Title ASC';
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) {
			array_push($Groups,$row);
		}
		// Rekursives Array aus den Gruppen erstellen
		$sOptions = '';
		shopStatic::getGroupsRecursive($sOptions,$Groups,0,'');
		// Select erstellen mit vorgebruzelten Optionen
		$sHtml = '
		<select name="g" id="groupSearch" class="cStartGroupField">
			<option value="0">'.$Res->html(1150, page::language()).'</option>
			'.$sOptions.'
		</select>
		';
		// Javascript Event registrieren
		$sHtml .= '
		<script type="text/javascript">
			$("groupSearch").observe("change",function() {
				this.form.submit();
			});
		</script>
		';
		// HTML Code zurückgeben
		return($sHtml);
	}

	/**
	 * Rekursiv die Artikelgruppen als verschachtelte Optionen ausgeben
	 * @param string $sOptions HTML Code für die Options
	 * @param array $Groups Gesamtarray der Gruppen
	 * @param int $nSearchID Gesucht wird diese ID als Parent
	 * @param string $sPrefix Prefix für den Optionstitel
	 */
	public static function getGroupsRecursive(&$sOptions,&$Groups,$nSearchID,$sPrefix) {
		// Alle Objekte mit der Gesuchten ID als Parent suchen
		foreach ($Groups as $Group) {
			if ($Group['sag_Parent'] == $nSearchID) {
				// Option erstellen
				$sOptions .= '
				<option value="'.$Group['sag_ID'].'">'.$sPrefix.' '.$Group['sag_Title'].'</option>';
				// Rekursiv Subgruppen suchen
				shopStatic::getGroupsRecursive($sOptions,$Groups,$Group['sag_ID'],'--'.$sPrefix);
			}
		}
	}

  /**
   * Gibt einen Backlinks aus, wenn der Referer von der aktuellen Domain ist, nimmt es den
   * Referer als zurück Link ansonsten die Startseite
   */
  public static function getBackLink() {
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    $host = $_SERVER['HTTP_HOST'];
    debug($referer);
    debug($host);
    if ($referer['host'] == $host) {
      $link = $_SERVER['HTTP_REFERER'];
    } else {
      if (strtolower($_SERVER['HTTPS']) == 'on') {
        $link = 'https://'.$host.'/';
      } else {
        $link = 'http://'.$host.'/';
      }
    }
    // HTML Code und übersetzter Link
    $Res = singleton::resources();
    return('<a href="'.$link.'" class="shop-back-link">'.$Res->html(37,page::language()).'</a>');
  }
}