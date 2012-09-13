<?php
/**
 * Bietet Validierungs -/ Einschränkungsmethoden für SQL Statements.
 * @author Michael Sebel <michael@sebel.ch>
 */
class sqlOps {
	
	/**
	 * Auf ein bestimmtes Jahr einschränken.
	 * @param string sSQL, zu bearbeitender SQL String
	 * @param integer nYear, einzuschränkendes Jahr
	 * @param string sField, Name des einzuschränkenden Feldes
	 * @param string sDivider, Code vor der Einschränkung (OR, AND etc.)
	 */
	public static function constrainYear(&$sSQL,$nYear,$sField,$sDivider) {
		// Am Statement den Divider anbringen
		$sSQL .= $sDivider;
		// Jahreszahl validieren
		$nYear = getInt($nYear);
		// Startdatum definieren
		$nStartStamp = mktime(0,0,0,1,1,$nYear);
		$sStartDate = dateOps::getTime(dateOps::SQL_DATETIME,$nStartStamp);
		// Enddatum definieren
		$nEndStamp = mktime(0,0,0,12,31,$nYear);
		$sEndDate = dateOps::getTime(dateOps::SQL_DATETIME,$nEndStamp);
		// SQL String erweitern
		$sSQL .= " ($sField >= '$sStartDate' AND $sField <= '$sEndDate')";
	}
	
	/**
	 * Auf ein SQL konformes Datum einschränken.
	 * @param string sSQL, zu bearbeitender SQL String
	 * @param string sStartDate, einzuschränkendes Startdatum
	 * @param string sEndDate, einzuschränkendes Enddatum
	 * @param string sField, Name des einzuschränkenden Feldes
	 * @param string sDivider, Code vor der Einschränkung (OR, AND etc.)
	 */
	public static function constrainSqlDate(&$sSQL,$sStartDate,$sEndDate,$sField,$sDivider) {
		// Am Statement den Divider anbringen
		$sSQL .= $sDivider;
		// SQL String erweitern
		$sSQL .= " ($sField >= '$sStartDate' AND $sField <= '$sEndDate')";
	}
}