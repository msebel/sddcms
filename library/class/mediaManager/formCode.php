<?php
class formCode {
	
	const DROPDOWN_FIELD_DELIMITER = '$$';
	const DROPDOWN_VALUE_DELIMITER = '##';
	
	/**
	 * Ein Textfeld zurückgeben
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getTextfield (&$row,$nCseID) {
		$sValue = $row['ffi_Value'];
		if (isset($_SESSION['form_'.$nCseID][$row['ffi_Name']])) {
			$sValue = $_SESSION['form_'.$nCseID][$row['ffi_Name']];
		}
		$style = '';
		if (getInt($row['ffi_Width']) > 0)
			$style = 'style="width:'.getInt($row['ffi_Width']).'px"';
		$out = '
		<tr>
			<td width="25%" valign="top" nowrap>'.$row['ffi_Desc'].'
			'.self::printRequired($row['ffi_Required']).'</td>
			<td width="75%" valign="top">
				<input 
					type="'.$row['ffi_Type'].'" 
					value="'.$sValue.'" 
					name="'.$row['ffi_Name'].'_'.$nCseID.'" 
					'.$style.'
					class="'.$row['ffi_Class'].'" 
				/>
			</td>
		</tr>
		';
		return($out);
	}
	
	/**
	 * Eine Textarea zurückgeben
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getTextarea (&$row,$nCseID) {
		$sValue = $row['ffi_Value'];
		if (isset($_SESSION['form_'.$nCseID][$row['ffi_Name']])) {
			$sValue = $_SESSION['form_'.$nCseID][$row['ffi_Name']];
		}
		$style = '';
		if (getInt($row['ffi_Width']) > 0)
			$style = 'style="width:'.getInt($row['ffi_Width']).'px"';
		$out = '
		<tr>
			<td width="25%" valign="top" nowrap>'.$row['ffi_Desc'].'
			'.self::printRequired($row['ffi_Required']).'</td>
			<td width="75%" valign="top">
				<textarea 
					name="'.$row['ffi_Name'].'_'.$nCseID.'" 
					rows="5" '.$style.'
					class="'.$row['ffi_Class'].'" 
				>'.$sValue.'</textarea>
			</td>
		</tr>
		';		
		return($out);
	}
	
	/**
	 * Einen Radiobutton zurückgeben
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getRadio(&$row,$nCseID) {
		$sChecked = '';
		if ($_SESSION['form_'.$nCseID][$row['ffi_Name']] == $row['ffi_Value']) {
			$sChecked = ' checked="checked"';
		}
		$out = '
		<tr>
			<td width="25%" valign="top" nowrap>
				&nbsp;
			</td>
			<td width="75%" valign="top">
				<input '.$sChecked.'
					type="radio" 
					name="'.$row['ffi_Name'].'_'.$nCseID.'" 
					value="'.$row['ffi_Value'].'" 
				/> '.$row['ffi_Desc'].' '.self::printRequired($row['ffi_Required']).'
			</td>
		</tr>
		';		
		return($out);
	}
	
	/**
	 * Eine Checkbox zurückgeben
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getCheckbox(&$row,$nCseID) {
		$sChecked = '';
		if (is_string($_SESSION['form_'.$nCseID][$row['ffi_Name']])) {
			$sChecked = ' checked="checked"';
		}
		$out = '
		<tr>
			<td width="25%" valign="top" nowrap>
				&nbsp;
			</td>
			<td width="75%" valign="top">
				<input '.$sChecked.'
					type="checkbox" 
					name="'.$row['ffi_Name'].'_'.$nCseID.'" 
					value="'.$row['ffi_Value'].'" 
				/> '.$row['ffi_Desc'].' '.self::printRequired($row['ffi_Required']).'
			</td>
		</tr>
		';		
		return($out);
	}
	
	/**
	 * Ein verstecktes Feld zurückgeben
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getHidden(&$row,$nCseID) {
		$sValue = $row['ffi_Value'];
		if (isset($_SESSION['form_'.$nCseID][$row['ffi_Name']])) {
			$sValue = $_SESSION['form_'.$nCseID][$row['ffi_Name']];
		}
		$out = '
		<input 
			type="hidden" 
			name="'.$row['ffi_Name'].'_'.$nCseID.'" 
			value="'.$sValue.'" 
		/>
		';		
		return($out);
	}
	
	/**
	 * Antispam Field und Submitfeld holen
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getSubmit(&$row,$nCseID) {
		$style = '';
		if (getInt($row['ffi_Width']) > 0)
			$style = 'style="width:'.getInt($row['ffi_Width']).'px"';
		$out = '
		<tr>
			<td width="25%" valign="top">
				&nbsp;
				<div style="display:none;visibility:hidden">
					<input 
						type="text" 
						name="dummyfield_'.$nCseID.'" 
						value="" 
						style="width:0px;height:0px;border:0px none;"
					/>
				</div>
			</td>
			<td width="75%" valign="top">
				<input 
					type="submit" 
					class="'.$row['ffi_Class'].'"
					name="cmdSubmit_'.$nCseID.'" 
					'.$style.'
					value="'.$row['ffi_Desc'].'" 
				/> 
			</td>
		</tr>
		';		
		return($out);
	}
	
	/**
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getDropdown(&$row,$nCseID) {
		$OptionText = '';
		if (strlen($row['ffi_Options']) > 0) {
			$OptionPair = explode(formCode::DROPDOWN_FIELD_DELIMITER,$row['ffi_Options']);
			foreach ($OptionPair as $PairText) {
				$NameValue = explode(formCode::DROPDOWN_VALUE_DELIMITER,$PairText);
				$sChecked = '';
				if ($_SESSION['form_'.$nCseID][$row['ffi_Name']] == $NameValue[0].' ('.$NameValue[1].')') {
					$sChecked = ' selected="selected"';
				}
				$OptionText .= '<option value="'.$NameValue[0].' ('.$NameValue[1].')"'.$sChecked.'>'.$NameValue[1].'</option>';
			}
		}
		$style = '';
		if (getInt($row['ffi_Width']) > 0)
			$style = 'style="width:'.getInt($row['ffi_Width']).'px"';
		$out = '
		<tr>
			<td width="25%" valign="top" nowrap>
				'.$row['ffi_Desc'].'
			</td>
			<td width="75%" valign="top">
				<select name="'.$row['ffi_Name'].'_'.$nCseID.'" 
					'.$style.'
					class="'.$row['ffi_Class'].'"> 
					'.$OptionText.'
				</select> '.self::printRequired($row['ffi_Required']).'
			</td>
		</tr>
		';		
		return($out);
	}
	
	/**
	 * Ein Captcha zurückgeben
	 * @param array row, Datenzeile aus der Formulartabelle
	 * @param integer nCseID, Zugehörige Contentsection
	 * @return string HTML Code für das Formularelement
	 */
	public static function getCaptcha (&$row,$nCseID) {
		$style = '';
		if (getInt($row['ffi_Width']) > 0)
			$style = 'style="width:'.getInt($row['ffi_Width']).'px"';
		$out = '
		<tr>
			<td width="25%" valign="top" nowrap>'.$row['ffi_Desc'].'
			'.self::printRequired($row['ffi_Required']).'</td>
			<td width="75%" valign="top">
				<img src="/scripts/captcha/code.php" id="captchaImage" /><br>
				<input 
					type="text" 
					name="captchaCode"
					value="" 
					id="'.$row['ffi_Name'].'_'.$nCseID.'" 
					'.$style.'
					class="'.$row['ffi_Class'].'" 
				/>
				<img onclick="captchaReload()" src="/images/icons/arrow_refresh_small.png"/>
			</td>
		</tr>
		';
		return($out);
	}
	
	/**
	 * Einen roten Stern ausgeben, wenn das Formular zwingend ist
	 * @param integer nRequired, wenn 1, dann ist das Feld zwingend
	 * @return string Span mit rotem Stern und Whitespace davor
	 */
	public static function printRequired($nRequired) {
		$out = '';
		if (getInt($nRequired) == 1) {
			$out = ' <span style="color:#d00">*</span>';
		}
		return($out);
	}
	
	/**
	 * emailForm Error Session ausgeben und resetten
	 * @param integer nCseID, zugehörende Contentsection
	 * @return string HTML Liste mit Fehlermeldungen
	 */
	public static function getErrorMessages($nCseID) {
		$out = '';
		foreach ($_SESSION['emailFormError_'.$nCseID] as $sError) {
			$out .= '
			<span class="cEmailError">'.$sError.'</span><br>
			';
		}
		if (strlen($out) > 0) $out .= '<br>';
		unset($_SESSION['emailFormError_'.$nCseID]);
		return($out);
	}
}