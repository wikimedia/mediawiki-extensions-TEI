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

	private static $tagsMapping = [
		'footer' => 'back',
		'section' => 'body',
		'div' => 'div',
		'header' => 'front',
		'li' => 'item',
		'ul' => 'list',
		'p' => 'p',
		'article' => 'text'
	];

	private static $attributesMapping = [
		'lang' => 'xml:lang',
		'id' => 'xml:id'
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
		$this->initTeiDocument();
		$this->convertHtml( $htmlDocument );
	}

	private function initTeiDocument() {
		$this->teiDocument = new DOMDocument( '1.0', 'UTF-8' );
	}

	private function convertHtml( DOMDocument $htmlDocument ) {
		$converted = [];
		foreach ( $this->findPossibleTeiDocumentRootsInHtml( $htmlDocument ) as $htmlRoot ) {
			if ( !$this->isEmptyNode( $htmlRoot ) ) {
				$converted[] = $this->convertNode( $htmlRoot );
			}
		}
		$this->teiDocument->appendChild( $this->convertTeiNodesToATeiTextElement( $converted ) );
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

	/**
	 * @param DOMNode[] $nodes
	 * @return DOMElement
	 */
	private function convertTeiNodesToATeiTextElement( array $nodes ) {
		switch ( count( $nodes ) ) {
			case 0:
				return $this->convertTeiNodeToATeiTextElement( $this->createTeiElement( 'p' ) );
			case 1:
				return $this->convertTeiNodeToATeiTextElement( $nodes[0] );
			default:
				$group = $this->createTeiElement( 'group' );
				foreach ( $nodes as $node ) {
					$group->appendChild( $this->convertTeiNodeToATeiTextElement( $node ) );
				}
				return $this->convertTeiNodeToATeiTextElement( $group );
		}
	}

	private function convertTeiNodeToATeiTextElement( DOMNode $element ) {
		// We make sure we have a DOMElement
		if ( !( $element instanceof DOMElement ) ) {
			$p = $this->createTeiElement( 'p' );
			$p->appendChild( $element );
			$element = $p;
		}

		if ( $element->nodeName === 'text' ) {
			return $element;
		} elseif ( in_array( $element->nodeName, [ 'back', 'body', 'front', 'group' ] ) ) {
			$text = $this->createTeiElement( 'text' );
			$text->appendChild( $element );
			return $text;
		} else {
			$body = $this->createTeiElement( 'body' );
			$body->appendChild( $element );
			$text = $this->createTeiElement( 'text' );
			$text->appendChild( $body );
			return $text;
		}
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

		$teiElement = $this->createTeiElement( self::$tagsMapping[$htmlElement->localName] );
		$this->convertAndAddChildrenNode( $htmlElement, $teiElement );
		$this->convertAndAddGlobalAttributes( $htmlElement, $teiElement );
		return $teiElement;
	}

	private function convertAndAddChildrenNode( DOMElement $htmlElement, DOMElement $teiElement ) {
		foreach ( $htmlElement->childNodes as $childNode ) {
			$teiElement->appendChild( $this->convertNode( $childNode ) );
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
