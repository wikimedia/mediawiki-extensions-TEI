<?php

namespace MediaWiki\Extension\Tei;

use MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter;
use MediaWiki\Extension\Tei\Model\DefaultTeiRegistryBuilder;
use MediaWiki\Extension\Tei\Model\Normalizer;
use MediaWiki\Extension\Tei\Model\TeiRegistry;
use MediaWiki\Extension\Tei\Model\Validator;

/**
 * @license GPL-2.0-or-later
 *
 * Global context for the TEI extension
 */
class TeiExtension {

	private $registry;

	private $validator;

	private $normalizer;

	private $domDocumentFactory;

	private $teiToHtmlConverter;

	/**
	 * @return TeiRegistry
	 */
	private function getRegistry() {
		if ( $this->registry === null ) {
			$this->registry = ( new DefaultTeiRegistryBuilder() )->build();
		}
		return $this->registry;
	}

	/**
	 * @return Validator
	 */
	public function getValidator() {
		if ( $this->validator === null ) {
			$this->validator = new Validator( $this->getRegistry() );
		}
		return $this->validator;
	}

	/**
	 * @return Normalizer
	 */
	public function getNormalizer() {
		if ( $this->normalizer === null ) {
			$this->normalizer = new Normalizer();
		}
		return $this->normalizer;
	}

	/**
	 * @return DOMDocumentFactory
	 */
	public function getDOMDocumentFactory() {
		if ( $this->domDocumentFactory === null ) {
			$this->domDocumentFactory = new DOMDocumentFactory();
		}
		return $this->domDocumentFactory;
	}

	/**
	 * @return TeiToHtmlConverter
	 */
	public function getTeiToHtmlConverter() {
		if ( $this->teiToHtmlConverter === null ) {
			$this->teiToHtmlConverter = new TeiToHtmlConverter();
		}
		return $this->teiToHtmlConverter;
	}

	/**
	 * @return TeiExtension
	 */
	public static function getDefault() {
		static $self;
		if ( $self === null ) {
			$self = new TeiExtension();
		}
		return $self;
	}
}
