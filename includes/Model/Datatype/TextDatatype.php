<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class TextDatatype extends Datatype {

	public function __construct() {
		parent::__construct( 'teidata.text' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		return StatusValue::newGood();
	}
}
