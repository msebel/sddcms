<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/news') !== false) {
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: /controller.php?id='.$_GET['id']);
	exit;
}

/**
 * Viewmodul für das Content Modul
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewCmsNews extends abstractSddView {

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		if (isset($_GET['news'])) {
			return($this->getNews());
		} else {
			return($this->getMain());
		}
	}

	// Hauptseite für die Newsübersicht
	private function getMain() {
		// Konfiguration holen
		$out = '';
		$NewsConfig = array();
		pageConfig::get(page::menuID(),$this->Conn,$NewsConfig);
		if (strlen($NewsConfig['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($NewsConfig['htmlCode']['Value']);
			$out .= '<div class="divEntryText">'.$NewsConfig['htmlCode']['Value'].'</div>';
		}

		// Datum
		$now = dateOps::getTime(dateOps::SQL_DATETIME);
		// Alle Content Sektionen holen die Aktiv sind
		$sSQL = "SELECT tbcontent.con_ID,tbcontent.con_Modified,tbcontent.con_ShowDate,
		tbcontent.con_Title,tbuser.usr_Name,tbcontent.con_Content,
		IFNULL(tbcontent.con_DateFrom,tbcontent.con_Date) AS con_ViewDate
		FROM tbcontent LEFT JOIN tbuser ON tbuser.usr_ID = tbcontent.usr_ID
		WHERE tbcontent.mnu_ID = ".page::menuID()."
		AND IFNULL(tbcontent.con_DateTo,NOW()) >= NOW()
		AND IFNULL(tbcontent.con_DateFrom,tbcontent.con_Date) <= NOW()
		AND tbcontent.con_Active = 1 ORDER BY con_ViewDate DESC";
		// Paging aktivieren
		$PagingEngine = new paging($this->Conn,$this->link());
		$PagingEngine->start($sSQL,$NewsConfig['postsPerPage']['Value']);
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		$out .= $PagingEngine->getHtml();
		// News anzeigen
		while ($row = $this->Conn->next($nRes)) {
			contentView::getNews(
				$row,
				$out,
				$this->Res,
				$NewsConfig
			);
		}
		$out .= $PagingEngine->getHtml();

		// System abschliessen
		stringOps::htmlViewEnt($out);
		return($out);
	}

	// Subseite für komplette Newsanzeiges
	private function getNews() {
		// Konfiguration holen
		$out = '';
		$NewsConfig = array();
		pageConfig::get(page::menuID(),$this->Conn,$NewsConfig);

		$nContentID = getInt($_GET['news']);
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE con_ID = $nContentID AND mnu_ID = ".page::menuID();
		$nResult = $this->Conn->getCountResult($sSQL);

		if ($nResult != 1) {
			redirect('location: /error.php?type=noAccess');
		}

		contentView::getContentElement($nContentID,$out,$this->Conn);
		stringOps::htmlViewEnt($out);

		// Social Bookmarks anzeigen, wenn konfiguriert
		if ($NewsConfig['socialBookmarking']['Value'] == 1) {
			$bm = socialButtons::blog($Res);
			$bm->setTitle(singleton::currentmenu()->Name.' '.page::title());
			$bm->setUrl(stringOps::currentUrl());
			$out .= '
			<div class="cSocialButtonList">
				'.$bm->output().'
			</div>
			';
		}

		// Backlink
		$out .= '
		<br>
		<p>
			<a class="cMoreLink" href="'.singleton::currentmenu()->getLink().'">'.$this->Res->html(37,page::language()).'</a>
		</p>
		';
		return($out);
	}
}