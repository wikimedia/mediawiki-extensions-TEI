<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use RequestContext;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class EnumerationDatatype extends Datatype {

	/**
	 * @var string[]
	 */
	private $possibleValues;
	/**
	 * @param string ...$possibleValues
	 */
	public function __construct( ...$possibleValues ) {
		parent::__construct( 'teidata.enumerated' );

		$this->possibleValues = $possibleValues;
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( in_array( $attributeValue, $this->possibleValues ) ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-enumeration-unknown-value',
				$attributeValue, $attributeName,
				RequestContext::getMain()->getLanguage()->commaList( $this->possibleValues )
			);
		}
	}
}
