<?php

namespace MediaWiki\Extension\Tei;

use ContentHandler;
use MediaWikiTestCase;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\TeiContentHandler
 */
class TeiContentHandlerTest extends MediaWikiTestCase {

	/**
	 * @var ContentHandler
	 */
	private $handler;

	public function setUp() {
		parent::setUp();

		$this->handler = new TeiContentHandler();
	}

	public function testMakeEmptyContent() {
		$content = $this->handler->makeEmptyContent();
		$this->assertTrue( $content->isValid() );
	}
}
