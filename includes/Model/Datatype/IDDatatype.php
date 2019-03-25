<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * Datatype of xml:id
 */
class IDDatatype extends Datatype {

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
		// Ids are already validated by the XML parser
		return StatusValue::newGood();
	}
}
