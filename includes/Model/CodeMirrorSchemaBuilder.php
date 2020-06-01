<?php

namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\Model\Datatype\EnumerationDatatype;

/**
 * Creates the TEI schema to be used by CodeMirror
 *
 * @license GPL-2.0-or-later
 * @author  Thomas Pellissier Tanon
 */
class CodeMirrorSchemaBuilder {

	/**
	 * @var TeiRegistry
	 */
	private $registry;

	/**
	 * @param TeiRegistry $registry
	 */
	public function __construct( TeiRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * @return array
	 */
	public function generateSchema() {
		$schema = [
			'!top' => $this->registry->getPossibleRootElements(),
			'!attrs' => $this->convertAttributesForEntityIdent( 'att.global', [] )
		];
		$globalAttributes = array_keys( $schema['!attrs'] );

		foreach ( $this->registry->getAllElementsSpec() as $elementSpec ) {
			$schema[$elementSpec->getName()] = $this->convertElementSpec( $elementSpec, $globalAttributes );
		}

		return $schema;
	}

	private function convertElementSpec( ElementSpec $elementSpec, $attributesFilter ) {
		return [
			'attrs' => $this->convertAttributesForEntityIdent( $elementSpec->getName(), $attributesFilter ),
			'children' => $this->getAllTagsFromContent( $elementSpec->getContent() )
		];
	}

	private function convertAttributesForEntityIdent( $entityIdent, $attributesFilter ) {
		$attrs = [];
		foreach (
			$this->registry->getAllAttributesForEntityIdent( $entityIdent ) as $attributeDef
		) {
			if ( !in_array( $attributeDef->getName(), $attributesFilter ) ) {
				$attrs[$attributeDef->getName()] = $this->convertAttributeDef( $attributeDef );
			}
		}
		return $attrs;
	}

	private function convertAttributeDef( AttributeDef $attributeDef ) {
		$datatype = $attributeDef->getDatatype();
		if ( $datatype instanceof EnumerationDatatype ) {
			return $datatype->getPossibleValues();
		}
		return null;
	}

	private function getAllTagsFromContent( array $content ) {
		$tags = [];
		$this->addAllTagsFromContent( $content, $tags );
		return array_values( array_unique( $tags ) );
	}

	private function addAllTagsFromContent( array $content, array &$tags ) {
		switch ( $content['type'] ) {
			case 'classRef':
				foreach ( $this->registry->getElementIdentsInClass( $content['key'] ) as $tag ) {
					$tags[] = $tag;
				}
				break;
			case 'elementRef':
				$tags[] = $content['key'];
				break;
			case 'macroRef':
				$this->addAllTagsFromContent(
					$this->registry->getMacroSpecFromIdent( $content['key'] )->getContent(),
					$tags
				);
				break;
		}
		if ( array_key_exists( 'content', $content ) ) {
			foreach ( $content['content'] as $child ) {
				$this->addAllTagsFromContent( $child, $tags );
			}
		}
	}
}
