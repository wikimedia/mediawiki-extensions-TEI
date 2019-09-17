<?php

namespace MediaWiki\Extension\Tei;

use MediaWiki\Extension\Tei\Converter\FileLookup;
use MediaWiki\Extension\Tei\Converter\HtmlToTeiConverter;
use MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter;
use MediaWiki\Extension\Tei\Model\CodeMirrorSchemaBuilder;
use MediaWiki\Extension\Tei\Model\Normalizer;
use MediaWiki\Extension\Tei\Model\TeiRegistry;
use MediaWiki\Extension\Tei\Model\Validator;
use MediaWiki\MediaWikiServices;

/**
 * @license GPL-2.0-or-later
 *
 * Global context for the TEI extension
 */
class TeiExtension {

	const DEFINITION_FILE_PATH = __DIR__ . '/../data/mw_tei_json_definition.json';

	private $registry;

	private $validator;

	private $normalizer;

	private $domDocumentFactory;

	private $teiToHtmlConverter;

	private $htmlToTeiConverter;

	private $codeMirrorSchemaBuilder;

	/**
	 * @return TeiRegistry
	 */
	private function getRegistry() {
		if ( $this->registry === null ) {
			$this->registry = new TeiRegistry( json_decode( file_get_contents(
				self::DEFINITION_FILE_PATH
			), true ) );
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
			$services = MediaWikiServices::getInstance();
			$this->teiToHtmlConverter = new TeiToHtmlConverter( new FileLookup(
				$services->getRepoGroup(), $services->getBadFileLookup()
			) );
		}
		return $this->teiToHtmlConverter;
	}

	/**
	 * @return HtmlToTeiConverter
	 */
	public function getHtmlToTeiConverter() {
		if ( $this->htmlToTeiConverter === null ) {
			$this->htmlToTeiConverter = new HtmlToTeiConverter();
		}
		return $this->htmlToTeiConverter;
	}

	/**
	 * @return CodeMirrorSchemaBuilder
	 */
	public function getCodeMirrorSchemaBuilder() {
		if ( $this->codeMirrorSchemaBuilder === null ) {
			$this->codeMirrorSchemaBuilder = new CodeMirrorSchemaBuilder( $this->getRegistry() );
		}
		return $this->codeMirrorSchemaBuilder;
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
