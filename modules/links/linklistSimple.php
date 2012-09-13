<?php
class linklistSimple extends linklist {
	
// HTML Linkliste ausgeben
	public function appendHtml(&$out) {
		$out .= '<table border="0" width="100%" cellpadding="3" cellspacing="0" class="cLinkTable">';
		foreach ($this->Data as $row) {
			if ($nLastCat != $row['lnc_ID']) {
				$out .= '
				<tr>
					<td>
						<span class="linkCategoryTitle">'.$row['lnc_Title'].'</span>
					</td>
				</tr>
				';
				$nLastCat = $row['lnc_ID'];
			} 
			$out .= '
			<tr>
				<td nowrap="nowrap">'.$this->getLink($row).'</td>
			</tr>
			';
		}
		$out .= '</table>';
	}
}