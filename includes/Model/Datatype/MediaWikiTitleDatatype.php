<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use MalformedTitleException;
use MediaWiki\Title\Title;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class MediaWikiTitleDatatype extends Datatype {

	public function __construct() {
		parent::__construct( 'mw.title' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		try {
			Title::newFromTextThrow( $attributeValue );
			return StatusValue::newGood();
		} catch ( MalformedTitleException $e ) {
			return StatusValue::newFatal( $e->getErrorMessage(), ...$e->getErrorMessageParameters() );
		}
	}
}
