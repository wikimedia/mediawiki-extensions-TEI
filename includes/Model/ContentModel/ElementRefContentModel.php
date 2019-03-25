<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel;

/**
 * @license GPL-2.0-or-later
 *
 * Validates that there is a single child with the given name
 *
 * @see https://www.tei-c.org/release/doc/tei-p5-doc/en/html/ref-elementRef.html
 */
class ElementRefContentModel extends ContentModel {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @param string $key tag name
	 */
	public function __construct( $key ) {
		$this->key = $key;
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}
}
