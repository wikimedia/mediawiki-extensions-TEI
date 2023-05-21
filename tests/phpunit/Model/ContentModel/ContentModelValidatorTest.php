<?php

namespace MediaWiki\Extension\Tei\Model\Content;

use MediaWiki\Extension\Tei\Model\TeiRegistry;
use PHPUnit\Framework\TestCase;
use StatusValue;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\Model\Content\ContentValidator
 * @covers \MediaWiki\Extension\Tei\Model\Content\NondeteministicFiniteAutomaton
 * @covers \MediaWiki\Extension\Tei\Model\Content\ContentValidatorFactory
 */
class ContentModelValidatorTest extends TestCase {

	private $groups = [
		'group.test1' => [ 'foo', 'bar' ]
	];

	public static function validationProvider() {
		return [
			[
				[ 'type' => 'empty' ],
				[],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'empty' ],
				[ 'foo' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'foo' )
			],
			[
				[ 'type' => 'elementRef', 'key' => 'foo' ],
				[ 'foo' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'elementRef', 'key' => 'foo' ],
				[],
				StatusValue::newFatal( 'tei-validation-element-content-too-short', 'foo' )
			],
			[
				[ 'type' => 'elementRef', 'key' => 'foo' ],
				[ 'foo', 'foo' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'foo' )
			],
			[
				[ 'type' => 'elementRef', 'key' => 'foo' ],
				[ 'bar' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'bar' )
			],
			[
				[ 'type' => 'textNode' ],
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' ],
				[],
				StatusValue::newFatal( 'tei-validation-element-content-too-short', '#text' )
			],
			[
				[ 'type' => 'textNode' ],
				[ 'foo' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'foo' )
			],
			[
				[ 'type' => 'textNode' ],
				[ '#text', '#text' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-text' )
			],
			[
				[ 'type' => 'sequence', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[ 'foo', 'bar' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'sequence', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[ 'foo' ],
				StatusValue::newFatal( 'tei-validation-element-content-too-short', 'bar' )
			],
			[
				[ 'type' => 'sequence', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[ 'foo', 'baz' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'baz' )
			],
			[
				[ 'type' => 'sequence', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[ 'foo', 'bar', 'baz' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'baz' )
			],
			[
				[ 'type' => 'alternate', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[ 'foo' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'alternate', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[ 'bar' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'alternate', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[],
				StatusValue::newFatal( 'tei-validation-element-content-too-short', 'foo, bar' )
			],
			[
				[ 'type' => 'alternate', 'content' => [
					[ 'type' => 'elementRef', 'key' => 'foo' ],
					[ 'type' => 'elementRef', 'key' => 'bar' ]
				] ],
				[ 'foo', 'bar' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'bar' )
			],
			[
				[ 'type' => 'textNode', 'minOccurs' => 0, 'maxOccurs' => 1 ],
				[],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode', 'minOccurs' => 0, 'maxOccurs' => 1 ],
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 0, 'maxOccurs' => 1 ],
				[ '#text', '#text' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-text' )
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 0, 'maxOccurs' => 1 ],
				[ 'foo' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'foo' )
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 0, 'maxOccurs' => null ],
				[],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 0, 'maxOccurs' => null ],
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 0, 'maxOccurs' => null ],
				[ '#text', '#text' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 0, 'maxOccurs' => null ],
				[ 'foo' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'foo' )
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 1, 'maxOccurs' => null ],
				[],
				StatusValue::newFatal( 'tei-validation-element-content-too-short', '#text' )
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 1, 'maxOccurs' => null ],
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 1, 'maxOccurs' => null ],
				[ '#text', '#text' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 1, 'maxOccurs' => null ],
				[ '#text', '#text', '#text' ],
				StatusValue::newGood()
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 1, 'maxOccurs' => null ],
				[ '#text', 'foo' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'foo' )
			],
			[
				[ 'type' => 'textNode' , 'minOccurs' => 1, 'maxOccurs' => null ],
				[ 'foo', '#text' ],
				StatusValue::newFatal( 'tei-validation-element-content-unexpected-node', 'foo' )
			],
		];
	}

	/**
	 * @dataProvider validationProvider
	 */
	public function testValidation(
		array $content, array $labels, StatusValue $expectedStatus
	) {
		$factory = new ContentValidatorFactory(
			new TeiRegistry( [
				'elements' => [
					'root' => [
						'content' => $content
					]
				]
			] )
		);
		$validator = $factory->getForElement( 'root' );
		$this->assertEquals( $expectedStatus, $validator->validate( $labels ) );
	}
}
