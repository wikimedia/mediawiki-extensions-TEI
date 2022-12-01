<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use LanguageCode;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class LanguageDatatype extends Datatype {

	public function __construct() {
		parent::__construct( 'teidata.language' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( $attributeValue === '' || LanguageCode::isWellFormedLanguageTag( $attributeValue ) ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-language-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}
