<?php
class moduleCache extends commonModule {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}

	// Speichert die Caching Einstellungen
	public function saveConfig() {
		$active = getInt($_POST['activateCache']);
		// Wenn sich etwas geändert hat, jenachdem reagieren
		if ($active != option::get('caching')) {
			// Caching wurde aktiviert
			if ($active == 1) {
				mkdir(BP.'/mandant/'.page::mandant().'/cache',0777);
			}
			// Caching wurde deaktiviert
			if ($active == 0) {
				fileOps::deleteFolder(BP.'/mandant/'.page::mandant().'/cache');
			}
		}
		// Option speichern
		option::set('caching',$active);
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/cache/index.php?id='.page::menuID());
	}
}