<?php

namespace MediaWiki\Extension\Tei\Converter;

use MediaWiki\Extension\Tei\DOMDocumentFactory;
use PHPUnit\Framework\TestCase;
use TestFileReader;
use Title;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter
 */
class TeiToHtmlConverterTest extends TestCase {

	/**
	 * @var DOMDocumentFactory
	 */
	private $domDocumentFactory;

	/**
	 * @var TeiToHtmlConverter
	 */
	private $teiToHtmlConverter;

	public function setUp() {
		parent::setUp();

		$this->domDocumentFactory = new DOMDocumentFactory();
		$this->teiToHtmlConverter = new TeiToHtmlConverter();
	}

	private function readTestFile( $fileName ) {
		foreach ( TestFileReader::read( __DIR__ . '/' . $fileName )['tests'] as $test ) {
			yield [ $test['desc'], $test['input'], $test['result'] ];
		}
	}

	private function assertTeiToHtmlConversionTest( $testDesc, $tei, $html ) {
		$this->assertEquals(
			$html,
			$this->teiToHtmlConverter->convertToHtmlBodyContent(
				$this->domDocumentFactory->buildFromXMLString( $tei )->getValue(),
				Title::makeTitle( NS_MAIN, 'Test' )
			),
			$testDesc
		);
	}

	public function roundtripTestProvider() {
		return $this->readTestFile( 'roundtrip.txt' );
	}

	/**
	 * @dataProvider roundtripTestProvider
	 */
	public function testRountripConversion( $testDesc, $tei, $html ) {
		$this->assertTeiToHtmlConversionTest( $testDesc, $tei, $html );
	}

	public function teiToHtmlTestProvider() {
		return $this->readTestFile( 'tei2html.txt' );
	}

	/**
	 * @dataProvider teiToHtmlTestProvider
	 */
	public function testTeiToHtmlConversion( $testDesc, $tei, $html ) {
		$this->assertTeiToHtmlConversionTest( $testDesc, $tei, $html );
	}
}
