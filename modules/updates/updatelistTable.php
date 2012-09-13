<?php
class UpdatelistTable extends Updatelist {
	
// HTML Linkliste ausgeben
	public function appendHtml(&$out) {
		$out .= '<table border="0" width="100%" cellpadding="3" cellspacing="0">';
		foreach ($this->Data as $row) {
			$out .= '
			<tr>
				<td nowrap="nowrap" valign="top">'.$row['lnk_Date'].'</td>
				<td nowrap="nowrap" valign="top">'.$this->getLink($row).'</td>
				<td valign="top">'.$row['lnk_Desc'].'</td>
			</tr>
			';
		}
		$out .= '</table>';
	}
}