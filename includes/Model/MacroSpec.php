<?php

namespace MediaWiki\Extension\Tei\Model;

/**
 * @license GPL-2.0-or-later
 *
 * Definition of a TEI macro
 */
class MacroSpec {

	/**
	 * @var string
	 */
	private $ident;

	/**
	 * @var mixed[]
	 */
	private $data;

	/**
	 * @param string $ident macro name
	 * @param string[] $data the macro spec data
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
	 * @return array
	 */
	public function getContent() {
		return $this->data['content'];
	}
}
