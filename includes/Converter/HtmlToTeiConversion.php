<?php

namespace MediaWiki\Extension\Tei\Converter;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use MediaWiki\Extension\Tei\Model\TeiRegistry;

/**
 * @license GPL-2.0-or-later
 *
 * A conversion from HTML to TEI
 */
class HtmlToTeiConversion {

	const NODE_NAME = 'nodeName';
	const VALUE_FUNCTION = 'value-function';
	const TEI_TAG_NAME = 'data-tei-tag';
	const TEI_CONTENT = 'data-tei-content';

	private static $tagsMapping = [
		'a' => 'ref',
		'abbr' => 'abbr',
		'address' => 'address',
		'article' => 'text',
		'aside' => null,
		'b' => [
			self::NODE_NAME => 'hi',
			'rend' => 'bold'
		],
		'blockquote' => 'cit',
		'br' => 'lb',
		'cite' => 'bibl',
		'del' => 'del',
		'div' => 'ab',
		'figcaption' => 'figDesc',
		'figure' => 'figure',
		'footer' => 'back',
		'h1' => 'head',
		'h2' => 'head',
		'h3' => 'head',
		'h4' => 'head',
		'h5' => 'head',
		'h6' => 'head',
		'header' => 'front',
		'i' => [
			self::NODE_NAME => 'hi',
			'rend' => 'italic'
		],
		'img' => 'graphic',
		'ins' => 'add',
		'li' => 'item',
		'ol' => [
			self::NODE_NAME => 'list',
			'type' => 'ordered'
		],
		'p' => 'p',
		'q' => 'q',
		'section' => 'div',
		'sub' => [
			self::NODE_NAME => 'hi',
			'rend' => 'sub'
		],
		'sup' => [
			self::NODE_NAME => 'hi',
			'rend' => 'sup'
		],
		'small' => [
			self::NODE_NAME => 'hi',
			'rend' => 'small'
		],
		'span' => 'hi',
		'table' => [
			self::NODE_NAME => 'table',
		],
		'td' => 'cell',
		'th' => [
			self::NODE_NAME => 'cell',
			'role' => 'label'
		],
		'time' => 'date',
		'tr' => 'row',
		'ul' => [
			self::NODE_NAME => 'list',
			'type' => 'unordered'
		],
		'var' => [
			self::NODE_NAME => 'hi',
			'rend' => 'var'
		]
	];

	private static $tagsToIgnore = [ 'thead', 'tbody', 'tfoot' ];

	private static $attributesMapping = [
		'class' => [
			self::NODE_NAME => 'rend',
			self::VALUE_FUNCTION => 'convertClass'
		],
		'colspan' => 'cols',
		'datetime' => 'when',
		'height' => 'height',
		'href' => 'target',
		'hreflang' => 'targetLang',
		'id' => 'xml:id',
		'lang' => 'xml:lang',
		'rowspan' => 'rows',
		'src' => 'url',
		'style' => 'style',
		'width' => 'width'
	];

	/**
	 * @var DOMXPath
	 */
	private $htmlXPath;

	/**
	 * @var DOMDocument
	 */
	private $teiDocument;

	/**
	 * @param DOMDocument $htmlDocument
	 */
	public function __construct( DOMDocument $htmlDocument ) {
		$this->htmlXPath = new DOMXPath( $htmlDocument );
		$this->teiDocument = new DOMDocument( '1.0', 'UTF-8' );
		$this->convertHtml( $htmlDocument );
	}

	/**
	 * @return string
	 */
	public function getXml() {
		return $this->teiDocument->saveXML( $this->teiDocument->documentElement );
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
		if ( $htmlElement->hasAttribute( self::TEI_TAG_NAME ) ) {
			$teiElement = $this->createTeiElement( $htmlElement->getAttribute( self::TEI_TAG_NAME ) );
		} elseif ( array_key_exists( $htmlElement->localName, self::$tagsMapping ) ) {
			$teiTagData = self::$tagsMapping[$htmlElement->localName];
			if ( $teiTagData === null ) {
				return $this->teiDocument->createTextNode( '' );
			}
			if ( is_string( $teiTagData ) ) {
				$teiTagData = [ self::NODE_NAME => $teiTagData ];
			}

			$teiElement = $this->createTeiElement( $teiTagData[self::NODE_NAME] );
			foreach ( $teiTagData as $name => $value ) {
				if ( $name !== self::NODE_NAME ) {
					$teiElement->setAttribute( $name, $value );
				}
			}
		} else {
			return $this->teiDocument->createTextNode( $htmlElement->C14N() );
		}

		if ( $teiElement->tagName === 'note' ) {
			if ( $htmlElement->hasAttribute( 'href' ) ) {
				$href = trim( $htmlElement->getAttribute( 'href' ) );
				if ( strpos( $href, '#' ) === 0 ) {
					$id = substr( $href, 1 );
					foreach ( $this->htmlXPath->query(
						'//*[@id="' . $id . '"]'
					) as $htmlContent ) {
						$this->convertAndAddAttributes( $htmlContent, $teiElement );
						$this->convertAndAddChildrenNode( $htmlContent, $teiElement );
						if ( strpos( $id, 'mw-note-' ) === 0 ) {
							$teiElement->removeAttribute( 'xml:id' );
						}
					}
				}
			}

			$this->convertAndAddAttributes( $htmlElement, $teiElement );
			$teiElement->removeAttribute( 'target' );

			return $teiElement;
		}

		$this->convertAndAddAttributes( $htmlElement, $teiElement );

		if ( $htmlElement->hasAttribute( self::TEI_CONTENT ) ) {
			$teiElement->textContent = $htmlElement->getAttribute( self::TEI_CONTENT );
		} else {
			$this->convertAndAddChildrenNode( $htmlElement, $teiElement );
		}
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

	private function convertAndAddAttributes( DOMElement $htmlElement, DOMElement $teiElement ) {
		/**	@var DOMNode $attribute **/
		foreach ( $htmlElement->attributes as $attribute ) {
			if ( array_key_exists( $attribute->nodeName, self::$attributesMapping ) ) {
				$attributeData = self::$attributesMapping[$attribute->nodeName];
				if ( is_string( $attributeData ) ) {
					$attributeData = [ self::NODE_NAME => $attributeData ];
				}

				$nodeValue = array_key_exists( self::VALUE_FUNCTION, $attributeData )
					? $this->{$attributeData[ self::VALUE_FUNCTION ]}( $attribute->nodeValue )
					: $attribute->nodeValue;

				$teiElement->setAttribute( $attributeData[self::NODE_NAME], $nodeValue );
			} elseif (
				strpos( $attribute->nodeName, 'data-tei-' ) === 0 &&
				$attribute->nodeName !== self::TEI_TAG_NAME && $attribute->nodeName != self::TEI_CONTENT
			) {
				$teiElement->setAttribute(
					substr( $attribute->nodeName, 9 ),
					$attribute->nodeValue
				);
			}
		}
	}

	private function createTeiElement( $name ) {
		return $this->teiDocument->createElementNS( TeiRegistry::TEI_NAMESPACE, $name );
	}

	private static function convertClass( $value ) {
		return implode( ' ', array_map( function ( $val ) {
			return substr( $val, 9 );
		}, array_filter( explode( ' ', $value ), function ( $value ) {
			return strpos( $value, 'tei-rend-' ) === 0;
		} ) ) );
	}
}
