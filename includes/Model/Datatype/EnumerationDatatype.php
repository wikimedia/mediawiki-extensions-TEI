<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use RequestContext;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class EnumerationDatatype extends Datatype {

	/**
	 * @var boolean
	 */
	private $closed;

	/**
	 * @var string[]
	 */
	private $possibleValues;

	/**
	 * @param bool $closed
	 * @param string[] $possibleValues
	 */
	public function __construct( $closed, array $possibleValues ) {
		parent::__construct( 'teidata.enumerated' );

		$this->closed = $closed;
		$this->possibleValues = $possibleValues;
	}

	/**
	 * @return string[]
	 */
	public function getPossibleValues() {
		return $this->possibleValues;
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( $this->closed ) {
			if ( in_array( $attributeValue, $this->possibleValues ) ) {
				return StatusValue::newGood();
			} else {
				return StatusValue::newFatal(
					'tei-validation-enumeration-unknown-value',
					$attributeValue, $attributeName,
					RequestContext::getMain()->getLanguage()->commaList( $this->possibleValues )
				);
			}
		} else {
			if ( preg_match( '/^(\p{L}|\p{N}|\p{P}|\p{S})+$/', $attributeValue ) ) {
				return StatusValue::newGood();
			} else {
				return StatusValue::newFatal(
					'tei-validation-word-invalid-value',
					$attributeValue, $attributeName
				);
			}

		}
	}
}
