<?php

namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\DOMDocumentFactory;
use PHPUnit\Framework\TestCase;
use TestFileReader;

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

	public function setUp() {
		parent::setUp();

		$this->domDocumentFactory = new DOMDocumentFactory();
		$this->normalizer = new Normalizer();
	}

	public function normalizationProvider() {
		foreach ( TestFileReader::read( __DIR__ . '/normalization.txt' )['tests'] as $test ) {
			yield [ $test['desc'], $test['input'], $test['result'] ];
		}
	}

	/**
	 * @dataProvider normalizationProvider
	 */
	public function testRountripConversion( $testDesc, $input, $expected ) {
		$dom = $this->domDocumentFactory->buildFromXMLString( $input )->getValue();
		$this->normalizer->normalizeDOM( $dom );
		$actual = $dom->saveXml( $dom->documentElement );
		$this->assertEquals( $expected, $actual, $testDesc );
	}
}
