<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use Language;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * Datatype of xml:id
 */
class LanguageDatatype extends Datatype {

	public function __construct() {
		parent::__construct( 'ID' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( Language::isWellFormedLanguageTag( $attributeValue ) ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-language-invalid-value',
				$attributeName, $attributeValue
			);
		}
	}
}
