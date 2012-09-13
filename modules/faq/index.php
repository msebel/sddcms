<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/faq') !== false) {
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: /controller.php?id='.$_GET['id']);
	exit;
}

/**
 * Viewmodul für das FAQ Modul
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewCmsFaq extends abstractSddView {

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		$out = '';
		// Javascript einfügen
		singleton::meta()->addJavascript('/modules/faq/index.js',true);

		// Konfiguration laden
		$Config = array();
		pageConfig::get(page::menuID(),$this->Conn,$Config);

		// Header erstellen
		if (strlen($Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($Config['htmlCode']['Value']);
			$out .= '<div class="divEntryText">'.$Config['htmlCode']['Value'].'</div>';
		}

		// Datensatz holen und Anzeigen
		$sSQL = "SELECT tbfaqentry.faq_Question AS QuestionContent,tbcontent.con_Content AS AnswerContent
		FROM tbfaqentry INNER JOIN tbcontent ON tbcontent.con_ID = tbfaqentry.faq_Answer
		WHERE tbfaqentry.mnu_ID = ".page::menuID()." AND tbfaqentry.faq_Active = 1 ORDER BY tbfaqentry.faq_Sortorder ASC";

		$nRes = $this->Conn->execute($sSQL);
		$nCount = 0;
		while ($row = $this->Conn->next($nRes)) {
			if ($Config['displayNumeration']['Value'] == 1 || $Config['showUnexpanded']['Value'] == 1) {
				$nCount++;
			}
			$this->displayRow($out,$row,$nCount,$Config);
		}
		return($out);
	}

	// Datensatz in output buffer schreiben
	private function displayRow(&$out,&$row,$nCount,&$Config) {
		stringOps::htmlViewEnt($row['AnswerContent']);
		stringOps::noHtml($row['QuestionContent']);
		stringOps::htmlViewEnt($row['QuestionContent']);
		// Antwort schreiben, wenn nicht vorhanden
		if ($this->isDisposeable($row['AnswerContent'])) {
			$row['AnswerContent'] = $this->Res->html(455,page::language());
		}
		// <p> Tags erstellen wenn nicht vorhanden
		if (substr($row['AnswerContent'],0,3) != '<p>') {
			$row['AnswerContent'] = '<p>'.$row['AnswerContent'].'</p>';
		}
		// Zahl darstellen
		$sChar = '';
		if ($nCount > 0) $sChar = $nCount.'. ';
		// Plus Div erstellen, wenn nötig
		if ($Config['showUnexpanded']['Value'] == 1) {
			$out .= '
			<div class="faqExpandIcon" id="img'.$nCount.'"
			onmouseover="mpHand(this);" onmouseout="mpDefault(this);"
			onclick="ToggleFaqView('.$nCount.');"></div>
			';
		}
		// Output erstellen
		$out .= '
		<div class="faqTitle">
			<strong>'.$sChar.$row['QuestionContent'].'</strong>
		</div>
		';
		// Umschliessendes ausklapp Div
		if ($Config['showUnexpanded']['Value'] == 1) {
			$out .= '<div id="faq'.$nCount.'" style="display:none;">';
		}
		$out .= '<div class="faqEntry">'.$row['AnswerContent'].'</div>
			<div class="lineDivider"></div>
		';
		if ($Config['showUnexpanded']['Value'] == 1) {
			$out .= '</div>';
		}
	}

	// Angeben, ob der Faq Eintrag brauchbar ist
	private function isDisposeable($String) {
		$bDispose = false;
		// Wenn 0 Zeichen
		if (strlen($String) == 0) {
			$bDispose = true;
		} else {
			if ($String == '<p></p>') {
				$bDispose = true;
			}
		}
		return($bDispose);
	}

	// Prüft auf Access und leitet auf Fehlerseite weiter
	private function checkAccessRedirect($nFaqID) {
		if (!$this->checkAccess($nFaqID)) {
			header('location /error.php?type=noAccess');
			exit();
		}
	}

	// Prüfen ob mnu_ID und faq_ID passen
	private function checkAccess($nFaqID) {
		$bState = false;
		$sSQL = "SELECT COUNT(faq_ID) FROM tbfaqentry
		WHERE mnu_ID = ".page::menuID()." AND faq_ID = $nFaqID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn ein Resultat, Zugriff gew�hrt
		if ($nResult == 1) $bState = true;
		return($bState);
	}
}