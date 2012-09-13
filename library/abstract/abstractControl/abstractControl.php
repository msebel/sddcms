<?php 
/**
 * baseControl, HTML Control Basisklasse.
 * Abstrakte Klasse, welche Grundfunktionen für
 * multi-dimensionale HTML Controls bietet.
 * @author Michael Sebel <michael@sebel.ch>
 * @abstract
 */
abstract class abstractControl {
	
	/**
	 * Array aller HTML Controls.
	 * Man kann auch ein anderes Array oder andere algorithmen 
	 * verwenden um HTML Controls zu dimensionieren.
	 * @var array
	 */
	protected $Controls = array();
	
	/**
	 * Konstruktor, ist nicht überschreibbar.
	 * @final
	 */
	final function __construct() {}
	
	/**
	 * Ladet spezielle Objekte, wie etwa Conn oder Res.
	 * @abstract
	 */
	abstract function loadObjects();
	/**
	 * Ladet das Metadaten Objekt
	 * @param meta Meta, das Metadaten Objekt (Referenz)
	 * @abstract
	 */
	abstract function loadMeta($Meta);
	/**
	 * Gibt ein HTML Control anhand des Namens aus
	 * @param string Name, Name des HMTL Controls
	 * @abstract
	 */
	abstract function get($Name);
	
	/**
	 * Alle Controls entfernen, indem das lokale Array neu instanziert wird
	 */
	public function removeAll() {
		$this->Controls = array();
	}
	
	/**
	 * Objekt entfernen anhand seiner ID
	 * @param integer ID, Identifikation des Controls
	 */
	public function removeAt($ID) {
		$NewControls = array();
		$Iterator = 0;
		foreach ($this->Controls as $Control) {
			if ($Iterator != $ID) {
				array_push($NewControls,$Control);
			}
			$Iterator++;
		}
		$this->Controls = $NewControls;
	}
	
	/**
	 * Anhand des Namen entfernen
	 * @param string Name, Name des zu entfernenden Objektes
	 */
	public function removeByName($Name) {
		$this->remoteAt($this->getIdByName($Name));
	}
	
	/**
	 * Herausfinden, ob ein Control existiert
	 * @param string Name, Name des zu suchenden Controls
	 * @return boolean True, wenn das Control gefunden wird
	 */
	public function exists($Name) {
		$Exists = false;
		foreach ($this->Controls as $Control) {
			if ($Control['Name'] == $Name) {
				$Exists = true; break;
			}
		}
		return($Exists);
	}
	
	/**
	 * Index eines Controls anhand des Namen finden
	 * @param string Name, Name des zu suchenden Controls
	 * @return integer ID des gesuchten Objekts oder -1 wenn nicht gefunden
	 */
	public function getIdByName($Name) {
		$Iterator = 0;
		$nReturnID = -1;
		foreach ($this->Controls as $Control) {
			if ($Control['Name'] == $Name) {
				$nReturnID = $Iterator;
			}
			$Iterator++;
		}
		return($nReturnID);
	}
}