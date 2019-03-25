<?php

namespace MediaWiki\Extension\Tei\Model;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
use DOMText;
use MediaWiki\Extension\Tei\Model\ContentModel\Evaluation\ContentModelValidator;
use OutOfBoundsException;
use StatusValue;

/**
 * Validates a TEI document
 *
 * @license GPL-2.0-or-later
 * @author  Thomas Pellissier Tanon
 */
class Validator {

	/**
	 * @var TeiRegistry
	 */
	private $registry;

	/**
	 * @var ContentModelValidator
	 */
	private $tagsContentModelValidator;

	/**
	 * @param TeiRegistry $registry
	 */
	public function __construct( TeiRegistry $registry ) {
		$this->registry = $registry;

		$this->tagsContentModelValidator = new ContentModelValidator(
			'tei-validation-tag-content-',
			function ( $groupId ) use ( $registry ) {
				return $registry->getElementNamesInClass( $groupId );
			}
		);
	}

	/**
	 * @param DOMDocument $document
	 * @return StatusValue
	 */
	public function validateDOM( DOMDocument $document ) {
		if ( $document->documentElement === null ) {
			return StatusValue::newFatal( 'tei-validation-no-root' );
		}

		$status = StatusValue::newGood();
		$this->validateRoot( $document->documentElement, $status );
		return $status;
	}

	private function validateRoot( DOMNode $root, StatusValue $status ) {
		if ( !in_array( $root->nodeName, [ 'text', 'body' ] ) ) {
			$status->fatal(
				'tei-validation-unexpected-root', $root->nodeName
			);
		}
		$this->validateTag( $root, $status );
	}

	private function validateTag( DOMNode $tag, StatusValue $status ) {
		if ( $tag instanceof DOMText || $tag instanceof DOMComment ) {
			return;
		}

		// We validate namespace
		if ( $tag->namespaceURI !== TeiRegistry::TEI_NAMESPACE ) {
			$status->fatal( 'tei-validation-wrong-namespace' );
		}

		// We validate the tag based on its definition
		try {
			$definition = $this->registry->getElementSpecFromName( $tag->nodeName );
			$this->validateTagUsingDefinition( $tag, $definition, $status );
		} catch ( OutOfBoundsException $e ) {
			$status->fatal(
				'tei-validation-unknown-tag', $tag->nodeName
			);
		}

		// We do a recursive call
		foreach ( $tag->childNodes as $childNode ) {
			$this->validateTag( $childNode, $status );
		}
	}

	private function validateTagUsingDefinition(
		DOMNode $tag, ElementSpec $definition, StatusValue $status
	) {
		// Attributes
		$this->validateAttributes( $tag->attributes, $definition->getName(), $status );

		// Children nodes
		$status->merge(
			$this->tagsContentModelValidator->validate(
				$definition->getContentModel(),
				$this->nodeNames( $tag->childNodes )
			)
		);
	}

	private function validateAttributes(
		DOMNamedNodeMap $attributes, $elementName, StatusValue $status
	) {
		$attributesDef = $this->registry->getAllAttributesForElement( $elementName );

		/** @var DOMAttr $attr */
		foreach ( $attributes as $attr ) {
			if ( !array_key_exists( $attr->nodeName, $attributesDef ) ) {
				$status->fatal( 'tei-validation-unknown-attribute', $attr->nodeName, $elementName );
				break;
			}
			$def = $attributesDef[$attr->nodeName];
			$status->merge( $def->getDatatype()->validate( $attr->nodeName, $attr->value ) );
		}

		// We valid
		foreach ( $attributesDef as $attrDef ) {
			if ( $attrDef->isMandatory() && $attributes->getNamedItem( $attrDef->getName() ) === null ) {
				$status->fatal(
					'tei-validation-missing-mandatory-attribute', $attrDef->getName(), $elementName
				);
			}
		}
	}

	private function nodeNames( DOMNodeList $list ) {
		$names = [];
		foreach ( $list as $node ) {
			// We ignore whitespace elements
			if ( $node instanceof DOMText && $node->isElementContentWhitespace() ) {
				continue;
			}
			$names[] = $node->nodeName;
		}
		return $names;
	}
}
