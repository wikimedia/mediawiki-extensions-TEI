<?php

namespace MediaWiki\Extension\Tei\Converter;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Title;

/**
 * @license GPL-2.0-or-later
 *
 * Converts TEI to HTML
 */
class TeiToHtmlConverter {

	private static $tagsMapping = [
		'abbr' => 'abbr',
		'back' => 'footer',
		'body' => 'section',
		'cell-role-label' => 'th',
		'cell-role-data' => 'td',
		'cell' => 'td',
		'del' => 'del',
		'div' => 'div',
		'front' => 'header',
		'hi' => 'span',
		'hi-rend-bold' => 'b',
		'hi-rend-italic' => 'i',
		'hi-rend-sub' => 'sub',
		'hi-rend-sup' => 'sup',
		'hi-rend-small' => 'small',
		'hi-rend-var' => 'var',
		'item' => 'li',
		'lb' => 'br',
		'list' => 'ul',
		'list-type-ordered' => 'ol',
		'list-type-unordered' => 'ul',
		'p' => 'p',
		'row' => 'tr',
		'table' => 'table',
		'text' => 'article'
	];

	private static $attributesMapping = [
		'xml:lang' => 'lang',
		'xml:id' => 'id',
		'cols' => 'colspan',
		'rows' => 'rowspan',
	];

	/**
	 * @var DOMDocument
	 */
	private $htmlDocument;

	/**
	 * @param DOMDocument $teiDocument
	 * @param Title $pageTitle
	 * @return string
	 */
	public function convertToStandaloneHtml( DOMDocument $teiDocument, Title $pageTitle ) {
		$this->buildHtmlDocument( $teiDocument, $pageTitle );
		return $this->htmlDocument->saveHTML();
	}

	/**
	 * @param DOMDocument $teiDocument
	 * @param Title $pageTitle
	 * @return string
	 */
	public function convertToHtmlBodyContent( DOMDocument $teiDocument, Title $pageTitle ) {
		$this->buildHtmlDocument( $teiDocument, $pageTitle );
		$html = '';
		/**	@var DOMElement $body **/
		foreach ( $this->htmlDocument->getElementsByTagName( 'body' ) as $body ) {
			foreach ( $body->childNodes as $child ) {
				$html .= $this->htmlDocument->saveXML( $child );
			}
		}
		return $html;
	}

	private function buildHtmlDocument( DOMDocument $teiDocument, Title $pageTitle ) {
		$this->initHtmlDocument();
		$this->addHead( $pageTitle );
		$this->addTeiText( $teiDocument );
	}

	private function initHtmlDocument() {
		$this->htmlDocument = new DOMDocument( '1.0', 'UTF-8' );
		$this->htmlDocument->appendChild( $this->htmlDocument->createElement( 'html' ) );
	}

	private function addHead( Title $pageTitle ) {
		$head = $this->htmlDocument->createElement( 'head' );
		$this->htmlDocument->documentElement->appendChild( $head );

		$metaCharset = $this->htmlDocument->createElement( 'meta' );
		$metaCharset->setAttribute( 'charset', 'utf-8' );
		$head->appendChild( $metaCharset );

		$title = $this->htmlDocument->createElement( 'title', $pageTitle->getFullText() );
		$head->appendChild( $title );
	}

	private function addTeiText( DOMDocument $teiDocument ) {
		$body = $this->htmlDocument->createElement( 'body' );
		$this->htmlDocument->documentElement->appendChild( $body );
		$body->appendChild( $this->convertNode( $teiDocument->documentElement ) );
	}

	private function convertNode( DOMNode $teiNode ) {
		if ( $teiNode instanceof DOMText ) {
			return $this->htmlDocument->createTextNode( $teiNode->textContent );
		} elseif ( $teiNode instanceof DOMComment ) {
			return $this->htmlDocument->createComment( $teiNode->textContent );
		} elseif ( $teiNode instanceof DOMElement ) {
			return $this->convertElement( $teiNode );
		} else {
			return $this->htmlDocument->createTextNode( $teiNode->C14N() );
		}
	}

	private function convertElement( DOMElement $teiElement ) {
		$htmlTagName = $this->htmlTagForTeiElement( $teiElement );
		if ( $htmlTagName === null ) {
			return $this->htmlDocument->createTextNode( $teiElement->C14N() );
		}

		$htmlElement = $this->htmlDocument->createElement( $htmlTagName );
		$this->convertAndAddChildrenNode( $teiElement, $htmlElement );
		$this->convertAndAddGlobalAttributes( $teiElement, $htmlElement );
		return $htmlElement;
	}

	private function htmlTagForTeiElement( DOMElement $teiElement ) {
		foreach ( $this->possibleKeysForTagsMapping( $teiElement ) as $key ) {
			if ( array_key_exists( $key, self::$tagsMapping ) ) {
				return self::$tagsMapping[$key];
			}
		}
		return null;
	}

	private function possibleKeysForTagsMapping( DOMElement $teiElement ) {
		if ( $teiElement->hasAttribute( 'type' ) ) {
			yield $teiElement->tagName . '-type-' . $teiElement->getAttribute( 'type' );
		}
		if ( $teiElement->hasAttribute( 'role' ) ) {
			yield $teiElement->tagName . '-role-' . $teiElement->getAttribute( 'role' );
		}
		if ( $teiElement->hasAttribute( 'rend' ) ) {
			yield $teiElement->tagName . '-rend-' . $teiElement->getAttribute( 'rend' );
		}
		yield $teiElement->tagName;
	}

	private function convertAndAddChildrenNode( DOMElement $teiElement, DOMElement $htmlElement ) {
		foreach ( $teiElement->childNodes as $childNode ) {
			$htmlElement->appendChild( $this->convertNode( $childNode ) );
		}
	}

	private function convertAndAddGlobalAttributes( DOMElement $teiElement, DOMElement $htmlElement ) {
		/**	@var DOMNode $attribute **/
		foreach ( $teiElement->attributes as $attribute ) {
			if ( array_key_exists( $attribute->nodeName, self::$attributesMapping ) ) {
				$htmlElement->setAttribute(
					self::$attributesMapping[$attribute->nodeName],
					$attribute->nodeValue
				);
			}
		}
	}
}
