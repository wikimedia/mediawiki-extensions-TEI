<?php

namespace MediaWiki\Extension\Tei\Converter;

use RemexHtml\Tokenizer\Tokenizer;

/**
 * @license GPL-2.0-or-later
 *
 * Converts HTML to TEI
 */
class HtmlToTeiConverter {

	/**
	 * @param string $input
	 * @return string
	 */
	public function convert( $input ) {
		$serializer = new TagsMappingSerializer( new HtmlToTeiTagMapper() );
		$tokenizer = new Tokenizer( $serializer, $input, [] );
		$tokenizer->execute();
		return $serializer->getOutput();
	}
}
