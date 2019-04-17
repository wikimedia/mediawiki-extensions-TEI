<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class PointerDatatype extends Datatype {

	public function __construct() {
		parent::__construct( 'teidata.pointer' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( filter_var( $attributeValue, FILTER_VALIDATE_URL ) ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-pointer-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}
