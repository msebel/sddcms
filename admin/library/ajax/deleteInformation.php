<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/admin/library/library.php');
$tpl->setEmpty();

$Module = new moduleFilelibrary();
$Module->loadObjects($Conn,$Res);
// Zugriff auf Bibliothek prüfen und initialisieren
$Module->controlAccess($Access);
$Module->initialize();

$sRoot = $Module->Options->get('rootFolder');
$sCurr = $Module->Options->get('currentFolder');
$sFile = $_GET['file'];
// Nur alphanumerische Zeichen im Filenamen
stringOps::alphaNumFiles($sFile);
$sComp = $sRoot.$sCurr.$sFile;
$sFileDisp = stringOps::chopString($sFile,22,true);

if (file_exists($sComp) && strlen($sFile) > 0) {
	// Meldung je nach Dateiart (dir/file)
	switch (filetype($sComp)) {
		case 'dir':
			$nFolders = 0;
			$nFiles = 0;
			// Lesen wie viele Files/Ordner sich im Ordner befinden
			if ($resDir = opendir($sComp)) {
		        while (($sReadFile = readdir($resDir)) !== false) {
		        	// Auf File prüfen
		        	if (filetype($sComp .'/'. $sReadFile) == 'file') {
			        	$nFiles++;
		        	}
		        	// Auf Ordner prüfen, ohne . und ..
			        if (filetype($sComp .'/'. $sReadFile) == 'dir') {
		        		if ($sReadFile != '.' && $sReadFile != '..') {
			        		$nFolders++;
			        	}
		        	}
		        }
		    }
		    // Ordner schliessen
	    	closedir($resDir);
			// Nachricht genererieren
			if ($nFolders > 0 && $nFiles > 0) {
				// Dateien und Ordner vorhanden
				echo $Res->html(707,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(708,page::language());
				echo ' <strong>'.$nFolders.'</strong> ';
				echo $Res->html(709,page::language());
				echo ' <strong>'.$nFiles.'</strong> ';
				echo $Res->html(710,page::language()).'. ';
				echo $Res->html(706,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(704,page::language()).'? ';
			} elseif ($nFolders > 0 && $nFiles == 0) {
				// Nur Ordner im Ordner
				echo $Res->html(707,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(708,page::language());
				echo ' <strong>'.$nFolders.'</strong> ';
				echo $Res->html(711,page::language()).'. ';
				echo $Res->html(706,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(704,page::language()).'? ';
			} elseif ($nFolders == 0 && $nFiles > 0) {
				// Nur noch Dateien im Ordner
				echo $Res->html(707,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(708,page::language());
				echo ' <strong>'.$nFiles.'</strong> ';
				echo $Res->html(710,page::language()).'. ';
				echo $Res->html(706,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(704,page::language()).'? ';
			} else {
				// Keine Ordner und Dateien mehr
				echo $Res->html(707,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(712,page::language()).'. ';
				echo $Res->html(706,page::language());
				echo ' \'<strong>'.$sFile.'</strong>\' ';
				echo $Res->html(704,page::language()).'? ';
			}
			break;
		case 'file':
			// In alle Contents, Teaser / Configs durchsuchen
			$sSQL = '
			SELECT tbcontent.con_Content AS ContentEntry FROM tbcontent
			INNER JOIN tbmenu ON tbmenu.mnu_ID = tbcontent.mnu_ID
			WHERE tbmenu.man_ID = '.page::mandant().' AND tbmenu.mnu_Active = 1 
			AND tbcontent.con_Active = 1 AND tbcontent.con_Content IS NOT NULL
			UNION ALL SELECT tbkonfig.cfg_Text AS ContentEntry FROM tbkonfig
			INNER JOIN tbmenu ON tbmenu.mnu_ID = tbkonfig.mnu_ID
			WHERE tbmenu.man_ID = '.page::mandant().' AND tbmenu.mnu_Active = 1
			AND tbkonfig.cfg_Text IS NOT NULL
			UNION ALL SELECT tbteaserentry.ten_Content AS ContentEntry FROM tbteaserentry
			INNER JOIN tbteaser ON tbteaser.tap_ID = tbteaserentry.tap_ID
			INNER JOIN tbteasersection_teaser ON tbteasersection_teaser.tap_ID = tbteaser.tap_ID
			WHERE tbteaser.man_ID = '.page::mandant().' AND tbteasersection_teaser.tsa_Active = 1
			AND tbteaserentry.ten_Content IS NOT NULL
			UNION ALL SELECT tbteaserkonfig.cfg_Text AS ContentEntry FROM tbteaserkonfig
			INNER JOIN tbteaser ON tbteaser.tap_ID = tbteaserkonfig.tap_ID
			INNER JOIN tbteasersection_teaser ON tbteasersection_teaser.tap_ID = tbteaser.tap_ID
			WHERE tbteaser.man_ID = '.page::mandant().' AND tbteasersection_teaser.tsa_Active = 1
			AND tbteaserkonfig.cfg_Text IS NOT NULL
			';
			$nCount = 0;
			$nRes = $Conn->execute($sSQL);
			// Vorkommnisse suchen
			$sCompLink = 'library.php?file='.str_replace(BP.'/page/'.page::id().'/library/','',$sComp);
			$sComp = str_replace(BP,'',$sComp);
			while ($row = $Conn->next($nRes)) {
				if (strstr($row['ContentEntry'],$sComp) !== false) {
					$nCount++;
				}
				if (strstr($row['ContentEntry'],$sCompLink) !== false) {
					$nCount++;
				}
			}
			// Meldung erstellen
			if ($nCount > 0) {
				echo $Res->html(701,page::language());
				echo ' '.$nCount.' ';
				echo $Res->html(702,page::language()).'. ';
				echo $Res->html(703,page::language());
				echo ' \'<strong>'.$sFileDisp.'</strong>\' ';
				echo $Res->html(704,page::language()).'? ';
			} else {
				echo $Res->html(705,page::language()).'. ';
				echo $Res->html(703,page::language());
				echo ' \'<strong>'.$sFileDisp.'</strong>\' ';
				echo $Res->html(704,page::language()).'? ';
			}
			break;
		default:
			echo $Res->html(700,page::language()).'.';
	}
} else {
	// Keine Datei/Ordner angewählt
	echo $Res->html(700,page::language()).'.';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');