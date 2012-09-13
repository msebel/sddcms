<?php
/**
 * Konvertiert Bilder in ein anderes Format.
 * GIF, JPG und PNG Bilder können in letztere Formate konvertiert werden.
 * @author Michael Sebel <michael@sebel.ch>
 */
class imageConverter {
	
	/**
	 * Typ des Images, png, jpg oder gif
	 * @var string
	 */
    private $imtype;
    /**
     * Image Objekt (konvertierte version)
     * @var object
     */
    private $im;
    /**
     * Name des Bildes
     * @var string
     */
    private $imname;
    /**
     * Pfad zum Bild
     * @var string
     */
    private $impath;
    /**
     * Angabe, ob das Bild gültig ist
     * @var boolean
     */
    private $isvalid = false;
    /**
     * Konvertiertes Dateityp des Bilder
     * @var string
     */
    private $imconvertedtype;
	
    /**
     * Objekterstellen und Konvert starten
     * @param string path, Pfad zum konvertierenden Bild
     * @param string imagefile, Name des konvertierenden Bildes
     * @param string convertedtype, Neuer Dateityp des Bildes
     */
    public function __construct($path,$imagefile,$convertedtype) {

        // Bild validieren
        $fileinfo     = pathinfo($imagefile);
        $imtype       = $fileinfo["extension"];
        $this->imname = basename($fileinfo["basename"],".".$imtype);
        $this->impath = $path;
        $this->imtype = $imtype;

        // Originalfile einlesen
        switch ($imtype) {
        case "gif":
            $this->im = imageCreateFromGIF($path.$imagefile);
            break;
        case "jpg":
            $this->im = imageCreateFromJPEG($path.$imagefile);
            break;
        case "png":
            $this->im = imageCreateFromPNG($path.$imagefile);
            break;
        }

        // Bild konovertieren
        $this->convertImage($convertedtype);
    }
    
    /**
     * Bildobjekt aus dem Arbeitsspeicher zerstören
     */
    public function __destruct() {
    	imagedestroy($this->im);
    }
    
    /**
     * Gibt an, ob die konvertierung erfolgreich war
     * @return boolean True wenn erfolgreich
     */
    public function isValid() {
    	return($this->isvalid);
    }
	
    /**
     * Konvertiert das Image in den gegebenen Typ
     * @param string type, Neuer Typ des Images (png,jpg,gif)
     */
    private function convertImage($type) {

        // Filetyp prüfen
        $validtype = $this->validateType($type);

        // Bild neben dem original (auf disk im selben ordner) speichern
        switch($validtype){
            case 'jpeg' :
            case 'jpg'     :
                if($this->imtype == 'gif' or $this->imtype == 'png') {
                    // Transparenz ersetzen
                    $image = $this->replaceTransparentWhite($this->im);
                    imageJPEG($image,$this->impath.$this->imname.".jpg");
                    $this->isvalid = true;
                } else {
                	imageJPEG($this->im,$this->impath.$this->imname.".jpg");
                	$this->isvalid = true;
        		}
                break;
            case 'gif' :
                imageGIF($this->im,$this->impath.$this->imname.".gif");
                $this->isvalid = true;
                break;
            case 'png' :
                imagePNG($this->im,$this->impath.$this->imname.".png");
                $this->isvalid = true;
                break;
        }
    }
	
    /**
     * Validiert Imagetypen und Librarys
     * @param string type, Neuer Typ des Images (png,jpg,gif)
     * @return string Validierter Dateityp
     */
    private function validateType($type) {
        // Grafikfunktionen abfragen
        $is_available = false;

        switch($type){
            case 'jpeg':
            case 'jpg':
                if(function_exists("imagejpeg"))
                $is_available = true;
                break;
            case 'gif':
                if(function_exists("imagegif"))
                $is_available = true;
                break;
            case 'png':
                if(function_exists("imagepng"))
                $is_available = true;
                break;
        }
        if(!$is_available && function_exists("imagejpeg")){
            // Wenn keine Library kast zu jpeg versuchen
            return "jpeg";
        }
        else if(!$is_available && !function_exists("imagejpeg")){
           die("Konvertieren nicht möglich, libararies fehlen");
        }
        else
            return $type;
    }
	
    /**
     * Repariert GIF/PNG Transparenz
     * @param object im, Bildobjekt
     * @return object Ausgebessertes Bild
     */
    private function replaceTransparentWhite($im){
        $src_w = ImageSX($im);
        $src_h = ImageSY($im);
        $backgroundimage = imagecreatetruecolor($src_w, $src_h);
        $white =  ImageColorAllocate ($backgroundimage, 255, 255, 255);
        ImageFill($backgroundimage,0,0,$white);
        ImageAlphaBlending($backgroundimage, false);
        imagecopy($backgroundimage, $im, 0,0,0,0, $src_w, $src_h);
        return $backgroundimage;
    }
}