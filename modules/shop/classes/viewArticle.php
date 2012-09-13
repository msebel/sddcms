<?php
/**
 * View für Artikel
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewArticle extends abstractShopView {

	/**
	 * Ruft lediglich den Basiskonstruktor auf
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name) {
		parent::__construct($name);
	}

	/**
	 * Führt die View aus
	 */
	public function getContent() {
		// Artikel laden
		$nArtID = getInt($_GET['a']);
		$Article = new shopArticle($nArtID);
		if ($Article->getShaID() > 0) {
			$data = $Article->toTemplate();
			// Standard Variablen einfüllen
			foreach ($data as $key => $value) {
				$this->Tpl->addData($key, $value);
			}
			// Liste aller Bilder (Mit JS Wechsel und LB)
			$this->addImages($Article);
			// Extended Variablen als Liste einfüllen
			$this->addVariables($Article);
			// Wenn Artikelgrössen vorhanden, Dropdown anzeigen
			$this->Tpl->addData(
				'ARTICLE_SIZE_CONTROL', 
				shopStatic::addSizesControl($Article)
			);
		}
		// Weiterleiten auf Errorseite, wenn Artikel inaktiv
		if ($Article->getActive() == 0) {
			redirect('location: /error.php?type=noAccess');
		}
		// Artikel wurde 1x angeschaut
		$nVisited = $Article->getVisited() + 1;
		$Article->setVisited($nVisited);
		$Article->save();
		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Zusatzvariablen einfüllen in Liste
	 * @param shopArticle $Article Zu darstellender Artikel
	 */
	protected function addVariables(shopArticle $Article) {
		// Subtemplate erstellen
		$tPath = shopStatic::getTemplate('article-extended');
		$sub = new templateImproved($tPath);
		// Template für Listeneintrag
		$tPath = shopStatic::getTemplate('extendedvars-listentry');
		$tpl = new templateImproved($tPath);
		$list = new templateList($tpl);
		// Properties des Artikels holen (Wenn vorhanden)
		$sSQL = 'SELECT sdd_Value,sdf_Name FROM tbshopdynamicdata 
		INNER JOIN tbshopdynamicfield ON tbshopdynamicdata.sdf_ID = tbshopdynamicfield.sdf_ID
		WHERE tbshopdynamicdata.sha_ID = '.$Article->getShaID().' ORDER BY sdf_Name';
		$nRes = $this->Conn->execute($sSQL);
		$nCount = 0;
		$UsedFields = array();
		while ($row = $this->Conn->next($nRes)) {
			$nCount++;
			if (isset($UsedFields[$row['sdf_Name']])) {
				$row['sdf_Name'] = '&nbsp;';
			}
			$list->addData(array(
				'PROPERTY_NAME' => $row['sdf_Name'],
				'PROPERTY_VALUE' => $row['sdd_Value']
			));
			$UsedFields[$row['sdf_Name']] = true;
		}
		// Liste ins Template geben (Nur wenn etwas vorhanden)
		if ($nCount > 0) {
			$sub->addList('LIST_EXTENDED_PROPERTIES', $list);
		} else {
			$tPath = shopStatic::getTemplate('empty');
			$sub = new templateImproved($tPath);
		}
		// Subtemplate hinzufügen
		$this->Tpl->addSubtemplate('SUB_EXTENDED', $sub);
	}

	/**
	 * Erstellt die Liste der Bilder
	 * @param shopArticle $Article Zu darstellender Artikel
	 */
	protected function addImages(shopArticle $Article) {
		// Template für Listeneintrag
		$tPath = shopStatic::getTemplate('image-listentry');
		$tpl = new templateImproved($tPath);
		$list = new templateList($tpl);
		// Liste der Files laden
		$nEleID = $Article->getImage();
		$sPath = shopStatic::getElementFolder($nEleID);
		$files = fileOps::getFiles(BP.$sPath,true);
		$sUrl = '/modules/shop/getimage.php?id='.page::menuID();
		foreach ($files as $file) {
			$list->addData(array(
				'IMAGE_ORIGINAL' => $sUrl.'&e='.$nEleID.'&type=original&file='.$file,
				'IMAGE_THUMB' => $sUrl.'&e='.$nEleID.'&type=thumb&file='.$file,
				'IMAGE_WIDTH' => shopModuleConfig::THUMB_WIDTH,
				'IMAGE_HEIGHT' => shopModuleConfig::THUMB_HEIGHT,
			));
		}
		// Liste ins Template geben
		$this->Tpl->addList('LIST_IMAGES', $list);
	}
}