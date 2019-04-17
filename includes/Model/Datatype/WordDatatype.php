<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class WordDatatype extends Datatype {

	public function __construct() {
		parent::__construct( 'teidata.word' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( preg_match( '/^(\p{L}|\p{N}|\p{P}|\p{S})+$/', $attributeValue ) ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-word-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}
