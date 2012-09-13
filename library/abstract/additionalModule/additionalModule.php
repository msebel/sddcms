<?php
/**
 * Basisklasse für individuelle Module.
 * Bietet ähnliche Funktionalität wie im Standard, 
 * jedoch ohne zusätzliche Methoden. So sind individuelle 
 * Module vor Releaseänderungen geschützt.
 * @author Michael Sebel <michael@sebel.ch>
 * @abstract
 */
abstract class additionalModule {
	
	/**
	 * Konstruktor, wird nicht verwendet.
	 * Leer auch Child Klassen weisen einen leeren Konstruktor auf
	 */
	public function __construct() {}
	
	/**
	 * Definiert die zu ladenden Objekte.
	 * Sollte direkt nach der Instanz aufgerufen werden, damit Abhängikeiten 
	 * erfüllt werden. Ist dazu gedacht zumindest $Conn und $Res zu liefern
	 * @abstract
	 */
	abstract public function loadObjects();
}