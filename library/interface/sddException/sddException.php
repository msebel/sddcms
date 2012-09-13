<?php
/**
 * Interface Exception, welche den Grundstein für
 * Exceptions innerhalb sddCMS legt
 */
interface sddException  {

	/**
	 * Gibt einen formatierten Stack Trace aus
	 */
	public function getStackTraceFormatted();
}