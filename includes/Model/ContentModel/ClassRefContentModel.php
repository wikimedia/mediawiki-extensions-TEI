<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel;

/**
 * @license GPL-2.0-or-later
 *
 * Validates that there is a single child with a name in the class
 *
 * @see https://www.tei-c.org/release/doc/tei-p5-doc/en/html/ref-elementRef.html
 */
class ClassRefContentModel extends ContentModel {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @param string $key class name
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
