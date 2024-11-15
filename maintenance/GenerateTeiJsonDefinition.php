<?php

namespace MediaWiki\Extension\Tei;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Maintenance;
use MediaWiki\MediaWikiServices;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' )
	: __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';

/**
 * @license GPL-2.0-or-later
 *
 * Generates a TEI JSON definition file
 */
class GenerateTeiJsonDefinition extends Maintenance {

	/**
	 * @var DOMXPath
	 */
	private $domXPath;

	private $result = [
		'start' => [],
		'macros' => [],
		'classes' => [],
		'elements' => []
	];

	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Generates a JSON TEI definition file used ' .
							  'by the TEI extension for validation and documentation' );

		$this->addArg(
			'customization_file',
			'The customization file. By default data/mw_customization.odd',
			false
		);
		$this->addArg(
			'customization_name',
			'The customization name. ' .
			'It is the value of the intent attribute of the <schemaSpec> tag. ' .
			'By default "tei_mediawiki"',
			false
		);
		$this->addArg(
			'output_file',
			'The JSON definition file to output. By default data/mw_tei_json_definition.json',
			false
		);
		$this->requireExtension( 'TEI' );
	}

	public function execute() {
		$this->loadInputOddDom();
		$this->importSchema( $this->getArg( 1, 'tei_mediawiki' ) );

		file_put_contents(
			$this->getArg( 2, __DIR__ . '/../data/mw_tei_json_definition.json' ),
			json_encode( $this->deepSortArray( $this->result ), JSON_PRETTY_PRINT )
		);
	}

	private function importSchema( $schemaIdent ) {
		foreach ( $this->getElementsWithIdent( 'schemaSpec', $schemaIdent ) as $schemaSpec ) {
			$this->importSchemaSpec( $schemaSpec );
		}
	}

	private function importSchemaSpec( DOMElement $schemaSpec ) {
		$this->result['start'] = $this->getElementList( $schemaSpec, 'start' );

		/** @var DOMElement $childNode */
		foreach ( $schemaSpec->childNodes as $childNode ) {
			switch ( $childNode->nodeName ) {
				case 'moduleSpec':
					$this->importModuleSpec( $childNode );
					break;
				case 'moduleRef':
					$this->importModule(
						$childNode->getAttribute( 'key' ),
						$childNode->hasAttribute( 'include' )
							? $this->getElementList( $childNode, 'include' )
							: null,
						$childNode->hasAttribute( 'except' )
							? $this->getElementList( $childNode, 'except' )
							: []
					);
					break;
				case 'macros':
					$this->importMacroSpec( $childNode );
					break;
				case 'classes':
					$this->importClassSpec( $childNode );
					break;
				case 'elementRef':
					$this->importElement( $childNode->getAttribute( 'key' ) );
					break;
				case 'elements':
					$this->importElementSpec( $childNode );
					break;
			}
		}
	}

	private function importModule( $moduleIdent, ?array $include = null, array $except = [] ) {
		foreach ( $this->getElementsWithIdent( 'moduleSpec', $moduleIdent ) as $moduleSpec ) {
			$this->importModuleSpec( $moduleSpec, $include, $except );
		}
	}

	private function importModuleSpec(
		DOMElement $moduleSpec, ?array $include = null, array $except = []
	) {
		$moduleIdent = $moduleSpec->getAttribute( 'ident' );

		foreach ( $this->getElementsOfModule( 'macroSpec', $moduleIdent ) as $macroSpec ) {
			$this->importMacroSpec( $macroSpec );
		}
		foreach ( $this->getElementsOfModule( 'classSpec', $moduleIdent ) as $classSpec ) {
			$this->importClassSpec( $classSpec );
		}

		if ( $include === null ) {
			/** @var DOMElement $elementSpec */
			foreach ( $this->getElementsOfModule( 'elementSpec', $moduleIdent ) as $elementSpec ) {
				if ( !in_array( $elementSpec->getAttribute( 'ident' ), $except ) ) {
					$this->importElementSpec( $elementSpec );
				}
			}
		} else {
			foreach ( $include as $elementIdent ) {
				foreach ( $this->getElementsWithIdent( 'elementSpec', $elementIdent ) as $elementSpec ) {
					if ( !in_array( $elementSpec->getAttribute( 'ident' ), $except ) ) {
						$this->importElementSpec( $elementSpec );
					}
				}
			}
		}
	}

	private function importMacroSpec( DOMElement $macroSpec ) {
		$this->result['macros'][$macroSpec->getAttribute( 'ident' )] = [
			'module' => $macroSpec->getAttribute( 'module' ),
			'content' => $this->convertContent( $macroSpec )
		];
	}

	private function importClassSpec( DOMElement $classSpec ) {
		$ident = $classSpec->getAttribute( 'ident' );

		switch ( $this->getMode( $classSpec ) ) {
			case 'add':
				$this->result['classes'][$ident] = [
					'module' => $classSpec->getAttribute( 'module' ),
					'classes' => [],
					'attributes' => []
				];
				$this->convertClasses( $classSpec, $this->result['classes'][$ident]['classes'] );
				$this->convertAttributes( $classSpec, $this->result['classes'][$ident] );
				break;
			case 'delete':
				unset( $this->result['classes'][$ident] );
				break;
			case 'change':
				if ( !array_key_exists( $ident, $this->result['classes'] ) ) {
					$this->error( "No class found named '$ident'" );
				}
				$this->convertClasses( $classSpec, $this->result['classes'][$ident]['classes'] );
				$this->convertAttributes( $classSpec, $this->result['classes'][$ident] );
				break;
		}
	}

	private function importElement( $elementIdent ) {
		foreach ( $this->getElementsWithIdent( 'elementSpec', $elementIdent ) as $elementSpec ) {
			$this->importElementSpec( $elementSpec );
		}
	}

	private function importElementSpec( DOMElement $elementSpec ) {
		$ident = $elementSpec->getAttribute( 'ident' );

		switch ( $this->getMode( $elementSpec ) ) {
			case 'add':
				$this->result['elements'][$ident] = [
					'module' => $elementSpec->getAttribute( 'module' ),
					'content' => $this->convertContent( $elementSpec ),
					'classes' => [],
					'attributes' => []
				];
				$this->convertClasses( $elementSpec, $this->result['elements'][$ident]['classes'] );
				$this->convertAttributes( $elementSpec, $this->result['elements'][$ident] );
				break;
			case 'delete':
				unset( $this->result['elements'][$ident] );
				break;
			case 'change':
				if ( !array_key_exists( $ident, $this->result['elements'] ) ) {
					$this->error( "No element found named '$ident'" );
				}

				$newContent = $this->convertContent( $elementSpec );
				if ( $newContent !== null ) {
					$this->result['elements'][$ident]['content'] = $newContent;
				}
				$this->convertClasses( $elementSpec, $this->result['elements'][$ident]['classes'] );
				$this->convertAttributes( $elementSpec, $this->result['elements'][$ident] );
				break;
		}
	}

	private function getMode( DOMElement $element ) {
		return $element->hasAttribute( 'mode' )
			? $element->getAttribute( 'mode' )
			: 'add';
	}

	private function convertClasses( DOMElement $element, array &$classes ) {
		/** @var DOMElement $memberOf */
		foreach ( $this->domXPath->query( 'tei:classes/tei:memberOf', $element ) as $memberOf ) {
			switch ( $this->getMode( $memberOf ) ) {
				case 'add':
					$classes[] = $memberOf->getAttribute( 'key' );
					break;
				case 'delete':
					// @phan-suppress-next-line PhanTypeMismatchArgumentInternal
					array_splice( $classes, array_search(
						$memberOf->getAttribute( 'key' ), $classes
					), 1 );
					break;
			}
		}
	}

	private function convertAttributes( DOMElement $elementSpec, array &$elementDef ) {
		foreach ( $this->domXPath->query( 'tei:attList/tei:attDef', $elementSpec ) as $attDef ) {
			$this->convertAttribute( $attDef, $elementDef );
		}
	}

	private function convertAttribute( DOMElement $attDef, array &$elementDef ) {
		$ident = $attDef->getAttribute( 'ident' );

		switch ( $this->getMode( $attDef ) ) {
			case 'add':
				$elementDef['attributes'][$ident] = [
					'datatype' => $this->convertDatatype( $attDef )
				];
				$elementDef['attributes'][$ident]['usage'] = $attDef->hasAttribute( 'usage' )
					? $attDef->getAttribute( 'usage' )
					: 'opt';
				$valList = $this->convertValList( $attDef );
				if ( $valList !== null ) {
					$elementDef['attributes'][$ident]['valList'] = $valList;
				}
				break;
			case 'delete':
				unset( $elementDef['attributes'][$ident] );
				break;
			case 'change':
				if ( !array_key_exists( $ident, $elementDef['attributes'] ) ) {
					$elementDef['attributes'][$ident] = $this->getAttributeDef( $ident, $elementDef );
				}
				if ( !array_key_exists( $ident, $elementDef['attributes'] ) ) {
					$this->error( "No attribute found named '$ident'" );
				}

				if ( $attDef->hasAttribute( 'usage' ) ) {
					$elementDef['attributes'][$ident]['usage'] = $attDef->getAttribute( 'usage' );
				}
				$newDatatype = $this->convertDatatype( $attDef );
				if ( $newDatatype !== null ) {
					$elementDef['attributes'][$ident]['datatype'] = $newDatatype;
				}
				$newValList = $this->convertValList( $attDef );
				if ( $newValList !== null ) {
					$elementDef['attributes'][$ident]['valList'] = $newValList;
				}
				break;
		}
	}

	private function convertDatatype( DOMElement $attDef ) {
		/** @var DOMElement $datatype */
		foreach ( $this->domXPath->query( 'tei:datatype', $attDef ) as $datatype ) {
			$result = [];

			if ( $datatype->hasAttribute( 'minOccurs' ) ) {
				$result['minOccurs'] = (int)$datatype->getAttribute( 'minOccurs' );
			}

			if ( $datatype->hasAttribute( 'maxOccurs' ) ) {
				$maxOccursValue = $datatype->getAttribute( 'maxOccurs' );
				$result['maxOccurs'] = ( $maxOccursValue === 'unbounded' ) ? null : (int)$maxOccursValue;
			}

			/** @var DOMElement $dataRef */
			foreach ( $this->domXPath->query( 'tei:dataRef', $datatype ) as $dataRef ) {
				if ( $dataRef->hasAttribute( 'key' ) ) {
					$result['dataRef']['key'] = $dataRef->getAttribute( 'key' );
				}
				if ( $dataRef->hasAttribute( 'name' ) ) {
					$result['dataRef']['name'] = $dataRef->getAttribute( 'name' );
				}
			}

			return $result;
		}
		return null;
	}

	private function convertValList( DOMElement $attDef ) {
		/** @var DOMElement $valList */
		foreach ( $this->domXPath->query( 'tei:valList', $attDef ) as $valList ) {
			$result = [
				'type' => $valList->hasAttribute( 'type' ) ? $valList->getAttribute( 'type' ) : 'open',
				'items' => []
			];

			/** @var DOMElement $valItem */
			foreach ( $this->domXPath->query( 'tei:valItem', $valList ) as $valItem ) {
				$result['items'][$valItem->getAttribute( 'ident' )] = [];
			}

			return $result;
		}
		return null;
	}

	private function getAttributeDef( $attributeIdent, array &$elementDef ) {
		if ( array_key_exists( $attributeIdent, $elementDef['attributes'] ) ) {
			return $elementDef['attributes'][$attributeIdent];
		}
		foreach ( $elementDef['classes'] as $class ) {
			if ( array_key_exists( $class, $this->result['classes'] ) ) {
				$attributeDef = $this->getAttributeDef( $attributeIdent, $this->result['classes'][$class] );
				if ( $attributeDef !== null ) {
					return $attributeDef;
				}
			}
		}
	}

	private function convertContent( DOMElement $elementSpec ) {
		foreach ( $this->domXPath->query( 'tei:content/*', $elementSpec ) as $contentModel ) {
			return $this->convertContentModel( $contentModel );
		}
		return null;
	}

	private function convertContentModel( DOMElement $element ) {
		$result = [
			'type' => $element->nodeName
		];

		if ( $element->hasAttribute( 'key' ) ) {
			$result['key'] = $element->getAttribute( 'key' );
		}

		if ( $element->hasAttribute( 'minOccurs' ) ) {
			$result['minOccurs'] = (int)$element->getAttribute( 'minOccurs' );
		}

		if ( $element->hasAttribute( 'maxOccurs' ) ) {
			$maxOccursValue = $element->getAttribute( 'maxOccurs' );
			$result['maxOccurs'] = ( $maxOccursValue === 'unbounded' ) ? null : (int)$maxOccursValue;
		}

		foreach ( $element->childNodes as $childNode ) {
			if ( $childNode instanceof DOMElement ) {
				$result['content'][] = $this->convertContentModel( $childNode );
			}
		}

		return $result;
	}

	private function getElementsWithIdent( $nodeName, $ident ) {
		foreach ( $this->domXPath->query(
			'//tei:' . $nodeName . '[@ident="' . $ident . '"]'
		) as $element ) {
			yield $element;
		}
	}

	private function getElementsOfModule( $nodeName, $moduleIdent ) {
		foreach ( $this->domXPath->query(
			'//tei:' . $nodeName . '[@module="' . $moduleIdent . '"]'
		) as $element ) {
			yield $element;
		}
	}

	private function getElementList( DOMElement $element, $attribute ) {
		return array_filter( array_map( 'trim', explode( ' ', $element->getAttribute( $attribute ) ) ) );
	}

	private function loadInputOddDom() {
		$fullTeiDefinitionDom = $this->loadP5FullDefinition();
		$customizationDom = $this->readXmlFile(
			$this->getArg( 0, __DIR__ . '/../data/mw_customization.odd' )
		);
		$fullTeiDefinitionDom->documentElement->appendChild( $fullTeiDefinitionDom->importNode(
			$customizationDom->documentElement, true
		) );
		$this->domXPath = new DOMXpath( $fullTeiDefinitionDom );
		$this->domXPath->registerNamespace( 'tei', 'http://www.tei-c.org/ns/1.0' );
	}

	private function readXmlFile( $xmlFileName ) {
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->load( $xmlFileName );
		return $dom;
	}

	protected function loadP5FullDefinition() {
		$proxy = $this->getConfig()->get( 'HTTPProxy' );
		$client = MediaWikiServices::getInstance()->getHttpRequestFactory()->createMultiClient( [
			'proxy' => $proxy
		] );

		[ $code, $desc, $header, $body, $err ] = $client->run( [
			'method' => 'GET',
			'url' => 'http://www.tei-c.org/release/xml/tei/odd/p5subset.xml'
		] );

		if ( $code === 200 && is_string( $body ) ) {
			$dom = new DOMDocument();
			$dom->preserveWhiteSpace = false;
			$dom->loadXML( $body );
			return $dom;
		}

		$errorMessage = 'Error while retrieving http://www.tei-c.org/release/xml/tei/odd/p5subset.xml';
		if ( $err ) {
			$errorMessage .= ": " . $err;
		}
		if ( $desc ) {
			$errorMessage .= ": " . $desc;
		}
		if ( is_string( $body ) ) {
			$errorMessage .= "\n\n" . $body;
		}
		$this->fatalError( $errorMessage );
	}

	private function deepSortArray( &$array ) {
		if ( !is_array( $array ) ) {
			return $array;
		}
		foreach ( $array as $key => $value ) {
			$array[$key] = $this->deepSortArray( $value );
		}
		ksort( $array );
		return $array;
	}
}

$maintClass = GenerateTeiJsonDefinition::class;
require_once RUN_MAINTENANCE_IF_MAIN;
