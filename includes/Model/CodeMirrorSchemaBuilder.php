<?php


namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\Model\ContentModel\AlternateContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ClassRefContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ElementRefContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\RepeatableContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\SequenceContentModel;
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
			'!top' => [ 'text' ]
		];

		foreach ( $this->registry->getAllElementsSpec() as $elementSpec ) {
			$schema[$elementSpec->getName()] = $this->convertElementSpec( $elementSpec );
		}

		return $schema;
	}

	private function convertElementSpec( ElementSpec $elementSpec ) {
		$schema = [
			'attrs' => [],
			'children' => $this->getAllTagsFromContentModels( $elementSpec->getContentModel() )
		];

		foreach (
			$this->registry->getAllAttributesForElement( $elementSpec->getName() ) as $attributeDef
		) {
			$schema['attrs'][$attributeDef->getName()] = $this->convertAttributeDef( $attributeDef );
		}

		return $schema;
	}

	private function convertAttributeDef( AttributeDef $attributeDef ) {
		$datatype = $attributeDef->getDatatype();
		if ( $datatype instanceof EnumerationDatatype ) {
			return $datatype->getPossibleValues();
		}
		return null;
	}

	private function getAllTagsFromContentModels( ContentModel $contentModel ) {
		$tags = [];
		$this->addAllTagsFromContentModels( $contentModel, $tags );
		return array_values( array_unique( $tags ) );
	}

	private function addAllTagsFromContentModels( ContentModel $contentModel, array &$tags ) {
		if ( $contentModel instanceof AlternateContentModel ) {
			foreach ( $contentModel->getAlternate() as $alternate ) {
				$this->addAllTagsFromContentModels( $alternate, $tags );
			}
		} elseif ( $contentModel instanceof ClassRefContentModel ) {
			foreach ( $this->registry->getElementNamesInClass( $contentModel->getKey() ) as $tag ) {
				$tags[] = $tag;
			}
		} elseif ( $contentModel instanceof ElementRefContentModel ) {
			$tags[] = $contentModel->getKey();
		} elseif ( $contentModel instanceof RepeatableContentModel ) {
			$this->addAllTagsFromContentModels( $contentModel->getElement(), $tags );
		} elseif ( $contentModel instanceof SequenceContentModel ) {
			foreach ( $contentModel->getSequence() as $alternate ) {
				$this->addAllTagsFromContentModels( $alternate, $tags );
			}
		}
	}
}
