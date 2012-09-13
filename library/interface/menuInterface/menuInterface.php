<?php
/**
 * Bildet das Menuobjekt ab
 * @author Michael Sebel <michael@sebel.ch>
 */
interface menuInterface {

	/**
	 * Konstruktor, welcher alle Objekte lädt. 
	 * Das Menu wird nur beim ersten mal geladen und ist dann in der Session gelagert
	 * @param access Access, Referenz zum Zugriffsobjekt
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	public function __construct(access &$Access,dbConn &$Conn);
	
	/**
	 * Alle Menuobjekte laden (für die Menuverwaltung).
	 * Zugriffe und inaktive/unsichtbare Menus werden nicht beachtet
	 * @param string sParent, Übergeordneter Item String
	 */
	public function loadAllMenuObjects($sParent);
	
	/**
	 * Menuobjekte löschen und das menuObjects array neu initialisieren.
	 */
	public function reset();
	
	/**
	 * Das Menu als HTML laden.
	 * Hier werden auch die Optionen für individuelle menu-HTML-Daten geladen
	 * @return string HTML Code mit dem Menu drin
	 */
	public function getMenu();
	
	/**
	 * Das Menu in Form von select Options bekommen.
	 * @param integer sSelected, ID des selektierten Menupunktes
	 * @return string <options> HTML Code für Selectbox output
	 */
	public function getSelectOptions($sSelected = NULL);
	
	/**
	 * Array der Menuobjekte für spezielle Verarbeitung zurückgeben.
	 */
	public function getMenuObjects();
}