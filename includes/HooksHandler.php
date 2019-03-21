<?php

namespace MediaWiki\Extension\Tei;

use Title;

/**
 * Hooks handler
 *
 * @license GPL-2.0-or-later
 * @author  Thomas Pellissier Tanon
 */
class HooksHandler {

	/**
	 * Extension registration callback
	 */
	public static function onRegister() {
		// Content handler
		define( 'CONTENT_MODEL_TEI', 'tei' );
		define( 'CONTENT_FORMAT_TEI_XML', 'application/tei+xml' );
	}

	/**
	 * Loads CodeEditor
	 *
	 * @param Title $title
	 * @param string &$lang
	 * @param string $model
	 * @param string $format
	 * @return bool
	 */
	public static function onCodeEditorGetPageLanguage( Title $title, &$lang, $model, $format ) {
		if ( $model === CONTENT_MODEL_TEI ) {
			$lang = 'xml';
		}

		return true;
	}
}
