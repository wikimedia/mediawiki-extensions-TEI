<?php

namespace MediaWiki\Extension\Tei;

use PHPUnit\Framework\TestCase;
use StatusValue;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\DOMDocumentFactory
 */
class DOMDocumentFactoryTest extends TestCase {

	public function xmlParsingProvider() {
		$cases = [
			[ '', StatusValue::newFatal( 'tei-libxml-empty-document' ) ],
			[ '<text></text>', StatusValue::newGood() ]
		];

		$parsingError = StatusValue::newGood();
		$parsingError->fatal( 'tei-libxml-error-message', 'expected \'>\'', 1 );
		$cases[] = [ '<text><div></div</text>', $parsingError ];

		return $cases;
	}

	/**
	 * @dataProvider xmlParsingProvider
	 */
	public function testXMLParsing( $input, StatusValue $expectedOutput ) {
		$actualOutput = ( new DOMDocumentFactory() )->buildFromXMLString( $input );
		$this->assertEquals( $expectedOutput->isGood(), $actualOutput->isGood() );
		$this->assertEquals( $expectedOutput->isOK(), $actualOutput->isOK() );
		$this->assertEquals( $expectedOutput->getErrors(), $actualOutput->getErrors() );
	}

	public function htmlParsingProvider() {
		$cases = [
			[ '', StatusValue::newFatal( 'tei-libxml-empty-document' ) ],
			[ '<html></html>', StatusValue::newGood() ]
		];
		$parsingError = StatusValue::newGood();
		$parsingError->error( 'tei-libxml-error-message', 'End tag : expected \'>\'', 1 );
		$cases[] = [ '<html></html', $parsingError ];

		return $cases;
	}

	/**
	 * @dataProvider htmlParsingProvider
	 */
	public function testHTMLParsing( $input, StatusValue $expectedOutput ) {
		$actualOutput = ( new DOMDocumentFactory() )->buildFromHTMLString( $input );
		$this->assertEquals( $expectedOutput->isGood(), $actualOutput->isGood() );
		$this->assertEquals( $expectedOutput->isOK(), $actualOutput->isOK() );
		$this->assertEquals( $expectedOutput->getErrors(), $actualOutput->getErrors() );
	}
}
