<?php

namespace MediaWiki\Extension\Tei\Converter;

use RemexHtml\Tokenizer\Attributes;
use RemexHtml\Tokenizer\TokenHandler;
use RemexHtml\Tokenizer\Tokenizer;

/**
 * @license GPL-2.0-or-later
 *
 * Handles XML/HTML tokens and map tags
 */
class TagsMappingSerializer implements TokenHandler {

	/**
	 * @var TagMapper
	 */
	private $tagMapper;

	/**
	 * @var string
	 */
	private $output;

	/**
	 * @param TagMapper $tagMapper
	 */
	public function __construct( TagMapper $tagMapper ) {
		$this->tagMapper = $tagMapper;
	}

	/**
	 * @return string
	 */
	public function getOutput() {
		return $this->output;
	}

	/**
	 * @see TokenHandler::startDocument
	 *
	 * @param Tokenizer $tokenizer
	 * @param string|null $fns
	 * @param string|null $fn
	 */
	public function startDocument( Tokenizer $tokenizer, $fns, $fn ) {
		$this->output = '';
	}

	/**
	 * @see TokenHandler::endDocument
	 *
	 * @param int $pos
	 */
	public function endDocument( $pos ) {
	}

	/**
	 * @see TokenHandler::error
	 *
	 * @param string $text
	 * @param int $pos
	 */
	public function error( $text, $pos ) {
	}

	/**
	 * @see TokenHandler::characters
	 *
	 * @param string $text
	 * @param int $start
	 * @param int $length
	 * @param int $sourceStart
	 * @param int $sourceLength
	 */
	public function characters( $text, $start, $length, $sourceStart, $sourceLength ) {
		$this->output .= htmlspecialchars( substr( $text, $start, $length ) );
	}

	/**
	 * @see TokenHandler::startTag
	 *
	 * @param string $name
	 * @param Attributes $attrs
	 * @param bool $selfClose
	 * @param int $sourceStart
	 * @param int $sourceLength
	 */
	public function startTag( $name, Attributes $attrs, $selfClose, $sourceStart, $sourceLength ) {
		$this->output .= $this->tagMapper->mapStartTag( $name, $attrs->getValues(), $selfClose );
	}

	/**
	 * @see TokenHandler::endTag
	 *
	 * @param string $name
	 * @param int $sourceStart
	 * @param int $sourceLength
	 */
	public function endTag( $name, $sourceStart, $sourceLength ) {
		$this->output .= $this->tagMapper->mapEndTag( $name );
	}

	/**
	 * @see TokenHandler::doctype
	 *
	 * @param string|null $name
	 * @param string|null $public
	 * @param string|null $system
	 * @param bool $quirks
	 * @param int $sourceStart
	 * @param int $sourceLength
	 */
	public function doctype( $name, $public, $system, $quirks, $sourceStart, $sourceLength ) {
	}

	/**
	 * @see TokenHandler::comment
	 *
	 * @param string $text
	 * @param int $sourceStart
	 * @param int $sourceLength
	 */
	public function comment( $text, $sourceStart, $sourceLength ) {
		$this->output .= '<!--' . $text . '-->';
	}
}
