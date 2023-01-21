<?php

namespace MediaWiki\Extension\Tei;

use PHPUnit\Framework\TestCase;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\TeiContent
 */
class TeiContentTest extends TestCase {

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
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0"> <body><p>Foo</p></body> </text>' )
			],
			[
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0"><front></front>
									 <body><div><p>Foo</p></div></body><back></back></text>' )
			],
			[
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0">
									  <body><p xml:id="p1" xml:lang="en">Foo</p></body> </text>' )
			],
		];
	}

	/**
	 * @dataProvider isValidProvider
	 */
	public function testIsValid( TeiContent $content ) {
		$this->assertTrue( $content->isValid() );
	}

	public function isNotValidProvider() {
		return [
			[
				new TeiContent( 'foo' )
			],
			[
				new TeiContent( '<text>' )
			],
			[
				new TeiContent( '<text><body><p>Foo</p></body> </text>' )
			],
			[
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0">
									  <body><p xml:lang="l ">Foo</p></body> </text>' )
			],
			[
				new TeiContent( '<text xmlns="http://www.tei-c.org/ns/1.0">
									  <body><p xml:foo="bar">Foo</p></body> </text>' )
			],
		];
	}

	/**
	 * @dataProvider isNotValidProvider
	 */
	public function testIsNotValid( TeiContent $content ) {
		$this->assertFalse( $content->isValid() );
	}
}
