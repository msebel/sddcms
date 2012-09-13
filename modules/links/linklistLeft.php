<?php
class linklistLeft extends linklist {
	
	// HTML Linkliste ausgeben
	public function appendHtml(&$out) {
		$nLastCat = 0;
		$out .= '<table border="0" width="100%" cellpadding="3" cellspacing="0" class="cLinkTable">';
		foreach ($this->Data as $row) {
			if ($nLastCat != $row['lnc_ID']) {
				$out .= '
				<tr>
					<td colspan="2">
						<span class="linkCategoryTitle">'.$row['lnc_Title'].'</span>
					</td>
				</tr>
				';
				$nLastCat = $row['lnc_ID'];
			} 
			$out .= '
			<tr>
				<td nowrap="nowrap" valign="top">'.$this->getLink($row).'</td>
				<td valign="top">'.$row['lnk_Desc'].'</td>
			</tr>
			';
		}
		$out .= '</table>';
	}
}