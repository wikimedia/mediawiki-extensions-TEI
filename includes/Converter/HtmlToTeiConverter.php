<?php

namespace MediaWiki\Extension\Tei\Converter;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use MediaWiki\Extension\Tei\Model\TeiRegistry;

/**
 * @license GPL-2.0-or-later
 *
 * Converts HTML to TEI
 */
class HtmlToTeiConverter {

	const TAG = 'tag';

	private static $tagsMapping = [
		'abbr' => 'abbr',
		'article' => 'text',
		'b' => [
			self::TAG => 'hi',
			'rend' => 'bold'
		],
		'br' => 'lb',
		'del' => 'del',
		'div' => 'div',
		'footer' => 'back',
		'header' => 'front',
		'i' => [
			self::TAG => 'hi',
			'rend' => 'italic'
		],
		'li' => 'item',
		'ol' => [
			self::TAG => 'list',
			'type' => 'ordered'
		],
		'p' => 'p',
		'section' => 'body',
		'sub' => [
			self::TAG => 'hi',
			'rend' => 'sub'
		],
		'sup' => [
			self::TAG => 'hi',
			'rend' => 'sup'
		],
		'small' => [
			self::TAG => 'hi',
			'rend' => 'small'
		],
		'span' => 'hi',
		'table' => [
			self::TAG => 'table',
		],
		'td' => 'cell',
		'th' => [
			self::TAG => 'cell',
			'role' => 'label'
		],
		'tr' => 'row',
		'ul' => [
			self::TAG => 'list',
			'type' => 'unordered'
		],
		'var' => [
			self::TAG => 'hi',
			'rend' => 'var'
		]
	];

	private static $tagsToIgnore = [ 'tbody' ];

	private static $attributesMapping = [
		'lang' => 'xml:lang',
		'id' => 'xml:id',
		'colspan' => 'cols',
		'rowspan' => 'rows',
	];

	/**
	 * @var DOMDocument
	 */
	private $teiDocument;

	/**
	 * @param DOMDocument $htmlDocument
	 * @return string
	 */
	public function convertToTei( DOMDocument $htmlDocument ) {
		$this->buildTeiDocument( $htmlDocument );
		return $this->teiDocument->saveXML( $this->teiDocument->documentElement );
	}

	private function buildTeiDocument( DOMDocument $htmlDocument ) {
		$this->teiDocument = new DOMDocument( '1.0', 'UTF-8' );
		$this->convertHtml( $htmlDocument );
	}

	private function convertHtml( DOMDocument $htmlDocument ) {
		foreach ( $this->findPossibleTeiDocumentRootsInHtml( $htmlDocument ) as $htmlRoot ) {
			if ( !$this->isEmptyNode( $htmlRoot ) ) {
				$this->teiDocument->appendChild( $this->convertNode( $htmlRoot ) );
			}
		}
	}

	private function findPossibleTeiDocumentRootsInHtml( DOMDocument $htmlDocument ) {
		if ( $htmlDocument->documentElement === null ) {
			// Empty document
		} elseif ( $htmlDocument->documentElement->nodeName === 'html' ) {
			foreach ( $htmlDocument->documentElement->childNodes as $child ) {
				if ( $child instanceof DOMElement ) {
					if ( $child->nodeName === 'body' ) {
						foreach ( $child->childNodes as $element ) {
							yield $element;
						}
					}
				}
			}
		} elseif ( $htmlDocument->documentElement->nodeName === 'body' ) {
			foreach ( $htmlDocument->documentElement->childNodes as $element ) {
				yield $element;
			}
		} else {
			yield $htmlDocument->documentElement;
		}
	}

	private function isEmptyNode( DOMNode $node ) {
		return $node instanceof DOMText && $node->isElementContentWhitespace();
	}

	private function convertNode( DOMNode $htmlNode ) {
		if ( $htmlNode instanceof DOMText ) {
			return $this->teiDocument->createTextNode( $htmlNode->textContent );
		} elseif ( $htmlNode instanceof DOMComment ) {
			return $this->teiDocument->createComment( $htmlNode->textContent );
		} elseif ( $htmlNode instanceof DOMElement ) {
			return $this->convertElement( $htmlNode );
		} else {
			return $this->teiDocument->createTextNode( $htmlNode->C14N() );
		}
	}

	private function convertElement( DOMElement $htmlElement ) {
		if ( !array_key_exists( $htmlElement->localName, self::$tagsMapping ) ) {
			return $this->teiDocument->createTextNode( $htmlElement->C14N() );
		}

		$teiTag = self::$tagsMapping[$htmlElement->localName];
		if ( is_array( $teiTag ) ) {
			$teiElement = $this->createTeiElement( $teiTag[self::TAG] );
			foreach ( $teiTag as $attributeName => $attributeValue ) {
				if ( $attributeName !== self::TAG ) {
					$teiElement->setAttribute( $attributeName, $attributeValue );
				}

			}
		} else {
			$teiElement = $this->createTeiElement( $teiTag );
		}
		$this->convertAndAddGlobalAttributes( $htmlElement, $teiElement );
		$this->convertAndAddChildrenNode( $htmlElement, $teiElement );
		return $teiElement;
	}

	private function convertAndAddChildrenNode( DOMElement $htmlElement, DOMElement $teiElement ) {
		foreach ( $htmlElement->childNodes as $childNode ) {
			if (
				$childNode instanceof DOMElement && in_array( $childNode->nodeName, self::$tagsToIgnore )
			) {
				$this->convertAndAddChildrenNode( $childNode, $teiElement );
			} else {
				$teiElement->appendChild( $this->convertNode( $childNode ) );
			}
		}
	}

	private function convertAndAddGlobalAttributes( DOMElement $htmlElement, DOMElement $teiElement ) {
		/**	@var DOMNode $attribute **/
		foreach ( $htmlElement->attributes as $attribute ) {
			if ( array_key_exists( $attribute->nodeName, self::$attributesMapping ) ) {
				$teiElement->setAttribute(
					self::$attributesMapping[$attribute->nodeName],
					$attribute->nodeValue
				);
			}
		}
	}

	private function createTeiElement( $name ) {
		return $this->teiDocument->createElementNS( TeiRegistry::TEI_NAMESPACE, $name );
	}
}
