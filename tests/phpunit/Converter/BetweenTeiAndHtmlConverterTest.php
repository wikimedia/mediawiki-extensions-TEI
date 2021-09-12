<?php

namespace MediaWiki\Extension\Tei\Converter;

use File;
use MediaWiki\Extension\Tei\DOMDocumentFactory;
use PHPUnit\Framework\TestCase;
use TestFileReader;
use ThumbnailImage;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter
 * @covers \MediaWiki\Extension\Tei\Converter\TeiToHtmlConversion
 * @covers \MediaWiki\Extension\Tei\Converter\HtmlToTeiConverter
 * @covers \MediaWiki\Extension\Tei\Converter\HtmlToTeiConversion
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

	public function setUp(): void {
		parent::setUp();

		$this->domDocumentFactory = new DOMDocumentFactory();
		$this->teiToHtmlConverter = new TeiToHtmlConverter( $this->fileLookupMock() );
		$this->htmlToTeiConverter = new HtmlToTeiConverter();
	}

	private function fileLookupMock() {
		/*$services = MediaWikiServices::getInstance();
		return new FileLookup(
			new RepoGroup(
				[
					'class' => MockLocalRepo::class,
					'name' => 'local',
					'url' => 'http://example.com/images',
					'hashLevels' => 2,
					'transformVia404' => false,
					'backend' => new MockFileBackend( [
						'name' => 'local-backend',
						'wikiId' => wfWikiID()
					] )
				],
				[],
				$services->getMainWANObjectCache()
			),
			$services->getBadFileLookup()
		);*/

		$fileMock = $this->getMockBuilder( File::class )
			->disableOriginalConstructor()->getMock();
		$fileMock->expects( $this->any() )
			->method( 'transform' )
			->willReturnCallback( static function ( $params ) use ( $fileMock ) {
				return new ThumbnailImage( $fileMock,  'http://example.com/file/FooBar.jpg', false, $params );
			} );

		$fileLookupMock = $this->getMockBuilder( FileLookup::class )
			->disableOriginalConstructor()->getMock();
		$fileLookupMock->expects( $this->any() )
			->method( 'getFileForPage' )
			->willReturnCallback( static function ( $fileName ) use ( $fileMock ) {
				if ( $fileName === 'FooBar.jpg' ) {
					return $fileMock;
				}
				return null;
			} );
		return $fileLookupMock;
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
