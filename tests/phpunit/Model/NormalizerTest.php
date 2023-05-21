<?php

namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\DOMDocumentFactory;
use PHPUnit\Framework\TestCase;
use Wikimedia\Parsoid\ParserTests\TestFileReader;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\Model\Normalizer
 */
class NormalizerTest extends TestCase {

	/**
	 * @var DOMDocumentFactory
	 */
	private $domDocumentFactory;

	/**
	 * @var Normalizer
	 */
	private $normalizer;

	public function setUp(): void {
		parent::setUp();

		$this->domDocumentFactory = new DOMDocumentFactory();
		$this->normalizer = new Normalizer();
	}

	public static function normalizationProvider() {
		foreach ( TestFileReader::read( __DIR__ . '/normalization.txt' )->testCases as $test ) {
			yield [ $test->testName, $test->wikitext, $test->legacyHtml ];
		}
	}

	/**
	 * @dataProvider normalizationProvider
	 */
	public function testNormalization( $testDesc, $input, $expected ) {
		$dom = $this->domDocumentFactory->buildFromXMLString( $input )->getValue();
		$this->normalizer->normalizeDOM( $dom );
		$actual = $dom->saveXml( $dom->documentElement );
		$this->assertEquals( $expected, $actual, $testDesc );
	}
}
