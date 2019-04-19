<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use InvalidArgumentException;

/**
 * @license GPL-2.0-or-later
 *
 * Builds a datatype from its definition
 */
class DatatypeFactory {

	/**
	 * @param array $attribute
	 * @return Datatype
	 */
	public static function build( array $attribute ) {
		if ( array_key_exists( 'key', $attribute['datatype']['dataRef'] ) ) {
			$datatypeId = $attribute['datatype']['dataRef']['key'];
		} elseif ( array_key_exists( 'name', $attribute['datatype']['dataRef'] ) ) {
			$datatypeId = $attribute['datatype']['dataRef']['name'];
		} else {
			throw new InvalidArgumentException(
				'No datatype id found in ' . json_encode( $attribute )
			);
		}

		switch ( $datatypeId ) {
			case 'ID':
				return new IDDatatype();
			case 'teidata.count':
				return new CountDatatype();
			case 'teidata.enumerated':
				return new EnumerationDatatype(
					$attribute['valList']['type'] === 'closed',
					array_keys( $attribute['valList']['items'] )
				);
			case 'teidata.language':
				return new LanguageDatatype();
			case 'teidata.pointer':
				return new PointerDatatype();
			case 'teidata.text':
				return new TextDatatype();
			case 'teidata.word':
				return new WordDatatype();
			default:
				throw new InvalidArgumentException(
					'Not supported datatype for in ' . json_encode( $attribute )
				);
		}
	}
}
