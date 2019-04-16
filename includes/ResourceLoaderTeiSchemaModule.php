<?php

namespace MediaWiki\Extension\Tei;

use ResourceLoaderContext;
use ResourceLoaderModule;

/**
 * @license GPL-2.0-or-later
 *
 * Provides data about the shape of the TEI XML content
 */
class ResourceLoaderTeiSchemaModule extends ResourceLoaderModule {

	/**
	 * @param ResourceLoaderContext $context
	 * @return string
	 */
	public function getScript( ResourceLoaderContext $context ) {
		$schema = TeiExtension::getDefault()->getCodeMirrorSchemaBuilder()->generateSchema();
		return 'mw.teiEditorSchema = ' . json_encode( $schema ) . ';';
	}

	public function enableModuleContentVersion() {
		return true;
	}
}
