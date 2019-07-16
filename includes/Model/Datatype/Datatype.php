<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * Definition of a datatype
 */
abstract class Datatype {

	/**
	 * @var string
	 */
	private $ident;

	/**
	 * @param string $ident tag name
	 */
	public function __construct( $ident ) {
		$this->ident = $ident;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->ident;
	}

	/**
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	abstract public function validate( $attributeName, $attributeValue );
}
