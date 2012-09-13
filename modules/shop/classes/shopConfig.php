<?php
/**
 * Shopkonfiguration aus der Datenbank (Cached)
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopConfig {

	/**
	 * Lokale kopier der Session Konfiguration
	 * @var array
	 */
	private static $Config = NULL;

	/**
	 * Initialisiert die Konfig und/oder holt eine
	 * bestehende Config aus der Session
	 * @param dbConn $Conn Offene Datenbankverbindung
	 */
	public static function initialize(dbConn &$Conn) {
		$Config = sessionConfig::get('ShopConfig', NULL);
		if ($Config == NULL || DEBUG) {
			self::loadConfig($Config,$Conn);
			sessionConfig::set('ShopConfig', $Config);
		}
		// Session in lokalen Speicher laden
		self::$Config = $Config;
	}

	/**
	 * Gibt einen Dump der Konfiguration aus
	 */
	public static function debug() {
		debug(self::$Config);
	}

	/**
	 * Lädt die aktuelle Konfiguration in die Session
	 * @param array $Config Hierein wird die Konfig gespeichert
	 * @param dbConn $Conn Offene Datenbankverbinung
	 */
	private static function loadConfig(&$Config,dbConn &$Conn) {
		// By Default, keine Configuration...
		$Config = NULL;
		// Versuchen eine Config zu laden
		$sSQL = 'SELECT shc_ID,usr_ID,shc_Maillogo,shc_Loginmenu,shc_IBAN,shc_Post,shc_Payment,
		shc_DeliveryCost,shc_Tipoftheday,shc_Stockdata,shc_Stockconfig,shc_Templates,shc_Mails,
		shc_MwstConfig,shc_Delivery,shc_BillMaximum,shc_BillActive,shc_PaypalActive,shc_GlobalMwst,
		shc_Conditioning FROM tbshopconfig WHERE man_ID = '.page::mandant();
		$nRes = $Conn->execute($sSQL);
		if ($row = $Conn->next($nRes)) {
			// Grunddaten laden
			$Config['ID'] = getInt($row['shc_ID']);
			$Config['UserID'] = getInt($row['usr_ID']);
			$Config['MaillogoID'] = getInt($row['shc_Maillogo']);
			$Config['LoginMenuID'] = getInt($row['shc_Loginmenu']);
			$Config['IBAN'] = $row['shc_IBAN'];
			$Config['Post'] = $row['shc_Post'];
			$Config['Payment'] = $row['shc_Payment'];
			$Config['DeliveryCost'] = $row['shc_DeliveryCost'];
			$Config['Tipoftheday'] = numericOps::getBoolFromInt($row['shc_Tipoftheday']);
			$Config['Stockdata'] = numericOps::getBoolFromInt($row['shc_Stockdata']);
			$Config['Stockconfig'] = numericOps::getBoolFromInt($row['shc_Stockconfig']);
			$Config['Templates'] = BP.$row['shc_Templates'];
			$Config['Mails'] = BP.$row['shc_Mails'];
			$Config['MwstConfig'] = numericOps::getBoolFromInt($row['shc_MwstConfig']);
			$Config['Delivery'] = numericOps::getBoolFromInt($row['shc_Delivery']);
			$Config['BillMaximum'] = getInt($row['shc_BillMaximum']);
			$Config['BillActive'] = numericOps::getBoolFromInt($row['shc_BillActive']);
			$Config['PaypalActive'] = numericOps::getBoolFromInt($row['shc_PaypalActive']);
			$Config['GlobalMwst'] = numericOps::getDecimal($row['shc_GlobalMwst'],1);
			$Config['Conditioning'] = getInt($row['shc_Conditioning']);
			// Weitere Konfigurationen laden (tbshopaddition)
			self::loadAdditions($Config,$Conn);
			// Lieferentitäten laden (tbshopdeliveryentity) wenn nötig
			self::loadDeliveryentites($Config,$Conn);
			// Daten für Mengenrabatte laden (tbshopmasscondition)
			self::loadMassconditions($Config,$Conn);;
		}
		// Config NULL oder ausgefüllt zurückgeben
		return($Config);
	}

	/**
	 * Lädt die Lieferentitäten und gibt Sie zurück
	 * @param array $Config Beschreibbare Konfiguration
	 * @param dbConn $Conn Offene Datenbankverbindung
	 */
	private static function loadDeliveryentites(&$Config,dbConn &$Conn) {
		$Config['Deliveryentities'] = array();
		// Nur wenn das Berechnen der Lieferkosten eingeschaltet ist
		if ($Config['Delivery']) {
			$sSQL = 'SELECT sde_Entities,sde_Cost FROM tbshopdeliveryentity
			WHERE shc_ID = '.$Config['ID'].' ORDER BY sde_Order ASC';
			$nRes = $Conn->execute($sSQL);
			while ($row = $Conn->next($nRes)) {
				array_push($Config['Deliveryentities'],array(
					'Entities' => getInt($row['sde_Entities']),
					'Cost' => (float) numericOps::getDecimal($row['sde_Cost'], 2)
				));
			}
		}
	}

	/**
	 * Lädt die Zusatzkonfigurationen und gibt Sie zurück
	 * @param array $Config Beschreibbare Konfiguration
	 * @param dbConn $Conn Offene Datenbankverbindung
	 */
	private static function loadAdditions(&$Config,dbConn &$Conn) {
		$Config['Additions'] = array();
		$sSQL = 'SELECT sac_Field,sac_Value FROM tbshopaddition
		WHERE shc_ID = '.$Config['ID'].' ORDER BY sac_Field ASC';
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) {
			$Config['Additions'][$row['sac_Field']] = $row['sac_Value'];
		}
	}

	/**
	 * Lädt die Mengenrabatte und gibt Sie zurück
	 * @param array $Config Beschreibbare Konfiguration
	 * @param dbConn $Conn Offene Datenbankverbindung
	 */
	private static function loadMassconditions(&$Config,dbConn &$Conn) {
		$Config['Massconditions'] = array();
		// Nur, wenn eine Konditionierung vorgesehen ist
		if ($Config['Conditioning'] > 0) {
			$sSQL = 'SELECT smc_Amount,smc_Condition FROM tbshopmasscondition
			WHERE shc_ID = '.$Config['ID'].' ORDER BY smc_Order ASC';
			$nRes = $Conn->execute($sSQL);
			while ($row = $Conn->next($nRes)) {
				array_push($Config['Massconditions'],array(
					'Amount' => (float) numericOps::getDecimal($row['smc_Amount'], 2),
					'Condition' => getInt($row['smc_Condition'])
				));
			}
		}
	}

	/**
	 * Gibt die Variable ID zurück
	 * @return type Variable ID
	 */
	public static function ID() {
		return(self::$Config['ID']);
	}

	/**
	 * Gibt die Variable UserID zurück
	 * @return type Variable UserID
	 */
	public static function UserID() {
		return(self::$Config['UserID']);
	}

	/**
	 * Gibt die Variable MaillogoID zurück
	 * @return type Variable NMaillogoIDAME
	 */
	public static function MaillogoID() {
		return(self::$Config['MaillogoID']);
	}

	/**
	 * Gibt die Variable LoginMenuID zurück
	 * @return type Variable LoginMenuID
	 */
	public static function LoginMenuID() {
		return(self::$Config['LoginMenuID']);
	}

	/**
	 * Gibt die Variable IBAN zurück
	 * @return type Variable IBAN
	 */
	public static function IBAN() {
		return(self::$Config['IBAN']);
	}

	/**
	 * Gibt die Variable Post zurück
	 * @return type Variable Post
	 */
	public static function Post() {
		return(self::$Config['Post']);
	}

	/**
	 * Gibt die Variable Payment zurück
	 * @return type Variable Payment
	 */
	public static function Payment() {
		return(self::$Config['Payment']);
	}

	/**
	 * Gibt die Variable DeliveryCost zurück
	 * @return type Variable DeliveryCost
	 */
	public static function DeliveryCost() {
		return(self::$Config['DeliveryCost']);
	}

	/**
	 * Gibt die Variable Tipoftheday zurück
	 * @return type Variable Tipoftheday
	 */
	public static function Tipoftheday() {
		return(self::$Config['Tipoftheday']);
	}

	/**
	 * Gibt die Variable Stockdata zurück
	 * @return type Variable Stockdata
	 */
	public static function Stockdata() {
		return(self::$Config['Stockdata']);
	}

	/**
	 * Gibt die Variable Stockconfig zurück
	 * @return type Variable Stockconfig
	 */
	public static function Stockconfig() {
		return(self::$Config['Stockconfig']);
	}

	/**
	 * Gibt die Variable Templates zurück
	 * @return type Variable Templates
	 */
	public static function Templates() {
		return(self::$Config['Templates']);
	}

	/**
	 * Gibt die Variable Mails zurück
	 * @return type Variable Mails
	 */
	public static function Mails() {
		return(self::$Config['Mails']);
	}

	/**
	 * Gibt die Variable MwstConfig zurück
	 * @return type Variable MwstConfig
	 */
	public static function MwstConfig() {
		return(self::$Config['MwstConfig']);
	}

	/**
	 * Gibt die Variable Delivery zurück
	 * @return type Variable Delivery
	 */
	public static function Delivery() {
		return(self::$Config['Delivery']);
	}

	/**
	 * Gibt die Variable BillMaximum zurück
	 * @return type Variable BillMaximum
	 */
	public static function BillMaximum() {
		return(self::$Config['BillMaximum']);
	}

	/**
	 * Gibt die Variable BillActive zurück
	 * @return type Variable BillActive
	 */
	public static function BillActive() {
		return(self::$Config['BillActive']);
	}

	/**
	 * Gibt die Variable PaypalActive zurück
	 * @return type Variable PaypalActive
	 */
	public static function PaypalActive() {
		return(self::$Config['PaypalActive']);
	}

	/**
	 * Gibt die Variable GlobalMwst zurück
	 * @return type Variable GlobalMwst
	 */
	public static function GlobalMwst() {
		return(self::$Config['GlobalMwst']);
	}

	/**
	 * Gibt die Variable Conditioning zurück
	 * @return type Variable Conditioning
	 */
	public static function Conditioning() {
		return(self::$Config['Conditioning']);
	}

	/**
	 * Gibt die Variable Massconditions zurück
	 * @return type Variable Massconditions
	 */
	public static function Massconditions() {
		return(self::$Config['Massconditions']);
	}

	/**
	 * Gibt die Variable Additions zurück
	 * @return type Variable Additions
	 */
	public static function Additions() {
		return(self::$Config['Additions']);
	}

	/**
	 * Gibt die Variable Deliveryentities zurück
	 * @return type Variable Deliveryentities
	 */
	public static function Deliveryentities() {
		return(self::$Config['Deliveryentities']);
	}
}
?>
