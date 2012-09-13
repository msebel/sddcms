<?php
/**
 *
 */
class abstractRowGenerator {

	/**
	 * Name der Tabelle aus der Code generiert werden soll
	 * @var string
	 */
	private $Table = '';
	/**
	 * Klassenname definieren (Per Default wird Table eingefügt)
	 * @var string
	 */
	private $ClassName = '';
	/**
	 * Felddaten name,type
	 * @var array
	 */
	private $Fields = array();
	/**
	 * So viele Zeichen brauchts bis zu einem Zeilenumbruch (DB Felder)
	 * @var int
	 */
	const LINE_BREAK_THRESHOLD = 60;

	/**
	 * Erstellt den Generator, Vorsicht, kein Errorhandling
	 * @param string $Table
	 * @param dbConn $Conn 
	 */
	public function __construct($Table,dbConn &$Conn) {
		$this->Table = $Table;
		$this->ClassName = $Table;
		$this->Conn = $Conn;
		$this->Conn->setDB('INFORMATION_SCHEMA');
		$this->loadFields();
	}

	/**
	 * Definiert den zu generierenden Klassennamen
	 * @param string $name Klassenname
	 */
	public function setClassName($name) {
		$this->ClassName = $name;
	}

	/**
	 * Generiert den Code, evtl. in ein Textfeld, zum kopieren
	 * @param bool $bTextfield Gibt an, ob der code in ein Textfeld generiert wird
	 */
	public function generate($bTextfield = true) {
		$code = '';
		// Klassenheader ausgeben
		$this->addHeader($code);
		// Properties hinzufügen
		$this->addProperties($code);
		// Konstruktor hinzufügen
		$this->addConstructor($code);
		// Load / LoadRow Funktion hinzufügen
		$this->addLoad($code);
		$this->addLoadRow($code);
		// Update,Insert,Delete Funktion
		$this->addUpdate($code);
		$this->addInsert($code);
		$this->addDelete($code);
		// getter/setter Funktionen
		$this->addGetterSetter($code);
		// Klasse abschliessen
		$code .= "}";
		// Tabs mit Leerzeichen ersetzen
		$code = str_replace("{T}", "    ", $code);
		// Datenbank am Ende wieder zurücksetzen
		$this->Conn->setInstanceDB();
		// Daten zurückgeben
		if ($bTextfield) {
			return('<textarea rows="30" cols="95">'.$code.'</textarea>');
		}
		// Falls keine Area, Code direkt zurückgeben
		return($code);
	}

	/**
	 * Klassenheader hinzufügen
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addHeader(&$code) {
		$code .= "<?php\r\n";
		$code .= "/**\r\n";
		$code .= " * MyClassDescriptionClicktoChange\r\n";
		$code .= " * @author Michael Sebel <michael@sebel.ch>\r\n";
		$code .= " */\r\n";
		$code .= "class $this->ClassName extends abstractRow {\r\n{T}\r\n";
	}

	/**
	 * Klassenvariablen hinzufügen
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addProperties(&$code) {
		// Alle Properties durchgehen
		foreach ($this->Fields as $Property) {
			// Codeblock generieren
			$code .= "{T}/**\r\n";
			$code .= "{T} * $Property[Name]: $Property[Comment]\r\n";
			$code .= "{T} * @var $Property[Datatype]\r\n";
			$code .= "{T} */\r\n";
			$code .= "{T}private \$my$Property[Variable] = $Property[Default];\r\n";
		}
		$code .= "\r\n"; // Einen Extraumbruch
	}

	/**
	 * Erstellt einen einfachen Konstruktor der den Parent aufruft
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addConstructor(&$code) {
		$code .= "{T}/**\r\n";
		$code .= "{T} * &Uuml;berschreibt den Standardkonstruktor, tut nichts spezielles\r\n";
		$code .= "{T} * @param int \$nID Zu ladender Datensatz\r\n";
		$code .= "{T} */\r\n";
		$code .= "{T}public function __construct(\$nID = 0) {\r\n";
		$code .= "{T}{T}parent::__construct(\$nID);\r\n";
		$code .= "{T}}\r\n\r\n";
	}

	/**
	 * Code zum laden einer Datenzeile
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addLoad(&$code) {
		// Name des ersten Feldes und Felder für Select
		$firstfield = $this->Fields[0]['Name'];
		$fields = $this->getLinebreakedFields(10);
		// Code generieren
		$code .= "{T}/**\r\n";
		$code .= "{T} * L&auml;dt den Datensatz ins lokale Objekt\r\n";
		$code .= "{T} * @param int \$nID ID des Datensatzes\r\n";
		$code .= "{T} */\r\n";
		$code .= "{T}public function load(\$nID) {\r\n";
		$code .= "{T}{T}\$sSQL = 'SELECT $fields\r\n";
		$code .= "{T}{T}FROM $this->Table\r\n";
		$code .= "{T}{T}WHERE $firstfield = '.\$nID;\r\n";
		$code .= "{T}{T}\$nRes = \$this->Conn->execute(\$sSQL);\r\n";
		$code .= "{T}{T}if (\$row = \$this->Conn->next(\$nRes)) {\r\n";
		$code .= "{T}{T}{T}\$this->loadRow(\$row);\r\n";
		$code .= "{T}{T}}\r\n";
		$code .= "{T}}\r\n\r\n";
	}

	/**
	 * Erstellt die loadRow Funktion
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addLoadRow(&$code) {
		// Code generieren
		$code .= "{T}/**\r\n";
		$code .= "{T} * L&auml;dt vorhandene Datenzeile ins Objekt\r\n";
		$code .= "{T} * @param array \$row Datenzeile\r\n";
		$code .= "{T} */\r\n";
		$code .= "{T}public function loadRow(\$row) {\r\n";
		$code .= "{T}{T}// Alle Objekte mit settern laden\r\n";
		// Alle Felder definieren
		foreach ($this->Fields as $field) {
			if ($field['isID']) {
				$code .= "{T}{T}\$this->my$field[Variable] = getInt(\$row['$field[Name]']);\r\n";
			} else {
				$code .= "{T}{T}\$this->set$field[Variable](\$row['$field[Name]']);\r\n";
			}
		}
		// Rest der Funktion staggeln
		$code .= "{T}{T}// Objekt als initialisiert taxieren\r\n";
		$code .= "{T}{T}\$this->isInitialized = true;\r\n";
		$code .= "{T}}\r\n\r\n";
	}

	/**
	 * Erstellt die update Funktion
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addUpdate(&$code) {
		// Code generieren
		$code .= "{T}/**\r\n";
		$code .= "{T} * Speichert die lokalen Daten\r\n";
		$code .= "{T} * @return int Prim&auml;rschl&uuml;ssel\r\n";
		$code .= "{T} */\r\n";
		$code .= "{T}public function update() {\r\n";
		$code .= "{T}{T}\$sSQL = \"UPDATE ".$this->Table." SET\r\n";
		// Alle Felder definieren
		$bFirst = true;
		$nCount = 0;
		foreach ($this->Fields as $field) {
			if ($bFirst) { $bFirst = false; continue; }
			$nCount++;
			if ($nCount == 1) $code .= "{T}{T}";
			$code .= "$field[Name] = ".$this->insertField($field);
			// Nur jeden zweiten ein Umbruch
			if ($nCount % 2 == 0) $code .= "\r\n{T}{T}";
		}
		$name = $this->Fields[0]['Name'];
		$var = $this->Fields[0]['Variable'];
		// Rest der Funktion staggeln
		$code .= "\r\n";
		$code .= "{T}{T}WHERE ".$name." = \$this->my".$var."\";\r\n";
		$code .= "{T}{T}\$this->Conn->command(\$sSQL);\r\n";
		$code .= "{T}{T}return(\$this->get".$var."());\r\n";
		$code .= "{T}}\r\n\r\n";
	}

	/**
	 * Erstellt die Insert Funktion
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addInsert(&$code) {
		// Felder erstellen
		$fields = $this->getLinebreakedFields(30,true);
		// Code generieren
		$code .= "{T}/**\r\n";
		$code .= "{T} * Erstellt die lokalen Daten\r\n";
		$code .= "{T} * @return int Prim&auml;rschl&uuml;ssel\r\n";
		$code .= "{T} */\r\n";
		$code .= "{T}public function insert() {\r\n";
		$code .= "{T}{T}\$sSQL = \"INSERT INTO ".$this->Table." ($fields) VALUES (\r\n{T}{T}";
		// Alle Felder definieren
		$nCount = 0;
		foreach ($this->Fields as $field) {
			if ($nCount == 0) { $nCount++; continue; }
			$code .= $this->insertField($field,")\";");
			if ($nCount % 3 == 0) $code .= "\r\n{T}{T}";
			$nCount++;
		}
		$name = $this->Fields[0]['Name'];
		$var = $this->Fields[0]['Variable'];
		// Rest der Funktion staggeln
		$code .= "{T}{T}\r\n";
		$code .= "{T}{T}\$this->my$var = \$this->Conn->insert(\$sSQL);\r\n";
		$code .= "{T}{T}return(\$this->get".$var."());\r\n";
		$code .= "{T}}\r\n\r\n";
	}

	/**
	 * Erstellt die Delete Funktion
	 * @param string $code Variable um Code anzuhängen
	 */
	private function addDelete(&$code) {
		// Daten des ersten Feldes für Primary Delete
		$name = $this->Fields[0]['Name'];
		$var = $this->Fields[0]['Variable'];
		// Code generieren
		$code .= "{T}/**\r\n";
		$code .= "{T} * Simple L&ouml;schfunktion\r\n";
		$code .= "{T} */\r\n";
		$code .= "{T}public function delete() {\r\n";
		$code .= "{T}{T}\$sSQL = \"DELETE FROM ".$this->Table."\r\n";
		$code .= "{T}{T}WHERE $name = \".\$this->get$var();\r\n";
		$code .= "{T}{T}\$this->Conn->command(\$sSQL);\r\n";
		$code .= "{T}}\r\n\r\n";
	}

	/**
	 * Getter und Setter hinzufügen
	 */
	private function addGetterSetter(&$code) {
		foreach($this->Fields as $field) {
			// Getter für alle Felder
			$code .= "{T}/**\r\n";
			$code .= "{T} * Getter f&uuml;r $field[Name]: $field[Comment]\r\n";
			$code .= "{T} * @return $field[Datatype] Wert von '$field[Name]'\r\n";
			$code .= "{T} */\r\n";
			$code .= "{T}public function get$field[Variable]() {\r\n";
			$this->getUnvalidatedField($code,$field);
			$code .= "{T}}\r\n\r\n";
			// Setter nur, wenn es nicht das Primärfeld ist
			if (!$field['isID']) {
				$code .= "{T}/**\r\n";
				$code .= "{T} * Setter f&uuml;r $field[Name]: $field[Comment]\r\n";
				$code .= "{T} * @param $field[Datatype] Neuer Wert f&uuml;r '$field[Name]'\r\n";
				$code .= "{T} */\r\n";
				$code .= "{T}public function set$field[Variable](\$value) {\r\n";
				$this->getValidatedField($code,$field);
				$code .= "{T}}\r\n\r\n";
			}
		}
	}

	/**
	 * Erstellt Code für die rückvalidierung (Nur für Strings)
	 * @param array $field Datenfeld zur Validierung
	 */
	private function getUnvalidatedField(&$code,$field) {
		switch($field['Datatype']) {
			case 'string':
				$code .= "{T}{T}\$value = stripslashes(\$this->my$field[Variable]);\r\n";
				$code .= "{T}{T}return(\$value);\r\n";
				break;
			case 'int':
			case 'double':
				$code .= "{T}{T}return(\$this->my$field[Variable]);\r\n";
				break;
		}
	}

	/**
	 * Erstellt Code für die simpelste Validierung
	 * @param array $field Datenfeld zur Validierung
	 */
	private function getValidatedField(&$code,$field) {
		switch($field['Datatype']) {
			case 'string':
				$code .= "{T}{T}\$this->Conn->escape(\$value);\r\n";
				$code .= "{T}{T}\$this->my$field[Variable] = \$value;\r\n";
				break;
			case 'int':
				$code .= "{T}{T}\$value = getInt(\$value);\r\n";
				$code .= "{T}{T}\$this->my$field[Variable] = \$value;\r\n";
				break;
			case 'double':
				$code .= "{T}{T}\$value = numericOps::getDecimal(\$value,2);\r\n";
				$code .= "{T}{T}\$this->my$field[Variable] = \$value;\r\n";
				break;
		}
	}

	/**
	 * Gibt ein Feld aus mit oder ohne Hochkomma und
	 * ein Komma am Ende, wenn es das letzte ist
	 * @param array $field Daten eines einzelnen Feldes
	 */
	private function insertField($field,$lastElse = '') {
		$string = '';
		$lastfield = $this->Fields[count($this->Fields)-1];
		// String oder nicht
		if ($field['Datatype'] == 'string') {
			$string .= "'\$this->my".$field['Variable']."'";
		} else {
			$string .= "\$this->my".$field['Variable']."";
		}
		// Komma hinzufügen, wenn es nicht das letzte ist
		if ($lastfield['Name'] != $field['Name']) {
			$string .= ',';
		} else {
			$string .= $lastElse;
		}
		return($string);
	}

	/**
	 * Gibt die Datenbankfelder mit LineBreak zurück
	 */
	private function getLinebreakedFields($nOffset,$omitFirst = false) {
		$lines = array();
		$line = '';
		foreach ($this->Fields as $field) {
			if ($omitFirst) { $omitFirst = false; continue; }
			$line .= $field['Name'].',';
			// Zeilenumbruch behandeln
			if (strlen($line) > self::LINE_BREAK_THRESHOLD - $nOffset) {
				array_push($lines,$line);
				$line = '';
				$nOffset = 0;
			}
		}
		// Die letzte Zeile hinzufügen
		if (strlen($line) > 0) array_push($lines,$line);
		// Alle Zeilen zu einer zusammenführen
		$fields = implode("\r\n{T}{T}", $lines);
		// Komma am Ende löschen
		$fields = substr($fields,0,strlen($fields) - 1);
		return($fields);
	}

	/**
	 * Variabelname aus Datenbankfeld erzeugen
	 * @param string $name Datenbankfeldname
	 */
	private function getVariable($name) {
		// Wenn es nicht mit _ID aufhört, Prefix entfernen
		if (!stringOps::endsWith($name, '_ID')) {
			$name = substr($name, strpos($name,'_'));
		}
		// Kapitalisieren
		$name = ucwords($name);
		// Underlines entfernen
		$name = str_replace('_', '', $name);
		return($name);
	}

	/**
	 * Gibt an, ob es ein ID Feld ist
	 * @param string $name Datenbankfeldname
	 */
	private function isID($name) {
		if (stringOps::endsWith($name, '_ID')) {
			return(true);
		} else {
			return(false);
		}
	}

	/**
	 * Gibt den PHP Datentyp zurück
	 * @param string $type Typ aus Datenbank
	 */
	private function getType($type) {
		switch($type) {
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'bigint':
				$type = 'int'; break;
			case 'float':
			case 'double':
			case 'real':
			case 'decimal':
				$type = 'double'; break;
			default:
				$type = 'string';
		}
		return($type);
	}

	/**
	 * Gibt den Default Wert für einen Datentypen zurück
	 * @param string $datatype
	 */
	private function getDefault($datatype) {
		switch ($datatype) {
			case 'int': $default = '0'; break;
			case 'double': $default = '0.0'; break;
			case 'string': $default = '\'\''; break;
		}
		return($default);
	}

	/**
	 * Lädt die nötigen Felddaten ins lokale Array
	 */
	private function loadFields() {
		$sSQL = 'SELECT COLUMN_NAME,DATA_TYPE,COLUMN_COMMENT FROM COLUMNS
		WHERE TABLE_NAME = "'.$this->Table.'" ORDER BY ORDINAL_POSITION ASC';
		$nRes = $this->Conn->execute($sSQL);
		$nCount = 0;
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Fields,array(
				'Name' => $row['COLUMN_NAME'],
				'Type' => $row['DATA_TYPE'],
				'Variable' => $this->getVariable($row['COLUMN_NAME']),
				'Datatype' =>  $this->getType($row['DATA_TYPE']),
				'Default' => $this->getDefault($this->getType($row['DATA_TYPE'])),
				'Comment' => $row['COLUMN_COMMENT'],
				'isID' => ($nCount == 0)
			));
			$nCount++;
		}
	}
}
?>
