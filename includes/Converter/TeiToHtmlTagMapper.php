<?php

namespace MediaWiki\Extension\Tei\Converter;

/**
 * @license GPL-2.0-or-later
 *
 * TEI to HTML tag mapper
 */
class TeiToHtmlTagMapper extends TagMapper {

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

	private $tagStack = [];

	/**
	 * @param string $name
	 * @param string[string] $attrs
	 * @param bool $isSelfClosing
	 * @return string
	 */
	public function mapStartTag( $name, $attrs, $isSelfClosing ) {
		// Map tag
		$teiElement = $this->htmlTagForTeiElement( $name, $attrs );
		if ( $teiElement === null ) {
			return htmlspecialchars( $this->serializeStartTag( $name, $attrs, $isSelfClosing ) );
		}

		// Map attributes
		$teiAttributes = [];
		foreach ( $attrs as $attributeName => $attributeValue ) {
			if ( array_key_exists( $attributeName, self::$attributesMapping ) ) {
				$teiAttributes[self::$attributesMapping[$attributeName]] = $attributeValue;
			}
		}

		$this->tagStack[] = [ $name, $teiElement ];
		return $this->serializeStartTag( $teiElement, $teiAttributes, $isSelfClosing );
	}

	private function htmlTagForTeiElement( $name, $attrs ) {
		foreach ( $this->possibleKeysForTagsMapping( $name, $attrs ) as $key ) {
			if ( array_key_exists( $key, self::$tagsMapping ) ) {
				return self::$tagsMapping[$key];
			}
		}
		return null;
	}

	private function possibleKeysForTagsMapping( $name, $attrs ) {
		if ( array_key_exists( 'type', $attrs ) ) {
			yield $name . '-type-' . $attrs['type'];
		}
		if ( array_key_exists( 'role', $attrs ) ) {
			yield $name . '-role-' . $attrs['role'];
		}
		if ( array_key_exists( 'rend', $attrs ) ) {
			yield $name . '-rend-' . $attrs['rend'];
		}
		yield $name;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function mapEndTag( $name ) {
		$top = end( $this->tagStack );
		if ( $top !== false ) {
			list( $teiTag, $htmlTag ) = $top;
			if ( $name === $teiTag ) {
				array_pop( $this->tagStack );
				return $this->serializeEndTag( $htmlTag );
			}
		}
		if ( array_key_exists( $name, self::$tagsMapping ) ) {
			return $this->serializeEndTag( self::$tagsMapping[$name] );
		} else {
			return htmlspecialchars( $this->serializeEndTag( $name ) );
		}
	}
}
