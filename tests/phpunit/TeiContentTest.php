<?php

namespace MediaWiki\Extension\Tei;

use MediaWikiTestCase;
use ParserOptions;
use Title;
use User;
use WikiPage;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\TeiContent
 */
class TeiContentTest extends MediaWikiTestCase {

	public function testGetModel() {
		$content = new TeiContent( '' );
		$this->assertEquals( CONTENT_MODEL_TEI, $content->getModel() );
	}

	public function testGetContentHandler() {
		$content = new TeiContent( '' );
		$this->assertEquals(
			CONTENT_MODEL_TEI, $content->getContentHandler()->getModelID()
		);
	}

	public function testCopy() {
		$content = new TeiContent( 'foo bar' );
		$this->assertEquals( $content, $content->copy() );
	}

	public function isValidProvider() {
		return [
			[
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0"><body><p>Foo</p></body></text>' )
			]
		];
	}

	/**
	 * @dataProvider isValidProvider
	 */
	public function testIsValid( TeiContent $content ) {
		$this->assertTrue( $content->isValid() );
	}

	/**
	 * @dataProvider isValidProvider
	 */
	public function testPrepareSaveValid( TeiContent $content ) {
		$page = WikiPage::factory( Title::makeTitle( NS_MAIN, 'Foo' ) );
		$user = User::newFromName( 'Foo' );
		$status = $content->prepareSave( $page, 0, -1, $user );
		$this->assertTrue( $status->isGood() );
	}

	public function isNotValidProvider() {
		return [
			[
				new TeiContent( 'foo' )
			],
			[
				new TeiContent( '<text>' )
			]
		];
	}

	/**
	 * @dataProvider isNotValidProvider
	 */
	public function testIsNotValid( TeiContent $content ) {
		$this->assertFalse( $content->isValid() );
	}

	/**
	 * @dataProvider isNotValidProvider
	 */
	public function testPrepareSaveNotValid( TeiContent $content ) {
		$page = WikiPage::factory( Title::makeTitle( NS_MAIN, 'Foo' ) );
		$user = User::newFromName( 'Foo' );
		$status = $content->prepareSave( $page, 0, -1, $user );
		$this->assertFalse( $status->isOK() );
	}

	public function preSaveTransformProvider() {
		return [
			[
				new TeiContent( 'foo' ),
				new TeiContent( 'foo' )
			],
			[
				new TeiContent( '<text><body><p>Foo</p></body></text>' ),
				new TeiContent( "<text>\n  <body>\n    <p>Foo</p>\n  </body>\n</text>" )
			],
			[
				new TeiContent( '<?xml version="1.0" encoding="UTF-8"?><text><body><p>Foo</p></body></text>' ),
				new TeiContent( "<text>\n  <body>\n    <p>Foo</p>\n  </body>\n</text>" )
			]
		];
	}

	/**
	 * @dataProvider preSaveTransformProvider
	 */
	public function testPreSaveTransform( TeiContent $input, TeiContent $output ) {
		$title = Title::makeTitle( NS_MAIN, 'Foo' );
		$user = User::newFromName( 'Foo' );
		$actual = $input->preSaveTransform( $title, $user, ParserOptions::newCanonical() );
		$this->assertEquals( $output->serialize(), $actual->serialize() );
	}
}
