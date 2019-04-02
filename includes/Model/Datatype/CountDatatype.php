<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * The value should be a positive integer
 */
class CountDatatype extends Datatype {

	public function __construct() {
		parent::__construct( 'teidata.count' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( (int)$attributeValue && $attributeValue > 0 ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-count-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}
