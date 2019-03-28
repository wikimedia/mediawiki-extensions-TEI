<?php

namespace MediaWiki\Extension\Tei\Converter;

use MediaWiki\Extension\Tei\DOMDocumentFactory;
use PHPUnit\Framework\TestCase;
use TestFileReader;
use Title;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter
 * @covers \MediaWiki\Extension\Tei\Converter\HtmlToTeiConverter
 */
class BetweenTeiAndHtmlConverterTest extends TestCase {

	/**
	 * @var DOMDocumentFactory
	 */
	private $domDocumentFactory;

	/**
	 * @var TeiToHtmlConverter
	 */
	private $teiToHtmlConverter;

	/**
	 * @var HtmlToTeiConverter
	 */
	private $htmlToTeiConverter;

	public function setUp() {
		parent::setUp();

		$this->domDocumentFactory = new DOMDocumentFactory();
		$this->teiToHtmlConverter = new TeiToHtmlConverter();
		$this->htmlToTeiConverter = new HtmlToTeiConverter();
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

	private function assertHtmlToTeiConversionTest( $testDesc, $tei, $html ) {
		$this->assertEquals(
			$tei,
			$this->htmlToTeiConverter->convertToTei(
				$this->domDocumentFactory->buildFromXMLString( $html )->getValue()
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
		$this->assertHtmlToTeiConversionTest( $testDesc, $tei, $html );
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

	public function html2teiTestProvider() {
		return $this->readTestFile( 'html2tei.txt' );
	}

	/**
	 * @dataProvider html2teiTestProvider
	 */
	public function testHtmlToTeiConversion( $testDesc, $tei, $html ) {
		$this->assertHtmlToTeiConversionTest( $testDesc, $tei, $html );
	}
}
