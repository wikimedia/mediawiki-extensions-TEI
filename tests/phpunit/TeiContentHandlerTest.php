<?php

namespace MediaWiki\Extension\Tei;

use ContentHandler;
use MediaWiki\Page\PageReferenceValue;
use MediaWiki\User\UserIdentityValue;
use MediaWikiIntegrationTestCase;
use ParserOptions;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\TeiContentHandler
 */
class TeiContentHandlerTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var ContentHandler
	 */
	private $handler;

	public function setUp(): void {
		parent::setUp();

		$this->handler = new TeiContentHandler();
	}

	public function testMakeEmptyContent() {
		$content = $this->handler->makeEmptyContent();
		$this->assertTrue( $content->isValid() );
	}

	public function providePreSaveTransform() {
		return [
			[
				new TeiContent( 'foo' ),
				new TeiContent( 'foo' )
			],
			[
				new TeiContent( '<text  xmlns="http://www.tei-c.org/ns/1.0">
									  <body> <p>Foo</p></body>  </text>' ),
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0">
									  <body> <p>Foo</p></body>  </text>' )
			],
			[
				new TeiContent( '<text><body><p>Foo</p></body></text>' ),
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0"><body><p>Foo</p></body></text>' )
			],
			[
				new TeiContent( '<?xml version="1.0" encoding="UTF-8"?>
						              <text  xmlns="http://www.tei-c.org/ns/1.0"><body><p>Foo</p></body></text>' ),
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0"><body><p>Foo</p></body></text>' )
			]
		];
	}

	/**
	 * @dataProvider providePreSaveTransform
	 */
	public function testPreSaveTransform( TeiContent $input, TeiContent $output ) {
		$contentTransformer = $this->getServiceContainer()->getContentTransformer();
		$page = PageReferenceValue::localReference( NS_MAIN, 'Foo' );
		$user = UserIdentityValue::newRegistered( 123, 'Foo' );
		$actual = $contentTransformer->preSaveTransform(
			$input,
			$page,
			$user,
			ParserOptions::newFromUser( $user )
		);

		$this->assertEquals( $output->serialize(), $actual->serialize() );
	}
}
