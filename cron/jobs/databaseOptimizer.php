<?php
class databaseOptimizer extends cmsSchedule {
	
	// Ausführungsfunktion Überschreiben
	public function execute() {
		// Instanzdatenbank optimieren
		$sSQL = "OPTIMIZE TABLE `tbaccess`,`tbaddress`,`tbblogcategory`,
		`tbblogcategory_content`,`tbcontent`,`tbcontentsection`,`tbcountdown`,
		`tbdirectlink`,`tbdomain`,`tbelement`,`tbfaqentry`,`tbformfield`,`tbgallery`,
		`tbimpersonation`,`tbkalender`,`tbkalender_contentsection`,`tbkeyword`,
		`tbkommentar`,`tbkonfig`,`tblink`,`tblocation`,`tblogging`,`tbmandant`,
		`tbmap`,`tbmap_menu`,`tbmenu`,`tbmenutyp`,`tbmenu_contentsection`,
		`tbmenu_impersonation`,`tboptions`,`tbowner`,`tbowner_wiki`,`tbpage`,
		`tbresource`,`tbroute`,`tbroute_location`,`tbteaser`,`tbteaserentry`,
		`tbteaserkonfig`,`tbteasersection`,`tbteasersection_teaser`,`tbteasertyp`,
		`tbuser`,`tbuseraccess`,`tbusergroup`,`tbuser_usergroup`,`tbwiki`,`tbwikientry`";
		$this->Conn->setInstanceDB();
		$this->Conn->command($sSQL);
		// Globale Datenbank optimieren
		$sSQL = "OPTIMIZE TABLE `tbcron`,`tbmenutyp`,`tboptions`,`tbresource`,`tbteasertyp`";
		$this->Conn->setGlobalDB();
		$this->Conn->command($sSQL);
		$this->Conn->setInstanceDB();
	}
}