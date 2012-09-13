<?php
/**
 * Funktionssammlung für Caching
 * @author Michael Sebel <michael@sebel.ch>
 */
class cacheLib {

	/**
	 * Checkt, ob die Design CSS Files des aktuellen Designs eine neue Versionsnummer bekommen
	 */
	public static function checkDesignFiles($design) {
		$version = option::get('designCssVersion_'.$design,1);
		// Schauen ob serialisierte Daten vorhanden sind
		$data = option::get('designCssData_'.$design,false);
		if ($data == false) {
			$data = array(
				'default_main' => 0,
				'default_local' => 0,
				'format' => 0,
				'design' => 0,
			);
		} else {
			$data = unserialize(stripslashes($data));
		}
		// get the file age of all the files
		$compare = array(
			'default_main' => filemtime(BP.'/resource/css/default.css'),
			'default_local' => filemtime(BP.'/design/'.$design.'/default.css'),
			'format' => filemtime(BP.'/design/'.$design.'/format.css'),
			'design' => filemtime(BP.'/design/'.$design.'/design.css'),
		);
		// Loop through until something is newer than before
		foreach ($data as $key => $value) {
			if ($value < $compare[$key]) {
				option::set('designCssVersion_'.$design,++$version);
				option::set('designCssData_'.$design,serialize($compare));
				break;
			}
		}
	}

}