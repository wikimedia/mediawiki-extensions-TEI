<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel;

/**
 * @license GPL-2.0-or-later
 *
 * Validates a sequence of tags
 *
 * @see https://www.tei-c.org/release/doc/tei-p5-doc/en/html/ref-sequence.html
 */
class SequenceContentModel extends ContentModel {

	/**
	 * @var ContentModel[]
	 */
	private $sequence;

	/**
	 * @param ContentModel ...$sequence
	 */
	public function __construct( ...$sequence ) {
		$this->sequence = $sequence;
	}

	/**
	 * @return ContentModel[]
	 */
	public function getSequence() {
		return $this->sequence;
	}
}
