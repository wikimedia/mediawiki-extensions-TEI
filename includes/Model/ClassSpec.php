<?php

namespace MediaWiki\Extension\Tei\Model;

/**
 * @license GPL-2.0-or-later
 *
 * Definition of a TEI class, i.e. a set of tags
 */
class ClassSpec {

	/**
	 * @var string
	 */
	private $ident;

	/**
	 * @var string[]
	 */
	private $superClasses;

	/**
	 * @var AttributeDef[]
	 */
	private $attributes;

	/**
	 * @param string $ident tag name
	 * @param string[] $superClasses the super classes of the class
	 * @param AttributeDef[] $attributes
	 */
	public function __construct( $ident, array $superClasses, array $attributes ) {
		$this->ident = $ident;
		$this->superClasses = $superClasses;
		$this->attributes = $attributes;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->ident;
	}

	/**
	 * @return string[]
	 */
	public function getSuperClasses() {
		return $this->superClasses;
	}

	/**
	 * @return AttributeDef[]
	 */
	public function getAttributes() {
		return $this->attributes;
	}
}
