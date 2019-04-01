<?php

namespace MediaWiki\Extension\Tei\Model;

use DOMAttr;
use DOMComment;
use DOMDocument;
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
			'tei-validation-element-content-',
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
		$this->validateNode( $root, $status );
	}

	private function validateNode( DOMNode $node, StatusValue $status ) {
		if ( $node instanceof DOMText || $node instanceof DOMComment ) {
			return;
		}

		// We validate namespace
		if ( $node->namespaceURI !== TeiRegistry::TEI_NAMESPACE ) {
			$status->fatal( 'tei-validation-wrong-namespace', $node->namespaceURI, $node->getLineNo() );
		}

		// We validate the tag based on its definition
		try {
			$definition = $this->registry->getElementSpecFromName( $node->nodeName );
			$this->validateElementUsingDefinition( $node, $definition, $status );
		} catch ( OutOfBoundsException $e ) {
			$status->fatal(
				'tei-validation-unknown-tag', $node->nodeName
			);
		}

		// We do a recursive call
		foreach ( $node->childNodes as $childNode ) {
			$this->validateNode( $childNode, $status );
		}
	}

	private function validateElementUsingDefinition(
		DOMNode $node, ElementSpec $definition, StatusValue $status
	) {
		// Attributes
		$this->validateAttributes( $node, $status );

		// Children nodes
		$status->merge(
			$this->tagsContentModelValidator->validate(
				$definition->getContentModel(),
				$this->nodeNames( $node->childNodes ),
				$node->nodeName,
				$node->getLineNo()
			)
		);
	}

	private function validateAttributes(
		DOMNode $node, StatusValue $status
	) {
		$attributesDef = $this->registry->getAllAttributesForElement( $node->nodeName );

		/** @var DOMAttr $attr */
		foreach ( $node->attributes as $attr ) {
			if ( !array_key_exists( $attr->nodeName, $attributesDef ) ) {
				$status->fatal(
					'tei-validation-unknown-attribute',
					$attr->nodeName, $node->nodeName, $attr->getLineNo()
				);
				break;
			}
			$def = $attributesDef[$attr->nodeName];
			$status->merge( $def->getDatatype()->validate( $attr->nodeName, $attr->value ) );
		}

		// We valid
		foreach ( $attributesDef as $attrDef ) {
			if (
				$attrDef->isMandatory() &&
				$node->attributes->getNamedItem( $attrDef->getName() ) === null
			) {
				$status->fatal(
					'tei-validation-missing-mandatory-attribute',
					$attrDef->getName(), $node->nodeName, $node->getLineNo()
				);
			}
		}
	}

	private function nodeNames( DOMNodeList $list ) {
		$names = [];
		foreach ( $list as $node ) {
			// We ignore whitespace elements and comments
			if (
				$node instanceof DOMText && $node->isElementContentWhitespace() ||
				$node instanceof DOMComment
			) {
				continue;
			}
			$names[] = $node->nodeName;
		}
		return $names;
	}
}
