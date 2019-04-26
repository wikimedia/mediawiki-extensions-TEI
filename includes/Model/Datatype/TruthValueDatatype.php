<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class TruthValueDatatype extends Datatype {

	const REGEX = '/^(0|1|false|true)$/';

	public function __construct() {
		parent::__construct( 'teidata.truthValue' );
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
				'tei-validation-truth-value-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}
