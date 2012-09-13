<?php
class moduleShopArticleDetail extends commonModule {

	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
    /**
     * ID des offenen Blocks
     * @var int
     */
    private $OpenBlockID = 1;

    // Daten speichern und Objekt zerstören
    public function __destruct() {
        $_SESSION['openblockid'] = $this->OpenBlockID;
    }

	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn = & func_get_arg(0); // $Conn
		$this->Res = & func_get_arg(1); // $Res
		// Zu öffnender Block
		if (getInt($_SESSION['openblockid']) > 0) {
			$this->OpenBlockID = getInt($_SESSION['openblockid']);
		}
	}

	// Formular für die Artikelgrössen erstellen
	public function getArticleSizesForm(shopArticle $article) {
		return('
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="cNav" width="20">
					<a href="#" id="open_1">+</a>
				</td>
				<td class="cNavSelected" width="150">
					'.$this->Res->html(1067,page::language()).'
				</td>
				<td class="cNav">&nbsp;</td>
			</tr>
		</table>
		<div id="block_1" style="display:none;">
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="cToolbar">
					<div class="cToolbarItem">
						&nbsp;
					</div>
					<div class="cToolbarItem">
						<a href="#" onClick="document.shopArticleSizes.submit()">
						<img src="/images/icons/disk.png" alt="'.$this->Res->html(36, page::language()).'" title="'.$this->Res->html(36, page::language()).'" border="0">
						</a>
					</div>
					<div class="cToolbarItem">
						<a href="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&addsize">
						<img src="/images/icons/page_add.png" alt="'.$this->Res->html(1072, page::language()).'" title="'.$this->Res->html(1072, page::language()).'" border="0">
						</a>
					</div>
				</td>
			</tr>
		</table>
		');
	}

	// Liste der Artikelgrössen aufzeigen
	public function getArticleSizesList(shopArticle $article) {
		$table = htmlControl::admintable();
		$window = htmlControl::window();
		// Tabelle Konfigurieren
		$table->setErrorMessage($this->Res->html(1074,page::language()), 2);
		$table->setLineHeight(20);
		// Kopfzeile definieren
		$table->setHead(array(
			new adminTableHead(20, '&nbsp;'),
			new adminTableHead(30, '&nbsp;'),
			new adminTableHead(180, 'Grösse', 1075),
			new adminTableHead(180, 'Zusatzkosten', 1076),
			new adminTableHead(70, 'Vorauswahl', 1077)
		));
		// Tabelle befüllen
		$sSQL = 'SELECT saz_ID,sha_ID,saz_Value,saz_Priceadd,saz_Primary FROM tbshoparticlesize
		WHERE sha_ID = '.$article->getShaID().' ORDER BY saz_Priceadd ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$size = new shopArticlesize();
			$size->loadRow($row);
			// Zeile erstellen
			$table->addRow($size->getSazID(),array(
				new adminCellIconID(
					'wndEdit'.$size->getSazID(),'/images/icons/bullet_wrench.png','',true
				),
				new adminCellDeleteIcon(
					'detail.php?id='.page::menuID().'&a='.$article->getShaID().'&delsize='.$size->getSazID(),
					$size->getValue(),
					$this->Res->html(213,page::language()),
					true
				),
				new adminCellText($size->getValue()),
				new adminCellText(
					numericOps::getDecimal(
						$article->getCurrentPrice() + $size->getPriceadd(),2
					).
					' (+'.$size->getPriceadd().')'
				),
				new adminCellRadio('primary', $size->getPrimary(), 1, $size->getSazID(), true)
			));
			// Window für Edit erstellen
			$sWindowHtml .= $this->getSizeEditWindow($window,$article,$size);
		}
		// Tabelle mit Formular ausgeben
		return('
		<form name="shopArticleSizes" method="post" action="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&savesizes">
			'.$table->get().'
		</form>
		'.$sWindowHtml.'
		</div><!--EndBlock1-->
		');
	}

	// Artikelgrössen (Primary Feld) speichern
	public function saveSizes(shopArticle $article) {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nSazID = getInt($_POST['id'][$i]);
			$size = new shopArticlesize($nSazID);
			$size->setPrimary(0);
			if ($size->getSazID() == getInt($_POST['primary'])) {
				$size->setPrimary(1);
			}
			$size->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop article sizes '.$nSdfID);
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Artikelgrössen (Primary Feld) speichern
	public function saveSize(shopArticle $article) {
		$size = new shopArticlesize(getInt($_POST['sazID']));
		if ($size->getShaID() == $article->getShaID()) {
			$size->setPriceadd($_POST['sazPriceadd']);
			$size->setValue($_POST['sazValue']);
			$size->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop article size '.$size->getSazID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Formulare für Meta Informationen
	public function getArticleMetaBlock() {
		return('
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="cNav" width="20">
					<a href="#" id="open_2">+</a>
				</td>
				<td class="cNavSelected" width="150">
					'.$this->Res->html(1066,page::language()).'
				</td>
				<td class="cNav">&nbsp;</td>
			</tr>
		</table>
		<div id="block_2" style="display:none;">
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="cToolbar">
					<div class="cToolbarItem">
						&nbsp;
					</div>
					<div class="cToolbarItem">
						<a href="#" onClick="document.shopArticleMeta.submit()">
						<img src="/images/icons/disk.png" alt="'.$this->Res->html(36, page::language()).'" title="'.$this->Res->html(36, page::language()).'" border="0">
						</a>
					</div>
				</td>
			</tr>
		</table>
		');
	}

	// Formular um neue Metas hinzuzufügen
	public function getArticleMetaAdder(shopArticle $article) {
		return('
		<form name="shopNewMeta" method="post" action="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&addmeta">
			<br>
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td width="140" valign="top">'.$this->Res->html(1069, page::language()).':</td>
					<td width="160" valign="top">
						<select name="sdfID" id="sdfID" style="width:155px;">
							<option value="">'.$this->Res->html(1070, page::language()).'</option>
							'.$this->getFieldOptions($article).'
						</select>
					</td>
					<td width="160" valign="top">
						<input type="hidden" name="newMetaHiddenInfo" id="newMetaHiddenInfo">
						<div id="newMetaInputContainer">
							&nbsp;
						</div>
					</td>
					<td width="100" valign="top">
						<div id="newMetaSubmitContainer" style="display:none">
							<input type="submit" class="cButton" value="'.$this->Res->html(233, page::language()).'">
						</div>
					</td>
				</tr>
			</table>
		</form>
		<br>
		');
	}

	// Liste der aktuellen Metadaten (Nur Löschicons
	public function getArticleMetaList(shopArticle $article) {
		$table = htmlControl::admintable();
		// Tabelle Konfigurieren
		$table->setErrorMessage($this->Res->html(1071,page::language()), 1);
		$table->setPrintIDs(false);
		$table->setLineHeight(20);
		// Tabellenkopf erstellen
		$table->setHead(array(
			new adminTableHead(30, '&nbsp'),
			new adminTableHead(100, 'Feldtyp', 0),
			new adminTableHead(150, 'Feldname', 0),
			new adminTableHead(250, 'Anzeigedaten', 0)
		));
		// Tabellendaten füllen
		$sSQL = 'SELECT tbshopdynamicdata.sdf_ID,tbshopdynamicdata.man_ID,
		sdd_ID,sha_ID,sdd_Value,sdf_Name,sdf_Default,sdf_Type FROM tbshopdynamicdata
		INNER JOIN tbshopdynamicfield ON tbshopdynamicdata.sdf_ID = tbshopdynamicfield.sdf_ID
		WHERE tbshopdynamicdata.sha_ID = '.$article->getShaID().' ORDER BY sdf_Name';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$data = new shopDynamicdata();
			$data->loadRow($row);
			$field = new shopDynamicfield();
			$field->loadRow($row);
			// Zeile erstellen
			$table->addRow($data->getSddID(),array(
				new adminCellDeleteIcon(
					'detail.php?id='.page::menuID().'&a='.$article->getShaID().'&delmeta='.$data->getSddID(),
					$field->getName().' / '.$data->getValue(),
					$this->Res->html(213,page::language()),
					true
				),
				new adminCellText($field->getFieldType()),
				new adminCellText($field->getName()),
				new adminCellText($data->getValue())
			));
		}
		// Formularcode mit generierter Liste ausgeben
		return('
		<form name="shopArticleMeta" method="post" action="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&savemeta">
			'.$table->get().'
		</form>
		</div><!--EndBlock2-->
		');
	}

	// Formular für die Lagerdaten
	public function getArticleStockForm(shopArticle $article) {
		return('
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="cNav" width="20">
					<a href="#" id="open_3">+</a>
				</td>
				<td class="cNavSelected" width="150">
					'.$this->Res->html(1068,page::language()).'
				</td>
				<td class="cNav">&nbsp;</td>
			</tr>
		</table>
		<div id="block_3" style="display:none;">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="cToolbar">
						<div class="cToolbarItem">
							&nbsp;
						</div>
						<div class="cToolbarItem">
							<a href="#" onClick="document.shopArticleStock.submit()">
							<img src="/images/icons/disk.png" border="0"
								alt="'.$this->Res->html(36, page::language()).'"
								title="'.$this->Res->html(36, page::language()).'">
							</a>
						</div>
						<div class="cToolbarItem">
							<img src="/images/icons/page_add.png" border="0" id="addStockarea"
								alt="'.$this->Res->html(425, page::language()).'"
								title="'.$this->Res->html(425, page::language()).'">
						</div>
					</td>
				</tr>
			</table>
		');
	}

	// Window um neue Lagerdaten hinzuzufügen
	public function getArticleStockAdder(shopArticle $article) {
		$window = htmlControl::window();
		$sHtml = $this->getArticleStockAdderWindow($article,$nWidth,$nHeight);
		$sTitle = $this->Res->html(1085,page::language());
		$window->add('addStockarea',$sHtml,$sTitle,$nWidth,$nHeight);
		return($window->get('addStockarea'));
	}

	// Fügt anhand POST Daten eine neue Lagerinfo hinzu
	public function addStockarea(shopArticle $article) {
		$stock = new shopArticleStockarea();
		$stock->setShaID($article->getShaID());
		$stock->setSsaID($_POST['ssaID']);
		$stock->setStock($_POST['sasStock']);
		$stock->setRemark($_POST['sasRemark']);
		$stock->setOntheway($_POST['sasOntheway']);
		$stock->save();
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop article stock for #'.$article->getShaID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Window HTML für neue Lagerverfügbarkeit
	private function getArticleStockAdderWindow(shopArticle $article, &$nWidth, &$nHeight) {
		$areas = $article->getUnusedAreas();
		// Meldung, wenn keine Lagerorte mehr hinzufügbar sind
		if (count($areas) == 0) {
			$nWidth = 350;
			$nHeight = 120;
			return('
			<table width="95%" cellpadding="3" cellspacing="0" border="0">
				<tr><td>'.$this->Res->html(1086, page::language()).'</td></tr>
				<tr><td>
					<input value="'.$this->Res->html(234, page::language()).'"
						type="button" class="cButton" onclick="evtCloseWindow()">
				</td></tr>
			</table>
			');
		}
		$nWidth = 400;
		$nHeight = 210;
		// Ansonsten, Formular für neue Lagerinformation anbieten
		return('
		<form action="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&addstock" method="post">
		<br>
		<table width="95%" cellpadding="3" cellspacing="0" border="0">
			<tr>
				<td width="120">'.$this->Res->html(1088, page::language()).':</td>
				<td>
					<select name="ssaID" style="width:200px;">
						'.$this->getAreaOptions($areas).'
					</select>
				</td>
			</tr>
			<tr>
				<td width="120">'.$this->Res->html(1087, page::language()).':</td>
				<td>
					<input type="text" name="sasRemark" style="width:220px;">
				</td>
			</tr>
			<tr>
				<td width="120">'.$this->Res->html(1089, page::language()).':</td>
				<td>
					<input type="text" name="sasStock" style="width:220px;">
				</td>
			</tr>
			<tr>
				<td width="120">'.$this->Res->html(1090, page::language()).':</td>
				<td>
					<input type="text" name="sasOntheway" style="width:220px;">
				</td>
			</tr>
			<tr>
				<td width="120">&nbsp;</td>
				<td>
					<input type="button" class="cButton" value="'.$this->Res->html(234, page::language()).'" onclick="evtCloseWindow();">
					<input type="submit" class="cButton" value="'.$this->Res->html(233, page::language()).'">
				</td>
			</tr>
		</table>
		</form>
		');
	}

	// Erstellt aus einem shopStockarea Array eine Option Liste
	private function getAreaOptions(array $areas) {
		$sHtml = '';
		foreach ($areas as $area) {
			$sHtml .= '<option value="'.$area->getSsaID().'">'.$area->getName().'</option>';
		}
		return($sHtml);
	}

	// Liste der Lagerverfügbarkeiten dieses Artikels
	public function getArticleStockList(shopArticle $article) {
		$table = htmlControl::admintable();
		// Tabelle Konfigurieren
		$table->setErrorMessage($this->Res->html(1091,page::language()), 1);
		$table->setLineHeight(25);
		// Tabellenkopf erstellen
		$table->setHead(array(
			new adminTableHead(30, '&nbsp'),
			new adminTableHead(140, 'Lager', 1088),
			new adminTableHead(70, 'Anz. gel.', 1092),
			new adminTableHead(70, 'Anz. best.', 1093),
			new adminTableHead(180, 'Bemerkungen', 1087)
		));
		// Tabellendaten füllen
		$sSQL = 'SELECT ssa_Name,sas_ID,sas_Stock,sas_Ontheway,sas_Remark FROM tbshopstockarea
		INNER JOIN tbshoparticle_stockarea ON tbshoparticle_stockarea.ssa_ID = tbshopstockarea.ssa_ID
		WHERE sha_ID = '.$article->getShaID().' ORDER BY ssa_Name';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Zeile hinzufügen
			$table->addRow($row['sas_ID'],array(
				new adminCellDeleteIcon(
					'detail.php?id='.page::menuID().'&a='.$article->getShaID().'&delstock='.$row['sas_ID'],
					$row['ssa_Name'],
					$this->Res->html(213,page::language()),
					true
				),
				new adminCellText($row['ssa_Name']),
				new adminCellInput('stock[]', $row['sas_Stock']),
				new adminCellInput('ontheway[]', $row['sas_Ontheway']),
				new adminCellInput('remark[]', $row['sas_Remark'])
			));
		}
		// Liste zusammen mit Formular zum speichern ausgeben
		return('
		<form name="shopArticleStock" method="post" action="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&savestock">
			<br>
			'.$table->get().'
		</form>
		</div><!--EndBlock3-->
		');
	}

	// Speichert alle Lagerverfügbarkeiten des Artikels
	public function saveStock(shopArticle $article) {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nSasID = getInt($_POST['id'][$i]);
			$stock = new shopArticleStockarea($nSasID);
			$stock->setStock($_POST['stock'][$i]);
			$stock->setOntheway($_POST['ontheway'][$i]);
			$stock->setRemark($_POST['remark'][$i]);
			$stock->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop article stocks for #'.$article->getShaID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Eine Lagerinfo löschen
	public function deleteStock(shopArticle $article) {
		// Daten validieren und löschen
		$stock = new shopArticleStockarea(getInt($_GET['delstock']));
		// Nur wenn die Artikel IDs übereinstimmen
		if ($stock->getShaID() == $article->getShaID()) {
			$stock->delete();
			// Erfolg ausgeben und weiterleiten
			logging::debug('deleted article stock info for #'.$article->getShaID());
			$this->setErrorSession($this->Res->html(57,page::language()));
		} else {
			// Erfolg ausgeben und weiterleiten
			logging::debug('error deleting article stock info for #'.$article->getShaID());
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Speichern und weiterleiten
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Formular für Artikelgruppen
	public function getArticleGroupForm(shopArticle $article) {
		return('
		<form name="shopArticleGroup" method="post" action="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&savegroups">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="cNav" width="20">
						<a href="#" id="open_4">+</a>
					</td>
					<td class="cNavSelected" width="150">
						'.$this->Res->html(1003,page::language()).'
					</td>
					<td class="cNav">&nbsp;</td>
				</tr>
			</table>
			<div id="block_4" style="display:none;">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="cToolbar">
						<div class="cToolbarItem">
							&nbsp;
						</div>
						<div class="cToolbarItem">
							<a href="#" onClick="document.shopArticleGroup.submit()">
							<img src="/images/icons/disk.png" border="0"
								alt="'.$this->Res->html(36, page::language()).'"
								title="'.$this->Res->html(36, page::language()).'">
							</a>
						</div>
					</td>
				</tr>
			</table>
		
		</form>
		');
	}

	// Multiselect aller Artikelgruppen zum Zuweisen
	public function getArticleGroupSelector(shopArticle $article) {
		$Groups = array();
		$Selector = htmlControl::selector();
		$sParam = 'article='.$article->getShaID();
		$Selector->add('groupSelector', $sParam, 'rest/groupselect/', 500, 10);
		// In welchen Gruppen ist der Artikel?
		$PreselectGroups = array();
		$sSQL = 'SELECT sag_ID FROM tbshoparticle_articlegroup
		WHERE sha_ID = '.$article->getShaID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($PreselectGroups,$row['sag_ID']);
		}
		$Groups = array();
		$Container = array();
		// Alle Gruppen laden und lokale zwischenspeichern
		$sSQL = 'SELECT sag_ID,sag_Parent,sag_Title FROM
		tbshoparticlegroup WHERE man_ID = '.page::mandant().'
		ORDER BY sag_Title ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($Groups,$row);
		}
		$this->getGroupsRecursive($Container,$Groups,0,'');
		foreach ($Container as $row) {
			$Selector->addRow(
				'groupSelector',
				$row['sag_ID'],
				$row['sag_Title'],
				in_array($row['sag_ID'],$PreselectGroups)
			);
		}
		// Den Selektor ausgeben inkl. etwas Text
		$out = '<p>'.$this->Res->html(1112, page::language()).'</p>';
		$out.= $Selector->get('groupSelector');
		$out.= '</div><!--EndBlock4-->';
		return($out);
	}

	// Get Groups Recursively
	public function getGroupsRecursive(&$Container,&$Groups,$nSearchID,$sPrefix) {
		// Alle Objekte mit der Gesuchten ID als Parent suchen
		foreach ($Groups as $Group) {
			if ($Group['sag_Parent'] == $nSearchID) {
				// Option erstellen
				$Group['sag_Title'] = $sPrefix.$Group['sag_Title'];
				$Container[] = $Group;
				// Rekursiv Subgruppen suchen
				$this->getGroupsRecursive($Container,$Groups,$Group['sag_ID'],'--'.$sPrefix);
			}
		}
	}

	// Den Artikel in $_GET['a'] laden und zurückgeben
	public function loadArticle() {
		$nShaID = getInt($_GET['a']);
		$article = new shopArticle($nShaID);
		// Mandant prüfen, muss aktueller sein
		if ($article->getManID() != page::mandant()) {
			redirect('location: /error.php?type=noAccess');
		}
		// Wenn nicht, Artikel zurückgeben (Alles OK)
		return($article);
	}

	// Speichert neue Metadaten in den Artikel
	public function addMeta(shopArticle $article) {
		$postdata = stripslashes($_POST['sdfID']);
        $Field = json_decode(str_replace("##",'"',$postdata));
		$Values = $this->getPostedMetaValues($Field,'metaValue');
		// Durchgehen und speichern der Values
		foreach ($Values as $value) {
			$data = new shopDynamicdata();
			$data->setManID(page::mandant());
			$data->setSdfID($Field->sdf_ID);
			$data->setShaID($article->getShaID());
			$data->setValue($value);
			$data->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved new metadata for article #'.$article->getShaID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Speichert neue Metadaten in den Artikel
	public function addSize(shopArticle $article) {
        $size = new shopArticlesize();
		$size->setShaID($article->getShaID());
		$size->setPriceadd(0.00);
		$size->setValue($this->Res->normal(1073,page::language()));
		// Primärflag beim ersten setzen
		$size->setPrimary(0);
		if ($article->countSizes($article) == 0) {
			$size->setPrimary(1);
		}
		$size->save();
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved new metadata for article #'.$article->getShaID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Löscht Metadaten von einem Artikel
	public function deleteMeta(shopArticle $article) {
		// Daten validieren und löschen
		$data = new shopDynamicdata(getInt($_GET['delmeta']));
		if ($data->getShaID() == $article->getShaID()) {
			$data->delete();
			// Erfolg ausgeben und weiterleiten
			logging::debug('deleted metadata for article #'.$article->getShaID());
			$this->setErrorSession($this->Res->html(57,page::language()));
		} else {
			// Erfolg ausgeben und weiterleiten
			logging::debug('error deleting meta for article #'.$article->getShaID());
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Speichern und weiterleiten
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Löscht Artikelgrösse von einem Artikel
	public function deleteSize(shopArticle $article) {
		// Daten validieren und löschen
		$size = new shopArticlesize(getInt($_GET['delsize']));
		if ($size->getShaID() == $article->getShaID()) {
			$size->delete();
			// Erfolg ausgeben und weiterleiten
			logging::debug('deleted size for article #'.$article->getShaID());
			$this->setErrorSession($this->Res->html(57,page::language()));
		} else {
			// Erfolg ausgeben und weiterleiten
			logging::debug('error deleting size for article #'.$article->getShaID());
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Speichern und weiterleiten
		session_write_close();
		redirect('location: /modules/shop/admin/article/detail.php?id='.page::menuID().'&a='.$article->getShaID());
	}

	// Gibt die ID des offenen Block zurück
	public function getOpenBlock() {
		return($this->OpenBlockID);
	}

	// Optionen für die Zusatzfelder auswahl
	public function getFieldOptions(shopArticle $article) {
		$sSQL = 'SELECT IFNULL(sdd_ID,0) AS sdd_ID,tbshopdynamicfield.sdf_ID,sdf_Name,
		sdf_Default,sdf_Type FROM tbshopdynamicfield LEFT JOIN tbshopdynamicdata
		ON tbshopdynamicdata.sdf_ID = tbshopdynamicfield.sdf_ID
		AND tbshopdynamicdata.sha_ID = '.$article->getShaID().'
		WHERE tbshopdynamicfield.man_ID = '.page::mandant();
		// Alle laden, die noch keine ID haben (Unausgewählte Felder)
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			if (getInt($row['sdd_ID']) == 0) {
				$out .= '<option value="'.str_replace('"', "##", json_encode($row)).'">'.$row['sdf_Name'].'</option>';
			}
		}
		return($out);
	}

	// Holt die MetaValues aus Post und Verarbeitet diese
	private function getPostedMetaValues($Field,$name) {
		$Values = array();
		// Array erstellen
		switch (getInt($Field->sdf_Type)) {
			case shopDynamicfield::TYPE_TEXT:
			case shopDynamicfield::TYPE_SINGLE:
				array_push($Values,$_POST[$name]);
				break;
			case shopDynamicfield::TYPE_MULTIPLE:
				foreach ($_POST[$name] as $value) {
					array_push($Values,$value);
				}
				break;
		}
		// Ver ID'te typen ersetzen mit Text aus DB
		// Das ist derzeit nur bei nicht Text-Feldern der Fall
		if (getInt($Field->sdf_Type) != shopDynamicfield::TYPE_TEXT) {
			for ($i = 0;$i < count($Values);$i++) {
				$sSQL = 'SELECT sdv_Value FROM tbshopdynamicvalue
				WHERE sdf_ID = '.$Field->sdf_ID.' AND sdv_ID = '.$Values[$i].'
				ORDER BY sdv_Order ASC';
				$Values[$i] = $this->Conn->getFirstResult($sSQL);
			}
		}
		// Das aufgebaute Array an Values zurückgeben
		return($Values);
	}

	// Window für das Bearbeiten einer Artikelgrösse
	private function getSizeEditWindow(windowControl $window,  shopArticle $article, shopArticlesize $size) {
		$sWindowHtml = '
		<form action="detail.php?id='.page::menuID().'&a='.$article->getShaID().'&savesize" method="post">
		<br>
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
			<tr>
				<td width="160">'.$this->Res->html(1078, page::language()).'</td>
				<td>
					<input type="text" name="sazValue" value="'.$size->getValue().'" style="width:200px;">
				</td>
			</tr>
			<tr>
				<td width="160">'.$this->Res->html(1079, page::language()).'</td>
				<td>
					<input type="text" name="sazPriceadd" value="'.$size->getPriceadd().'" style="width:95px;">
					<input type="text" value="'.$article->getCurrentPrice().'" style="width:95px;" disabled>
				</td>
			</tr>
			<tr>
				<td width="160">&nbsp;</td>
				<td>
					<input type="hidden" name="sazID" value="'.$size->getSazID().'">
					<input type="submit" value="'.$this->Res->html(233, page::language()).'" class="cButton">
				</td>
			</tr>
		</table
		</form>
		';
		// Window erstellen
		$window->add('wndEdit'.$size->getSazID(), $sWindowHtml, 'Artikelgrösse bearbeiten', 400, 150);
		return($window->get('wndEdit'.$size->getSazID()));
	}
}