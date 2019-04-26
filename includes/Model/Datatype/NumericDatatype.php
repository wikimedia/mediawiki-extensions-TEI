<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class NumericDatatype extends Datatype {

	const DOUBLE = '[+-]?(\d+(\.\d*)?|\.\d+)([Ee][+-]?\d+)?|(\+|-)?INF|NaN';
	const DECIMAL = '([+-]?(\d+(\.\d*)?|\.\d+)';
	const RANGE = '(\-?[\d]+/\-?[\d]+)';
	const REGEX = '/^(' . self::DOUBLE . '|' . self::DECIMAL . '|' . self::RANGE . ')$/';

	public function __construct() {
		parent::__construct( 'teidata.numeric' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( preg_match( self::REGEX, $attributeValue ) ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-numeric-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}
