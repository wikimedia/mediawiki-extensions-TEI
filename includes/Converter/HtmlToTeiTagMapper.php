<?php

namespace MediaWiki\Extension\Tei\Converter;

/**
 * @license GPL-2.0-or-later
 *
 * HTML to TEI tag mapper
 */
class HtmlToTeiTagMapper extends TagMapper {

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
	 * @param string $name
	 * @param string[string] $attrs
	 * @param bool $isSelfClosing
	 * @return string
	 */
	public function mapStartTag( $name, $attrs, $isSelfClosing ) {
		if ( array_key_exists( $name, self::$tagsMapping ) ) {
			// Map tag
			$teiTag = self::$tagsMapping[$name];
			$teiAttributes = [];
			if ( is_array( $teiTag ) ) {
				$teiElement = $teiTag[self::TAG];
				foreach ( $teiTag as $attributeName => $attributeValue ) {
					if ( $attributeName !== self::TAG ) {
						$teiAttributes[$attributeName] = $attributeValue;
					}
				}
			} else {
				$teiElement = $teiTag;
			}

			// Map attributes
			foreach ( $attrs as $attributeName => $attributeValue ) {
				if ( array_key_exists( $attributeName, self::$attributesMapping ) ) {
					$teiAttributes[self::$attributesMapping[$attributeName]] = $attributeValue;
				}
			}

			return $this->serializeStartTag( $teiElement, $teiAttributes, $isSelfClosing );
		} elseif ( in_array( $name, self::$tagsToIgnore ) ) {
			return '';
		} else {
			return htmlspecialchars( $this->serializeStartTag( $name, $attrs, $isSelfClosing ) );
		}
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function mapEndTag( $name ) {
		if ( array_key_exists( $name, self::$tagsMapping ) ) {
			$teiTag = self::$tagsMapping[$name];
			$teiElement = is_array( $teiTag ) ? $teiTag[self::TAG] : $teiTag;
			return $this->serializeEndTag( $teiElement );
		} elseif ( in_array( $name, self::$tagsToIgnore ) ) {
			return null;
		} else {
			return htmlspecialchars( $this->serializeEndTag( $name ) );
		}
	}
}
