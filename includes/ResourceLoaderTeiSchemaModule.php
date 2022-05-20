<?php

namespace MediaWiki\Extension\Tei;

use MediaWiki\ResourceLoader as RL;

/**
 * @license GPL-2.0-or-later
 *
 * Provides data about the shape of the TEI XML content
 */
class ResourceLoaderTeiSchemaModule extends RL\Module {

	/**
	 * @param RL\Context $context
	 * @return string
	 */
	public function getScript( RL\Context $context ) {
		$schema = TeiExtension::getDefault()->getCodeMirrorSchemaBuilder()->generateSchema();
		return 'mw.teiEditorSchema = ' . json_encode( $schema ) . ';';
	}

	public function enableModuleContentVersion() {
		return true;
	}
}
