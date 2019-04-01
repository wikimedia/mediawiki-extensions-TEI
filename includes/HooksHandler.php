<?php

namespace MediaWiki\Extension\Tei;

use ExtensionRegistry;
use OutputPage;
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
	 * Adds JavaScript to the page
	 *
	 * @param OutputPage $out
	 * @return bool
	 */
	public static function onBeforePageDisplay( OutputPage $out ) {
		if (
			$out->getTitle()->getContentModel() === CONTENT_MODEL_TEI &&
			ExtensionRegistry::getInstance()->isLoaded( 'VisualEditor' )
		) {
			$out->addModules( 'ext.tei.ve.pageTarget.init' );
		}

		return true;
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
