<?php

namespace MediaWiki\Extension\Tei;

use MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter;
use MediaWiki\Extension\Tei\Model\Normalizer;
use MediaWiki\Extension\Tei\Model\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\TeiExtension
 */
class TeiExtensionTest extends TestCase {

	public function testGetValidator() {
		$this->assertInstanceOf(
			Validator::class,
			TeiExtension::getDefault()->getValidator()
		);
	}

	public function testGetNormalizer() {
		$this->assertInstanceOf(
			Normalizer::class,
			TeiExtension::getDefault()->getNormalizer()
		);
	}

	public function testGetDOMDocumentFactory() {
		$this->assertInstanceOf(
			DOMDocumentFactory::class,
			TeiExtension::getDefault()->getDOMDocumentFactory()
		);
	}

	public function testGetTeiToHtmlConverter() {
		$this->assertInstanceOf(
			TeiToHtmlConverter::class,
			TeiExtension::getDefault()->getTeiToHtmlConverter()
		);
	}
}
