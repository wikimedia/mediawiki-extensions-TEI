<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class MultipleValuesDatatype extends Datatype {

	/**
	 * @var Datatype
	 */
	private $innerDatatype;

	/**
	 * @var int
	 */
	private $minOccurs;

	/**
	 * @var int|null
	 */
	private $maxOccurs;

	/**
	 * @param Datatype $innerDatatype
	 * @param int $minOccurs
	 * @param int|null $maxOccurs
	 */
	public function __construct( Datatype $innerDatatype, $minOccurs, $maxOccurs ) {
		parent::__construct( $innerDatatype->getName() );

		$this->innerDatatype = $innerDatatype;
		$this->minOccurs = $minOccurs;
		$this->maxOccurs = $maxOccurs;
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		$elements = array_filter( explode( ' ', $attributeValue ) );

		if ( count( $elements ) < $this->minOccurs ) {
			return StatusValue::newFatal(
				'tei-validation-multiple-too-few-values',
				$attributeValue, $attributeName, $this->minOccurs, count( $elements )
			);
		}
		if ( $this->maxOccurs !== null && count( $elements ) > $this->maxOccurs ) {
			return StatusValue::newFatal(
				'tei-validation-multiple-too-many-values',
				$attributeValue, $attributeName, $this->maxOccurs, count( $elements )
			);
		}

		$status = StatusValue::newGood();
		foreach ( $elements as $element ) {
			$status->merge( $this->innerDatatype->validate( $attributeName, $element ) );
		}
		return $status;
	}
}
