<?php

namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\Model\Datatype\Datatype;

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
	 * @var Datatype
	 */
	private $datatype;

	/**
	 * @var bool
	 */
	private $isMandatory;

	/**
	 * @param string $ident attribute name
	 * @param Datatype $datatype the value datatype
	 * @param bool $isMandatory
	 */
	public function __construct( $ident, Datatype $datatype, $isMandatory = false ) {
		$this->ident = $ident;
		$this->datatype = $datatype;
		$this->isMandatory = $isMandatory;
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
		return $this->datatype;
	}

	/**
	 * @return bool
	 */
	public function isMandatory() {
		return $this->isMandatory;
	}
}
