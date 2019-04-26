<?php

namespace MediaWiki\Extension\Tei\Model;

use OutOfBoundsException;

/**
 * @license GPL-2.0-or-later
 *
 * Registry containing the definition of all TEI tags and attributes
 */
class TeiRegistry {

	const TEI_NAMESPACE = 'http://www.tei-c.org/ns/1.0';

	/**
	 * @var array[]
	 */
	private $data = [];

	/**
	 * @var string[][]
	 */
	private $elementsInClasses;

	/**
	 * @param array $teiDefinition JSON definition of the TEI elements
	 */
	public function __construct( array $teiDefinition ) {
		$this->data = $teiDefinition;
	}

	/**
	 * @return string[]
	 */
	public function getPossibleRootElements() {
		return $this->data['start'];
	}

	/**
	 * @return ElementSpec[]
	 */
	public function getAllElementsSpec() {
		$elements = [];
		foreach ( $this->data['elements'] as $ident => $element ) {
			$elements[] = new ElementSpec( $ident, $element );
		}
		return $elements;
	}

	/**
	 * @param string $elementIdent
	 * @return ElementSpec
	 * @throws OutOfBoundsException If the element is not registered
	 */
	public function getElementSpecFromIdent( $elementIdent ) {
		if ( !array_key_exists( $elementIdent, $this->data['elements'] ) ) {
			throw new OutOfBoundsException(
				'An element named ' . $elementIdent . ' does not exist in the registry.'
			);
		}
		return new ElementSpec( $elementIdent, $this->data['elements'][$elementIdent] );
	}

	/**
	 * @param string $macroIdent
	 * @return MacroSpec
	 * @throws OutOfBoundsException If the macro is not registered
	 */
	public function getMacroSpecFromIdent( $macroIdent ) {
		if ( !array_key_exists( $macroIdent, $this->data['macros'] ) ) {
			throw new OutOfBoundsException(
				'A macro named ' . $macroIdent . ' does not exist in the registry.'
			);
		}
		return new MacroSpec( $macroIdent, $this->data['macros'][$macroIdent] );
	}

	/**
	 * @param string $elementIdent
	 * @return AttributeDef[]
	 */
	public function getAllAttributesForEntityIdent( $elementIdent ) {
		$attributesMap = [];
		$this->addAttributesForEntityIdent( $elementIdent, $attributesMap );
		return $attributesMap;
	}

	private function addAttributesForEntityIdent( $elementIdent, array &$attributesMap ) {
		if ( array_key_exists( $elementIdent, $this->data['classes'] ) ) {
			$this->addAttributesForEntity( $this->data['classes'][$elementIdent], $attributesMap );
		} elseif ( array_key_exists( $elementIdent, $this->data['elements'] ) ) {
			$this->addAttributesForEntity( $this->data['elements'][$elementIdent], $attributesMap );
		}
	}

	private function addAttributesForEntity( array $entitySpec, array &$attributesMap ) {
		foreach ( $entitySpec['attributes'] as $ident => $attribute ) {
			if ( !array_key_exists( $ident, $attributesMap ) ) {
				$attributesMap[$ident] = new AttributeDef( $ident, $attribute );
			}
		}
		foreach ( $entitySpec['classes'] as $class ) {
			$this->addAttributesForEntityIdent( $class, $attributesMap );
		}
		return $attributesMap;
	}

	/**
	 * @param string $classIdent
	 * @return string[]
	 */
	public function getElementIdentsInClass( $classIdent ) {
		if ( $this->elementsInClasses === null ) {
			$this->loadElementInClass();
		}
		if ( !array_key_exists( $classIdent, $this->elementsInClasses ) ) {
			return [];
		}
		return $this->elementsInClasses[$classIdent];
	}

	private function loadElementInClass() {
		foreach ( $this->data['elements'] as $elementIdent => $element ) {
			foreach ( $element['classes'] as $classIdent ) {
				$this->addElementToClass( $elementIdent, $classIdent );
			}
		}
	}

	private function addElementToClass( $elementIdent, $classIdent ) {
		$this->elementsInClasses[$classIdent][] = $elementIdent;
		if ( array_key_exists( $classIdent, $this->data['classes'] ) ) {
			foreach ( $this->data['classes'][$classIdent]['classes'] as $superClassIdent ) {
				$this->addElementToClass( $elementIdent, $superClassIdent );
			}
		}
	}
}
