<?php

namespace MediaWiki\Extension\Tei\Converter;

/**
 * @license GPL-2.0-or-later
 *
 * Interface of operation of mapping tags
 */
abstract class TagMapper {
	/**
	 * @param string $name
	 * @param string[string] $attrs
	 * @param bool $isSelfClosing
	 * @return string
	 */
	abstract public function mapStartTag( $name, $attrs, $isSelfClosing );

	/**
	 * @param string $name
	 * @return string
	 */
	abstract public function mapEndTag( $name );

	/**
	 * @param string $name
	 * @param string[string] $attrs
	 * @param bool $isSelfClosing
	 * @return string
	 */
	protected function serializeStartTag( $name, $attrs, $isSelfClosing ) {
		$output = "<$name";
		foreach ( $attrs as $name => $value ) {
			$output .= " $name=\"" . str_replace( '"', '&quot;', $value ) . '"';
		}
		if ( $isSelfClosing ) {
			$output .= ' /';
		}
		$output .= '>';
		return $output;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function serializeEndTag( $name ) {
		return "</$name>";
	}
}
