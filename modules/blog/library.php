<?php 
class blogEntryView {
	
	public static $Conn;
	public static $Res;
	
	// Kurzen Beitrag anzeigen
	public static function showEntryShort(&$Data,&$out,$nBlogID) {
		// Formatierungen entfernen aus Content
		stringOps::noHtml($Data['con_Content']);
		if (strlen($Data['con_Content']) > 500) {
			$Data['con_Content'] = substr($Data['con_Content'],0,500);
		}
		$Title = self::getTitle($Data);
		stringOps::htmlViewEnt($Title);
		stringOps::htmlViewEnt($Data['con_Content']);
		// Ausgeben mit beschränktem Inhalt und "mehr" Link
		$out .= '
		<div class="newsHead">
			'.$Title.'
		</div>
		<div class="newsContent">
			<p>'.$Data['con_Content'].'...
			<a class="cMoreLink" href="entry.php?id='.page::menuID().'&blog='.$nBlogID.'&entry='.$Data['con_ID'].'">'.self::$Res->html(442,page::language()).'</a></p>
		</div>
		<div class="cDivider"></div>
		';
	}
	
	// Langen Blog Beitrag anzeigen
	public static function showEntryLong(&$Data,&$out) {
		$Title = self::getTitle($Data);
		stringOps::htmlViewEnt($Title);
		stringOps::htmlViewEnt($Data['con_Content']);
		// Ausgeben des kompletten Beitrages
		$out .= '
		<div class="newsHead">
			'.$Title.'
		</div>
		<div class="newsContent">
			'.$Data['con_Content'].'
		</div>
		<div class="cDivider"></div>
		';
	}
	
	// Alle Kommentare anzeigen
	public static function showComments(&$out,$nConID,$nBlogID) {
		// Daten generieren
		$Comments = array();
		$PostLimit = 20;
		$Limitation = "";
		if (!isset($_GET['showAll'])) {
			$Limitation = "LIMIT 0,$PostLimit";
		}
		$sSQL = "SELECT com_Name,com_Time,com_Content FROM tbkommentar
		WHERE owner_ID = $nConID ORDER BY com_Time DESC $Limitation";
		$nRes = self::$Conn->execute($sSQL);
		while ($row = self::$Conn->next($nRes)) {
			array_push($Comments,$row);
		}
		// TabRowExtender für Kommentare
		$GBTabRow = new tabRowExtender('forumRowOdd','forumRowEven');
		$out .= '<table width="100%" cellpadding="5" cellspacing="1" class="forumTable">';
		foreach ($Comments as $Post) {
			// Zeilenklasse herausfinden
			$sClass = $GBTabRow->getSpecial();
			// Zeit und Datum herausfinden
			$nStamp = dateOps::getStamp(dateOps::SQL_DATETIME,$Post['com_Time']);
			$sDate = dateOps::getTime(dateOps::EU_DATE,$nStamp);
			$nTime = dateOps::getTime(dateOps::EU_CLOCK,$nStamp);
			// Texte formatierten
			$Post['com_Name'] = stringOps::chopString($Post['com_Name'],20,true);
			stringOps::htmlViewEnt($Post['com_Name']);
			stringOps::htmlViewEnt($Post['com_Content']);
			// Post anzeigen
			$out .= '
			<tr class="'.$sClass.'">
				<td width="20%" valign="top">
					'.self::$Res->html(326,page::language()).' 
					'.$Post['com_Name'].'<br>
					<br>
					<em>
					'.$sDate.' <br>
					'.self::$Res->html(327,page::language()).'
					'.$nTime.'
					</em>
					<br>
				</td>
				<td valign="top">
					'.$Post['com_Content'].'
				</td>
			</tr>
			';
		}
		$out .= '</table>';
		// Link um alle anzuzeigen, wenn überhaupt nötig
		$sSQL = "SELECT COUNT(com_ID) FROM tbkommentar WHERE owner_ID = $nConID";
		$nRecords = self::$Conn->getCountResult($sSQL);
		if ($nRecords > $PostLimit && !isset($_GET['showAll'])) {
			$out .= '
			<div style="float:right;">
				<p>
					<a href="entry.php?id='.page::menuID().'&blog='.$nBlogID.'&entry='.$nConID.'&showAll">'.self::$Res->html(658,page::language()).'</a>
				</p>
			</div>
			';
		}
	}
	
	// Prüfen ob Blogeintrag existiert
	public static function checkBlogEntry($nBlogID,$nConID) {
		$Valid = false;
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE mnu_ID = $nBlogID AND con_ID = $nConID";
		$nResult = self::$Conn->getCountResult($sSQL);
		if ($nResult == 1) $Valid = true;
		return($Valid);
	}
	
	// Titel für Anzeige bearbeiten
	private static function getTitle(&$Data) {
		$sNewsHead = '<h2>'.$Data['con_Title'].'</h2>';
		if ($Data['con_ShowDate'] == 1 || $Data['con_ShowName'] == 1) {
			$bPrintedName = false;
			// Erfassennamen anzeigen
			if ($Data['con_ShowName'] == 1) {
				$bPrintedName = true;
				$sNewsHead.= self::$Res->html(646,page::language()).' ';
				if ($Data['usr_Name'] != NULL) {
					$sNewsHead.= $Data['usr_Name'];
				} else {
					$sNewsHead.= self::$Res->html(649,page::language());
				}
			}
			// Datum anzeigen
			if ($Data['con_ShowDate'] == 1) {
				if ($bPrintedName) $sNewsHead .= ', ';
				$sNewsHead .= self::$Res->html(647,page::language()).' ';
				$sNewsHead .= dateOps::convertDate(
					dateOps::SQL_DATETIME,
					dateOps::EU_DATE,
					$Data['con_Date']
				).' ';
				$sNewsHead .= self::$Res->html(648,page::language()).' ';
				$sNewsHead .= dateOps::convertDate(
					dateOps::SQL_DATETIME,
					dateOps::EU_CLOCK,
					$Data['con_Date']
				).' ';
				$sNewsHead .= self::$Res->html(581,page::language());
			}
		}
		return($sNewsHead);
	}
}