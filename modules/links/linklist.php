<?php
// Basislinkliste
abstract class linklist {
	
	public $Conn;		// DB Verbindung
	public $Res;		// Sprachressourcen
	public $Data;		// Recordset
	
	// Erster und einziger Konstruktor
	final public function __construct(dbConn &$Conn,resources &$Res) {
		$this->Conn = $Conn;
		$this->Res = $Res;
		$this->loadLinks();
	}
	
	// Linkdaten in korrekter Reihenfolge laden
	final private function loadLinks() {
		$sSQL = "SELECT lnk_ID,lnk_Clicks,lnk_Name,lnk_Target,
		lnk_URL,lnk_Desc,tblink.lnc_ID,lnc_Title FROM tblink
		LEFT JOIN tblinkcategory ON tblinkcategory.lnc_ID = tblink.lnc_ID
		WHERE lnk_Active = 1 AND tblink.mnu_ID = ".page::menuID()."
		ORDER BY lnc_Order ASC,lnk_Sortorder ASC";
		$this->Data = array();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			stringOps::htmlViewEnt($row['lnk_Desc']);
			stringOps::htmlViewEnt($row['lnk_Name']);
			$row['lnc_ID'] = getInt($row['lnc_ID']);
			array_push($this->Data,$row);
		}
	}
	
	final public function getLink(&$row) {
		$File = 'out.php?id='.page::menuID().'&link='.$row['lnk_ID'];
		$File = ' href="'.$File.'"';
		$Title = ' title="'.$row['lnk_Desc'].'"';
		$Target = '';
		if (strlen($row['lnk_Target']) > 0) {
			$Target = ' target="'.$row['lnk_Target'].'"';
		}
		$Link = '<a'.$Target.$Title.$File.'>'.$row['lnk_Name'].'</a>&nbsp;';
		return($Link);
	}
	
	abstract public function appendHtml(&$out);
}