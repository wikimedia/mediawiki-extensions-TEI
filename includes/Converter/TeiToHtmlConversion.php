<?php

namespace MediaWiki\Extension\Tei\Converter;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use File;
use Linker;
use RemexHtml\HTMLData;
use RemexHtml\Serializer\HtmlFormatter;
use RepoGroup;
use Sanitizer;
use Title;
use User;

/**
 * @license GPL-2.0-or-later
 *
 * A conversion from TEI to HTML
 */
class TeiToHtmlConversion {

	const NODE_NAME = 'nodeName';
	const VALUE_FUNCTION = 'value-function';
	const TEI_TAG_NAME = 'data-tei-tag';
	const TEI_CONTENT = 'data-tei-content';

	private static $tagsMapping = [
		'ab' => 'div',
		'abbr' => 'abbr',
		'add' => 'ins',
		'actor' => 'span',
		'address' => 'address',
		'addrLine' => 'p',
		'anchor' => 'span',
		'argument' => 'div',
		'author' => 'span',
		'back' => 'footer',
		'bibl-parent-listBibl' => 'li',
		'bibl' => 'cite',
		'biblScope' => 'span',
		'body' => 'section',
		'byline' => 'div',
		'cell-role-label' => 'th',
		'cell-role-data' => 'td',
		'cell' => 'td',
		'choice' => 'span',
		'cit' => 'blockquote',
		'closer' => 'div',
		'corr' => 'span',
		'date' => 'time',
		'dateline' => 'div',
		'desc' => 'span',
		'del' => 'del',
		'div' => 'section',
		'div1' => 'section',
		'div2' => 'section',
		'div3' => 'section',
		'div4' => 'section',
		'div5' => 'section',
		'div6' => 'section',
		'docAuthor' => 'span',
		'docDate' => 'time',
		'docEdition' => 'span',
		'docImprint' => 'span',
		'docTitle' => 'div',
		'epigraph' => 'div',
		'expan' => 'span',
		'figDesc' => 'figcaption',
		'figure' => 'figure',
		'formula-notation-tex' => [
			self::NODE_NAME => 'span',
			self::VALUE_FUNCTION => 'convertTexFormula'
		],
		'formula' => 'span',
		'foreign' => 'span',
		'front' => 'header',
		'gap' => 'span',
		'graphic' => 'img',
		'group' => 'section',
		'head' => 'h1',
		'hi' => 'span',
		'hi-rend-bold' => 'b',
		'hi-rend-italic' => 'i',
		'hi-rend-sub' => 'sub',
		'hi-rend-sup' => 'sup',
		'hi-rend-small' => 'small',
		'hi-rend-var' => 'var',
		'imprimatur' => 'div',
		'item' => 'li',
		'l' => 'div',
		'label' => 'span',
		'lb' => 'br',
		'lg' => 'div',
		'list' => 'ul',
		'list-type-ordered' => 'ol',
		'list-type-unordered' => 'ul',
		'listBibl' => 'ul',
		'milestone' => 'span',
		'name' => 'span',
		'note' => [
			self::NODE_NAME => 'a',
			self::VALUE_FUNCTION => 'convertNote'
		],
		'num' => 'span',
		'opener' => 'div',
		'p' => 'p',
		'orig' => 'span',
		'pb' => 'span',
		'pc' => 'span',
		'place' => 'span',
		'postscript' => 'div',
		'q' => 'q',
		'ref' => 'a',
		'reg' => 'span',
		'relatedItem' => 'span',
		'row' => 'tr',
		'rs' => 'span',
		's' => 'span',
		'salute' => 'div',
		'seg' => 'span',
		'sic' => 'span',
		'signed' => 'div',
		'sp' => 'div',
		'speaker' => 'div',
		'stage' => 'div',
		'table' => 'table',
		'term' => 'span',
		'text' => 'article',
		'time' => 'time',
		'title' => 'span',
		'titlePage' => 'div',
		'titlePart' => 'div',
		'trailer' => 'div',
		'unclear' => 'span',
		'w' => 'span'
	];

	private static $attributesMapping = [
		'cols' => 'colspan',
		'height' => 'height',
		'rend' => [
			self::VALUE_FUNCTION => 'convertRend'
		],
		'rows' => 'rowspan',
		'style' => [
			self::VALUE_FUNCTION => 'convertStyle'
		],
		'target' => [
			self::VALUE_FUNCTION => 'convertTarget'
		],
		'targetLang' => 'hreflang',
		'url' => [
			self::VALUE_FUNCTION => 'convertUrl'
		],
		'when' => 'datetime',
		'width' => 'width',
		'xml:id' => 'id',
		'xml:lang' => 'lang',
	];

	/**
	 * @var DOMDocument
	 */
	private $teiDocument;

	/**
	 * @var Title|null
	 */
	private $pageTitle;

	/**
	 * @var DOMDocument
	 */
	private $htmlDocument;

	/**
	 * @var DOMElement[]
	 */
	private $notes = [];

	/**
	 * @var int
	 */
	private $notesCounter = 1;

	/**
	 * @var string[]
	 */
	private $externalLinksUrls = [];

	/**
	 * @var File[]
	 */
	private $includedFiles = [];

	/**
	 * @var string[]
	 */
	private $warnings = [];

	/**
	 * @param DOMDocument $teiDocument
	 * @param Title|null $pageTitle
	 */
	public function __construct( DOMDocument $teiDocument, Title $pageTitle = null ) {
		$this->teiDocument = $teiDocument;
		$this->pageTitle = $pageTitle;
		$this->htmlDocument = new DOMDocument( '1.0', 'UTF-8' );

		$root = $this->convertNode( $this->teiDocument->documentElement, 0 );
		$this->htmlDocument->appendChild( $root );
		foreach ( $this->notes as $note ) {
			$root->appendChild( $note );
		}
	}

	/**
	 * @return string
	 */
	public function getHtml() {
		return ( new HtmlFormatter() )->formatDOMNode( $this->htmlDocument->documentElement );
	}

	/**
	 * @return string[]
	 */
	public function getWarnings() {
		return $this->warnings;
	}

	/**
	 * @return string[]
	 */
	public function getExternalLinksUrls() {
		return $this->externalLinksUrls;
	}

	/**
	 * @return File[]
	 */
	public function getIncludedFiles() {
		return $this->includedFiles;
	}

	/**
	 * @param DOMNode $teiNode
	 * @param int $divNesting the number of divs in which the tag is. Used to find the proper <hX> tag
	 * @return DOMNode
	 */
	private function convertNode( DOMNode $teiNode, $divNesting ) {
		if ( $teiNode instanceof DOMText ) {
			return $this->htmlDocument->createTextNode( $teiNode->textContent );
		} elseif ( $teiNode instanceof DOMComment ) {
			return $this->htmlDocument->createComment( $teiNode->textContent );
		} elseif ( $teiNode instanceof DOMElement ) {
			return $this->convertElement( $teiNode, $divNesting );
		} else {
			return $this->htmlDocument->createTextNode( $teiNode->C14N() );
		}
	}

	private function convertElement( DOMElement $teiElement, $divNesting ) {
		if ( preg_match( '/^div(\d?)$/', $teiElement->tagName, $m ) ) {
			if ( $m[1] === '' ) {
				$divNesting++;
			} else {
				$divNesting = (int)$m[1];
			}
		}

		$htmlTagData = $this->htmlTagForTeiElement( $teiElement, $divNesting );
		if ( $htmlTagData === null ) {
			$htmlTagData = [ self::NODE_NAME => 'span' ];
		}

		$htmlElement = $this->createHtmlElement( $htmlTagData[self::NODE_NAME] );
		$htmlElement->setAttribute( self::TEI_TAG_NAME, $teiElement->tagName );

		if ( array_key_exists( self::VALUE_FUNCTION, $htmlTagData ) ) {
			$this->{$htmlTagData[ self::VALUE_FUNCTION ]}( $teiElement, $htmlElement );
		} else {
			$this->convertAndAddAttributes( $teiElement, $htmlElement );
			$this->convertAndAddChildrenNode( $teiElement, $htmlElement, $divNesting );
		}

		return $htmlElement;
	}

	private function htmlTagForTeiElement( DOMElement $teiElement, $divNesting ) {
		if ( $teiElement->tagName === 'head' ) {
			$level = min( 6, max( $divNesting, 1 ) );
			return [ self::NODE_NAME => "h$level" ];
		}

		foreach ( $this->possibleKeysForTagsMapping( $teiElement ) as $key ) {
			if ( array_key_exists( $key, self::$tagsMapping ) ) {
				$result = self::$tagsMapping[$key];
				if ( is_string( $result ) ) {
					$result = [ self::NODE_NAME => $result ];
				}
				return $result;
			}
		}
		return null;
	}

	private function possibleKeysForTagsMapping( DOMElement $teiElement ) {
		if ( $teiElement->hasAttribute( 'notation' ) ) {
			yield $teiElement->tagName . '-notation-' .
				  strtolower( $teiElement->getAttribute( 'notation' ) );
		}
		if ( $teiElement->hasAttribute( 'type' ) ) {
			yield $teiElement->tagName . '-type-' .
				  strtolower( $teiElement->getAttribute( 'type' ) );
		}
		if ( $teiElement->hasAttribute( 'role' ) ) {
			yield $teiElement->tagName . '-role-' .
				  strtolower( $teiElement->getAttribute( 'role' ) );
		}
		if ( $teiElement->hasAttribute( 'rend' ) ) {
			yield $teiElement->tagName . '-rend-' .
				  strtolower( $teiElement->getAttribute( 'rend' ) );
		}
		if ( $teiElement->parentNode instanceof DOMElement ) {
			yield $teiElement->tagName . '-parent-' . $teiElement->parentNode->tagName;
		}
		yield $teiElement->tagName;
	}

	private function convertAndAddChildrenNode(
		DOMElement $teiElement, DOMElement $htmlElement, $divNesting
	) {
		foreach ( $teiElement->childNodes as $childNode ) {
			$htmlElement->appendChild( $this->convertNode( $childNode, $divNesting ) );
		}
	}

	private function convertAndAddAttributes( DOMElement $teiElement, DOMElement $htmlElement ) {
		/**	@var DOMNode $attribute **/
		foreach ( $teiElement->attributes as $attribute ) {
			if ( array_key_exists( $attribute->nodeName, self::$attributesMapping ) ) {
				$attributeData = self::$attributesMapping[$attribute->nodeName];
				if ( is_string( $attributeData ) ) {
					$attributeData = [ self::NODE_NAME => $attributeData ];
				}

				if ( array_key_exists( self::VALUE_FUNCTION, $attributeData ) ) {
					$this->{$attributeData[ self::VALUE_FUNCTION ]}( $teiElement, $htmlElement );
				} else {
					$htmlElement->setAttribute( $attributeData[self::NODE_NAME], $attribute->nodeValue );
				}
			} else {
				$htmlElement->setAttribute(
					'data-tei-' . $attribute->nodeName,
					$attribute->nodeValue
				);
			}
		}
	}

	private function convertRend( DOMElement $teiElement, DOMElement $htmlElement ) {
		$htmlElement->setAttribute( 'class', implode( ' ', array_map( function ( $val ) {
			return 'tei-rend-' . $val;
		}, array_filter( explode( ' ', $teiElement->getAttribute( 'rend' ) ) ) ) ) );
	}

	private function convertStyle( DOMElement $teiElement, DOMElement $htmlElement ) {
		$style = Sanitizer::checkCss( $teiElement->getAttribute( 'style' ) );
		$htmlElement->setAttribute( 'style', $style );
	}

	private function convertTarget( DOMElement $teiElement, DOMElement $htmlElement ) {
		$url = Sanitizer::cleanUrl( $teiElement->getAttribute( 'target' ) );
		$this->externalLinksUrls[] = $url;
		$htmlElement->setAttribute( 'href', $url );
		return $url;
	}

	private function convertUrl( DOMElement $teiElement, DOMElement $htmlElement ) {
		global $wgThumbLimits;
		$fileName = $teiElement->getAttribute( 'url' );
		$htmlElement->setAttribute( 'data-tei-url', $fileName );

		$file = RepoGroup::singleton()->findFile( $fileName );
		if ( $file === false || wfIsBadImage( $file->getTitle()->getDBkey(), $this->pageTitle ) ) {
			$this->warning( 'tei-parser-file-not-found', $fileName );
			return;
		}
		$this->includedFiles[] = $file;

		$parameters = [];
		if ( $teiElement->hasAttribute( 'width' ) ) {
			$parameters['width'] = (int)$teiElement->getAttribute( 'width' );
		}
		if ( $teiElement->hasAttribute( 'height' ) ) {
			$parameters['height'] = (int)$teiElement->getAttribute( 'height' );
		}
		if ( !array_key_exists( 'width', $parameters ) ) {
			if ( array_key_exists( 'height', $parameters ) ) {
				$parameters['width'] = $parameters['height'] * $file->getWidth() / $file->getHeight();
			} else {
				$parameters['width'] = $wgThumbLimits[User::getDefaultOption( 'thumbsize' )];
				if ( !$file->isVectorized() && $file->getWidth() < $parameters['width'] ) {
					$parameters['width'] = $file->getWidth();
				}
			}
		}

		$thumbnail = $file->transform( $parameters );
		if ( $thumbnail === false ) {
			$this->warning( 'tei-parser-file-not-found', $fileName );
			return;
		}
		if ( $thumbnail->isError() ) {
			$this->warnings[] = $thumbnail->toHtml();
			return;
		}

		Linker::processResponsiveImages( $file, $thumbnail, $parameters );

		$dom = new DOMDocument();
		$dom->loadXML( $thumbnail->toHtml() );
		/** @var DOMNode $attribute */
		foreach ( $dom->documentElement->attributes as $attribute ) {
			$htmlElement->setAttribute( $attribute->nodeName, $attribute->nodeValue );
		}
	}

	private function convertTexFormula( DOMElement $teiElement, DOMElement $htmlElement ) {
		$text = $teiElement->textContent;
		$this->convertAndAddAttributes( $teiElement, $htmlElement );
		$htmlElement->setAttribute( self::TEI_CONTENT, $teiElement->textContent );

		if ( !class_exists( '\MathRenderer' ) ) {
			return $this->htmlDocument->createTextNode( $text );
		}
		$math = \MathRenderer::renderMath( $text, [], 'mathml' );
		$htmlElement->appendChild( $this->importHtml( $math ) );
	}

	private function convertNote( DOMElement $teiElement, DOMElement $htmlElement ) {
		if ( $teiElement->hasAttribute( 'xml:id' ) ) {
			$id = $teiElement->getAttribute( 'xml:id' );
		} else {
			$id = 'mw-note-' . $this->notesCounter;
		}

		// Pointer
		$htmlElement->setAttribute( 'href', '#' . $id );
		$htmlElement->setAttribute( 'role', 'doc-noteref' );
		if ( $teiElement->hasAttribute( 'n' ) ) {
			$htmlElement->textContent = $teiElement->getAttribute( 'n' );
			$htmlElement->setAttribute( 'data-tei-n', $teiElement->getAttribute( 'n' ) );
		} else {
			$htmlElement->textContent = (string)$this->notesCounter;
		}

		// Content
		$content = $this->createHtmlElement( 'aside' );
		$this->convertAndAddAttributes( $teiElement, $content );
		$content->removeAttribute( 'data-tei-n' );
		$content->setAttribute( 'id', $id );
		$content->setAttribute( 'role', 'doc-footnote' );
		$this->convertAndAddChildrenNode( $teiElement, $content, 6 );
		$this->notes[$id] = $content;

		$this->notesCounter++;
	}

	private function createHtmlElement( $name ) {
		return $this->htmlDocument->createElementNS( HTMLData::NS_HTML, $name );
	}

	private function warning( ...$args ) {
		$this->warnings[] = wfMessage( ...$args )->parse();
	}

	private function importHtml( $html ) {
		$dom = new DOMDocument();
		$dom->loadXML( $html );
		return $this->htmlDocument->importNode( $dom->documentElement, true );
	}
}