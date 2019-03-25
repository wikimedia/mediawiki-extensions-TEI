<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel;

/**
 * @license GPL-2.0-or-later
 *
 * Validates a content model that could be repeated
 *
 * @see https://www.tei-c.org/release/doc/tei-p5-doc/en/html/ref-alternate.html
 */
class RepeatableContentModel extends ContentModel {

	/**
	 * @var ContentModel
	 */
	private $element;

	/**
	 * @var int
	 */
	private $minOccurs;

	/**
	 * @var int|null
	 */
	private $maxOccurs;

	/**
	 * @param ContentModel $element
	 * @param int $minOccurs minimal number of occurrences
	 * @param int|null $maxOccurs minimal number of occurrences or null if unbounded
	 */
	public function __construct( ContentModel $element, $minOccurs = 1, $maxOccurs = 1 ) {
		$this->element = $element;
		$this->minOccurs = $minOccurs;
		$this->maxOccurs = $maxOccurs;
	}

	/**
	 * @return ContentModel
	 */
	public function getElement() {
		return $this->element;
	}

	/**
	 * @return int
	 */
	public function getMinOccurs() {
		return $this->minOccurs;
	}

	/**
	 * @return int|null
	 */
	public function getMaxOccurs() {
		return $this->maxOccurs;
	}
}
