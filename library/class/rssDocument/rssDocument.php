<?php
/**
 * Klasse, die Funktionen bietet um RSS auszugeben
 * @author Michael Sebel <michael@sebel.ch>
 */
class rssDocument extends XMLWriter {
	
	/**
	 * Erstellt ein RSS Dokument,
	 * @param string title, Titel der RSS News
	 * @param string description, Beschreibung der RSS News
	 * @param string link, Link zu den News (Original, nicht RSS)
	 * @param string date, Datum der publikation (SQL_DATETIME)
	 */
    public function __construct($title, $description, $link, $date) {
    	// Zeitzone auf GMT setzen
		@date_default_timezone_set("GMT"); 
		// Dokument erstellen
        $this->openURI('php://output');
        $this->startDocument('1.0','utf-8');
        $this->setIndent(4);
        // RSS Version 2.0, Atom kompatibel
        $this->startElement('rss');
        $this->writeAttribute('version', '2.0');
        $this->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
		// Channel mit Übergabedaten erstellen
        $this->startElement('channel');
        $this->writeElement('title', utf8_encode($title));
        $this->writeElement('description', utf8_encode($description));
        $this->writeElement('link', $link);
        $date = dateOps::convertDate(
       		dateOps::SQL_DATETIME,
       		dateOps::RFC822_DATE,
       		$date
       	);
        $this->writeElement('pubDate', $date);
    }
    
    /**
     * Erstellt aus einem rssItem Objekt einen Eintrag in die RSS News
     * @param rssItem RSS, Referenz zu einem RSS Eintragsobjekt (rssItem)
     */
    public function addItem(rssItem $RSS) {
    	// Enkodieren des Items
    	$this->encode($RSS->title);
    	$this->encode($RSS->description);
        $this->startElement('item');
        $this->writeElement('title', $RSS->title);
        $this->writeElement('link', $RSS->link);
        $this->writeElement('description', $RSS->description);
        $this->writeElement('guid', $RSS->guid);
        
        if (strlen($RSS->date) > 0) {
        	$sDate = dateOps::convertDate(
        		dateOps::SQL_DATETIME,
        		dateOps::RFC822_DATE,
        		$RSS->date
        	);
            $this->writeElement('pubDate', $sDate);
        }
        
        if (is_array($RSS->category)){
            $this->startElement('category');
            $this->writeAttribute('domain', $RSS->category['domain']);
            $this->text($RSS->category['title']);
            $this->endElement(); // End category
        }
    	$this->endElement(); // End item
    }
    
    /**
     * Kodiert einen referenzierten Wert zu validem HTML Output
     * @param string sParam, Gegebener referenzierter Parameter
     */
    private function encode(&$sParam) {
    	$sParam = utf8_encode($sParam);
    	stringOps::noHtml($sParam);
		$sParam = stringOps::chopString($sParam,300,true);
    }
    
    /**
     * Sendet den XML Output zum browser inkl. Header
     */
    public function output() {
        // End channel
        $this->endElement();
        // End rss
        $this->endElement();
        // Header und output
        header('Content-Type: application/rss+xml; charset=utf-8');
        $this->endDocument();
        $this->flush();
    }
}