<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class OutputMeasurementDatatype extends Datatype {

	// [\-+]?\d+(\.\d+)?(%|cm|mm|in|pt|pc|px|em|ex|gd|rem|vw|vh|vm)
	// has been removed because not compatible with HTML
	const REGEX = '/^\d+$/';

	public function __construct() {
		parent::__construct( 'teidata.outputMeasurement' );
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
				'tei-validation-output-measurement-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}
