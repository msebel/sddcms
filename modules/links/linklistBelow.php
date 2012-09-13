<?php
class linklistBelow extends linklist {
	
// HTML Linkliste ausgeben
	public function appendHtml(&$out) {
		$out .= '<div><table border="0" width="100%" cellpadding="3" cellspacing="0" class="cLinkTable">';
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
				<td valign="top">
					'.$this->getLink($row).'<br>
					'.$row['lnk_Desc'].'
				</td>
			</tr>
			';
		}
		$out .= '</table></div>';
	}
}