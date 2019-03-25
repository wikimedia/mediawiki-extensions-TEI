<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel;

/**
 * @license GPL-2.0-or-later
 *
 * Validates an alternative of tags
 *
 * @see https://www.tei-c.org/release/doc/tei-p5-doc/en/html/ref-alternate.html
 */
class AlternateContentModel extends ContentModel {

	/**
	 * @var ContentModel[]
	 */
	private $alternate;

	/**
	 * @param ContentModel ...$alternate
	 */
	public function __construct( ...$alternate ) {
		$this->alternate = $alternate;
	}

	/**
	 * @return ContentModel[]
	 */
	public function getAlternate() {
		return $this->alternate;
	}
}
