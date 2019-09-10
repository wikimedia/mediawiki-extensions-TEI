<?php

namespace MediaWiki\Extension\Tei\Converter;

use MediaWiki\BadFileLookup;
use MediaWiki\Extension\Tei\DOMDocumentFactory;
use PHPUnit\Framework\TestCase;
use RepoGroup;
use TestFileReader;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter
 * @covers \MediaWiki\Extension\Tei\Converter\HtmlToTeiConverter
 * @covers \MediaWiki\Extension\Tei\Converter\TagMapper
 * @covers \MediaWiki\Extension\Tei\Converter\TagsMappingSerializer
 * @covers \MediaWiki\Extension\Tei\Converter\TeiToHtmlTagMapper
 * @covers \MediaWiki\Extension\Tei\Converter\HtmlToTeiTagMapper
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
		$this->teiToHtmlConverter = new TeiToHtmlConverter(
			$this->repoGroupMock(), $this->badFileLookupMock()
		);
		$this->htmlToTeiConverter = new HtmlToTeiConverter();
	}

	private function repoGroupMock() {
		$repoGroupMock = $this->getMockBuilder( RepoGroup::class )
			->disableOriginalConstructor()->getMock();
		$repoGroupMock->expects( $this->any() )
			->method( 'findFile' )
			->willReturn( false );
		return $repoGroupMock;
	}

	private function badFileLookupMock() {
		$badFileLookup = $this->getMockBuilder( BadFileLookup::class )
			->disableOriginalConstructor()->getMock();
		$badFileLookup->expects( $this->any() )
			->method( 'isBadFile' )
			->willReturn( false );
		return $badFileLookup;
	}

	private function readTestFile( $fileName ) {
		foreach ( TestFileReader::read( __DIR__ . '/' . $fileName )['tests'] as $test ) {
			yield [ $test['desc'], $test['input'], $test['result'] ];
		}
	}

	private function assertTeiToHtmlConversionTest( $testDesc, $tei, $expectedHtml ) {
		$teiDocument = $this->domDocumentFactory->buildFromXMLString( $tei )->getValue();
		$actualHtml = $this->teiToHtmlConverter->convert( $teiDocument )->getHtml();
		$this->assertEquals( $expectedHtml, $actualHtml, $testDesc );
	}

	private function assertHtmlToTeiConversionTest( $testDesc, $expectedTei, $html ) {
		$htmlDocument = $this->domDocumentFactory->buildFromHTMLString( $html )->getValue();
		$actualTei = $this->htmlToTeiConverter->convert( $htmlDocument )->getXml();
		$this->assertEquals( $expectedTei, $actualTei, $testDesc );
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
