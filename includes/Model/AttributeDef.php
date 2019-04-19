<?php

namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\Model\Datatype\Datatype;
use MediaWiki\Extension\Tei\Model\Datatype\DatatypeFactory;

/**
 * @license GPL-2.0-or-later
 *
 * Definition of an attribute
 */
class AttributeDef {

	/**
	 * @var string
	 */
	private $ident;

	/**
	 * @var mixed[]
	 */
	private $data;

	/**
	 * @param string $ident attribute name
	 * @param string[] $data the attribute spec data
	 */
	public function __construct( $ident, array $data ) {
		$this->ident = $ident;
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->ident;
	}

	/**
	 * @return Datatype
	 */
	public function getDatatype() {
		return DatatypeFactory::build( $this->data );
	}

	/**
	 * @return bool
	 */
	public function isMandatory() {
		return array_key_exists( 'usage', $this->data ) && $this->data['usage'] === 'req';
	}
}
