<?php

namespace MediaWiki\Extension\Tei\Api;

use ApiBase;
use ApiMain;
use ApiUsageException;
use DOMDocument;
use MediaWiki\Extension\Tei\Converter\HtmlToTeiConverter;
use MediaWiki\Extension\Tei\Converter\TeiToHtmlConverter;
use MediaWiki\Extension\Tei\DOMDocumentFactory;
use MediaWiki\Extension\Tei\Model\Normalizer;
use MediaWiki\Extension\Tei\TeiExtension;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Revision\SlotRoleRegistry;
use Title;

/**
 * @license GPL-2.0-or-later
 *
 * API endpoint to convert TEI content
 */
class ApiTeiConvert extends ApiBase {

	private static $supportedContentFormats = [
		CONTENT_FORMAT_HTML,
		CONTENT_FORMAT_TEI_XML
	];

	/**
	 * @var RevisionLookup
	 */
	private $revisionLookup;

	/**
	 * @var SlotRoleRegistry
	 */
	private $slotRoleRegistry;

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

	/**
	 * @var Normalizer
	 */
	private $normalizer;

	/**
	 * ApiConvertTei constructor.
	 * @param ApiMain $mainModule
	 * @param string $moduleName
	 * @param string $modulePrefix
	 */
	public function __construct( ApiMain $mainModule, $moduleName, $modulePrefix = '' ) {
		parent::__construct( $mainModule, $moduleName, $modulePrefix );
		$this->revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		$this->slotRoleRegistry = MediaWikiServices::getInstance()->getSlotRoleRegistry();
		$this->domDocumentFactory = TeiExtension::getDefault()->getDOMDocumentFactory();
		$this->teiToHtmlConverter = TeiExtension::getDefault()->getTeiToHtmlConverter();
		$this->htmlToTeiConverter = TeiExtension::getDefault()->getHtmlToTeiConverter();
		$this->normalizer = TeiExtension::getDefault()->getNormalizer();
	}

	/**
	 * @return bool
	 */
	public function isInternal() {
		return true;
	}

	/**
	 * @throws ApiUsageException
	 */
	public function execute() {
		// Some users may access to pages some others are not allowed to
		$this->getMain()->setCacheMode( 'anon-public-user-private' );

		$params = $this->extractRequestParams();

		// We filter some disallowed combinations
		$this->requireMaxOneParameter( $params, 'pageid', 'revid', 'text' );

		$text = isset( $params['text'] ) ? $params['text'] : null;
		$title = $this->getOptionalTitleFromTitleOrPageId( $params );
		$from = isset( $params['from'] ) ? $params['from'] : null;
		$to = $params['to'];

		if ( $text !== null ) {
			if ( $title === null ) {
				$title = Title::makeTitle( NS_MAIN, 'API' );
			}
		} else {
			$this->requireAtLeastOneParameter( $params, 'title', 'pageid', 'revid' );

			$revId = isset( $params['revid'] ) ? $params['revid'] : 0;
			if ( isset( $params['title'] ) ) {
				$title = $this->parseTitle( $params['title'] );
				$revision = $this->revisionLookup->getRevisionByTitle( $title, $revId );
				if ( $revision === null ) {
					$this->dieWithError( [
						'apierror-missingrev-title', wfEscapeWikiText( $title->getPrefixedText() )
					], 'missingrev' );
				}
			} elseif ( isset( $params['pageid'] ) ) {
				$revision = $this->revisionLookup->getRevisionByPageId( $params['pageid'], $revId );
				if ( $revision === null ) {
					$this->dieWithError( [ 'apierror-missingrev-pageid', $params['pageid'] ], 'missingrev' );
				}
				$title = Title::newFromID( $revision->getPageId() );
			} else {
				$revision = $this->revisionLookup->getRevisionById( $revId );
				if ( $revision === null ) {
					$this->dieWithError( [ 'apierror-missingcontent-revid', $revId ] );
				}
				$title = Title::newFromID( $revision->getPageId() );
			}

			$content = $revision->getContent(
				$params['slot'],
				RevisionRecord::FOR_THIS_USER,
				$this->getUser()
			);
			if ( $content === null ) {
				$this->dieWithError( [ 'apierror-missingcontent-revid', $revision->getId() ] );
			}

			if ( $from === null ) {
				$from = $content->getDefaultFormat();
			}
			if ( !$content->isSupportedFormat( $from ) ) {
				$this->dieWithError( [
					'apierror-badformat', $from, $content->getModel(),
					wfEscapeWikiText( $title->getPrefixedText() )
				] );
			}
			$text = $content->serialize( $from );
		}

		$output = $this->convert( $text, $title, $from, $to, $params['normalize'] );

		$this->getResult()->addValue( null, 'convert', [
			'title' => $title->getFullText(),
			'text' => $output,
			'contentformat' => $to
		] );
	}

	private function getOptionalTitleFromTitleOrPageId( $params ) {
		$this->requireMaxOneParameter( $params, 'title', 'pageid' );

		if ( isset( $params['title'] ) ) {
			$title = Title::newFromText( $params['title'] );
			if ( !$title || $title->isExternal() ) {
				$this->dieWithError( [ 'apierror-invalidtitle', wfEscapeWikiText( $params['title'] ) ] );
			}
			return $title;
		} elseif ( isset( $params['pageid'] ) ) {
			$title = Title::newFromID( $params['pageid'] );
			if ( !$title ) {
				$this->dieWithError( [ 'apierror-nosuchpageid', $params['pageid'] ] );
			}
			return $title;
		}
		return null;
	}

	private function convert( $text, Title $title, $from, $to, $normalize ) {
		switch ( $from ) {
			case CONTENT_FORMAT_TEI_XML:
				return $this->convertFromTei( $text, $title, $to, $normalize );
			case CONTENT_FORMAT_HTML:
				return $this->convertFromHtml( $text, $title, $to, $normalize );
			default:
				$this->dieWithError( [ 'apierror-teiconvert-invalid-fromto', $from, $to ] );
		}
	}

	private function convertFromTei( $text, Title $title, $to, $normalize ) {
		if ( $normalize ) {
			$text = $this->normalizeTeiXml( $text );
		}

		switch ( $to ) {
			case CONTENT_FORMAT_TEI_XML:
				return $text;
			case CONTENT_FORMAT_HTML:
				return $this->teiToHtmlConverter->convert( $text );
			default:
				$this->dieWithError( [ 'apierror-teiconvert-invalid-fromto', CONTENT_FORMAT_TEI_XML, $to ] );
		}
	}

	private function convertFromHtml( $text, Title $title, $to, $normalize ) {
		switch ( $to ) {
			case CONTENT_FORMAT_TEI_XML:
				$text = $this->htmlToTeiConverter->convert( $text );
				if ( $normalize ) {
					$text = $this->normalizeTeiXml( $text );
				}
				return $text;
			case CONTENT_FORMAT_HTML:
				return $text;
			default:
				$this->dieWithError( [ 'apierror-teiconvert-invalid-fromto', CONTENT_FORMAT_TEI_XML, $to ] );
		}

		$this->dieWithError( [ 'apierror-teiconvert-invalid-fromto', $from, $to ] );
	}

	private function parseTitle( $titleText ) {
		$title = Title::newFromText( $titleText );
		if ( $title === null || $title->isExternal() ) {
			$this->dieWithError( [ 'apierror-invalidtitle', wfEscapeWikiText( $titleText ) ] );
		}
		return $title;
	}

	/**
	 * @param string $text
	 * @return DOMDocument
	 */
	private function parseXml( $text ) {
		$status = $this->domDocumentFactory->buildFromXMLString( $text );
		if ( !$status->isOK() ) {
			$this->dieStatus( $status );
		}
		$this->addMessagesFromStatus( $status );
		return $status->getValue();
	}

	private function normalizeTeiXml( $text ) {
		$teiDocument = $this->parseXml( $text );
		$this->normalizer->normalizeDOM( $teiDocument );
		return $teiDocument->saveXML( $teiDocument->documentElement );
	}

	/**
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'text' => [
				ApiBase::PARAM_TYPE => 'text',
			],
			'title' => null,
			'pageid' => [
				ApiBase::PARAM_TYPE => 'integer',
			],
			'revid' => [
				ApiBase::PARAM_TYPE => 'integer',
			],
			'from' => [
				ApiBase::PARAM_TYPE => self::$supportedContentFormats
			],
			'to' => [
				ApiBase::PARAM_TYPE => self::$supportedContentFormats,
				ApiBase::PARAM_REQUIRED => true
			],
			'slot' => [
				ApiBase::PARAM_TYPE => $this->slotRoleRegistry->getKnownRoles(),
				ApiBase::PARAM_DFLT => SlotRecord::MAIN
			],
			'normalize' => [
				ApiBase::PARAM_TYPE => 'boolean'
			]
		];
	}

	/**
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=teiconvert&title=TEI&to=text/html' => 'apihelp-teiconvert-example-from-title-to-html'
		];
	}
}
