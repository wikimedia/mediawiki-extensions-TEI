<?php

namespace MediaWiki\Extension\Tei\Model;

use InvalidArgumentException;
use MediaWiki\Extension\Tei\Model\ContentModel\ContentModel;
use OutOfBoundsException;

/**
 * @license GPL-2.0-or-later
 *
 * Registry containing the definition of all TEI tags and attributes
 */
class TeiRegistry {

	const TEI_NAMESPACE = 'http://www.tei-c.org/ns/1.0';

	/**
	 * @var ElementSpec[]
	 */
	private $elements = [];

	/**
	 * @var ClassSpec[]
	 */
	private $classes = [];

	/**
	 * @var string[][]
	 */
	private $elementsInClasses = [];

	/**
	 * @return ElementSpec[]
	 */
	public function getAllElementsSpec() {
		return array_values( $this->elements );
	}

	/**
	 * @param string $elementName
	 * @return ElementSpec
	 * @throws OutOfBoundsException If the element is not registered
	 */
	public function getElementSpecFromName( $elementName ) {
		if ( !array_key_exists( $elementName, $this->elements ) ) {
			throw new OutOfBoundsException(
				'An element named ' . $elementName . ' does not exist in the registry.'
			);
		}

		return $this->elements[$elementName ];
	}

	/**
	 * @param string $className
	 * @return string[]
	 */
	public function getElementNamesInClass( $className ) {
		if ( !array_key_exists( $className, $this->elementsInClasses ) ) {
			throw new InvalidArgumentException(
				'The class named ' . $className . ' does not exist in the registry.'
			);
		}
		return $this->elementsInClasses[$className];
	}

	/**
	 * @param string $elementName
	 * @return AttributeDef[]
	 * @throws OutOfBoundsException If the element is not registered
	 */
	public function getAllAttributesForElement( $elementName ) {
		$elementSpec = $this->getElementSpecFromName( $elementName );
		$attributesMap = [];
		foreach ( $elementSpec->getAttributes() as $attribute ) {
			$attributesMap[$attribute->getName()] = $attribute;
		}
		foreach ( $elementSpec->getClasses() as $class ) {
			$this->addAttributesFromClass( $class, $attributesMap );
		}
		return $attributesMap;
	}

	private function addAttributesFromClass( $className, array &$attributesMap ) {
		$classSpec = $this->classes[$className];
		foreach ( $classSpec->getAttributes() as $attribute ) {
			$attributesMap[$attribute->getName()] = $attribute;
		}
		foreach ( $classSpec->getSuperClasses() as $superClass ) {
			$this->addAttributesFromClass( $superClass, $attributesMap );
		}
	}

	/**
	 * @param string $ident tag name
	 * @param string[] $classes the classes this tag is in. They should all be registered
	 * @param ContentModel $contentModel the content model of the children of this tag
	 * @param AttributeDef[] $attributes the attributes specific to the element
	 */
	public function registerElement(
		$ident, array $classes, ContentModel $contentModel, array $attributes = []
	) {
		if ( array_key_exists( $ident, $this->elements ) ) {
			throw new InvalidArgumentException(
				'A tag named ' . $ident . ' already exists in the registry.'
			);
		}
		foreach ( $classes as $class ) {
			if ( !array_key_exists( $class, $this->classes ) ) {
				throw new InvalidArgumentException(
					'The class named ' . $class . ' does not exist in the registry.'
				);
			}
		}
		$this->elements[$ident] = new ElementSpec( $ident, $classes, $contentModel, $attributes );

		foreach ( $classes as $class ) {
			$this->addElementToClass( $ident, $class );
		}
	}

	private function addElementToClass( $elementName, $className ) {
		if ( in_array( $elementName, $this->elementsInClasses[$className] ) ) {
			return;
		}
		$this->elementsInClasses[$className][] = $elementName;
		foreach ( $this->classes[$className]->getSuperClasses() as $superClassName ) {
			$this->addElementToClass( $elementName, $superClassName );
		}
	}

	/**
	 * @param string $ident tag name
	 * @param string[] $superClasses the super classes of this class
	 * @param AttributeDef[] $attributes the attributes of the class
	 */
	public function registerClass( $ident, array $superClasses, array $attributes = [] ) {
		if ( array_key_exists( $ident, $this->classes ) ) {
			throw new InvalidArgumentException(
				'A class named ' . $ident . ' already exists in the registry.'
			);
		}
		foreach ( $superClasses as $class ) {
			if ( !array_key_exists( $class, $this->classes ) ) {
				throw new InvalidArgumentException(
					'The class named ' . $class . ' does not exist in the registry.'
				);
			}
		}

		$this->classes[$ident] = new ClassSpec( $ident, $superClasses, $attributes );
		$this->elementsInClasses[$ident] = [];
	}
}
