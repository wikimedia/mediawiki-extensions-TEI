<?php

namespace MediaWiki\Extension\Tei;

use ExtensionRegistry;
use OutputPage;

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
		if ( $out->getTitle()->getContentModel() !== CONTENT_MODEL_TEI ) {
			return true;
		}
		$action = $out->getRequest()->getVal( 'action' );
		$isEdit = ( $action === 'edit' || $action === 'submit' );

		if ( !$isEdit && ExtensionRegistry::getInstance()->isLoaded( 'VisualEditor' ) ) {
			$out->addModules( 'ext.tei.ve.pageTarget.init.mw' );
		}

		if ( $isEdit && ExtensionRegistry::getInstance()->isLoaded( 'CodeMirror' ) ) {
			$out->addModules( 'ext.tei.editor' );
		}

		return true;
	}
}
