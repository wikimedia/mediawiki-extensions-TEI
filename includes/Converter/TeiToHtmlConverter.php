<?php

namespace MediaWiki\Extension\Tei\Converter;

use RemexHtml\Tokenizer\Tokenizer;

/**
 * @license GPL-2.0-or-later
 *
 * Converts TEI to HTML
 */
class TeiToHtmlConverter {

	/**
	 * @param string $input
	 * @return string
	 */
	public function convert( $input ) {
		$serializer = new TagsMappingSerializer( new TeiToHtmlTagMapper() );
		$tokenizer = new Tokenizer( $serializer, $input, [] );
		$tokenizer->execute();
		return $serializer->getOutput();
	}
}
