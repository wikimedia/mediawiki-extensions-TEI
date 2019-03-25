<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel\Evaluation;

use MediaWiki\Extension\Tei\Model\ContentModel\AlternateContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ElementRefContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\EmptyContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\RepeatableContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\SequenceContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\TextNodeContentModel;
use PHPUnit\Framework\TestCase;
use StatusValue;

/**
 * @group TEI
 * @covers \MediaWiki\Extension\Tei\Model\\ContentModel\Evaluation\ContentModelValidator
 * @covers \MediaWiki\Extension\Tei\Model\\ContentModel\Evaluation\NondeteministicFiniteAutomaton
 * @covers \MediaWiki\Extension\Tei\Model\\ContentModel\Evaluation\ThompsonAutomatonBuilder
 */
class ContentModelValidatorTest extends TestCase {

	private $groups = [
		'group.test1' => [ 'foo', 'bar' ]
	];

	public function validationProvider() {
		return [
			[
				new EmptyContentModel(),
				[],
				StatusValue::newGood()
			],
			[
				new EmptyContentModel(),
				[ 'foo' ],
				StatusValue::newFatal( 'unexpected-node', 'foo' )
			],
			[
				new ElementRefContentModel( 'foo' ),
				[ 'foo' ],
				StatusValue::newGood()
			],
			[
				new ElementRefContentModel( 'foo' ),
				[],
				StatusValue::newFatal( 'too-short' )
			],
			[
				new ElementRefContentModel( 'foo' ),
				[ 'foo', 'foo' ],
				StatusValue::newFatal( 'unexpected-node', 'foo' )
			],
			[
				new ElementRefContentModel( 'foo' ),
				[ 'bar' ],
				StatusValue::newFatal( 'unexpected-node', 'bar' )
			],
			[
				new TextNodeContentModel(),
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				new TextNodeContentModel(),
				[],
				StatusValue::newFatal( 'too-short' )
			],
			[
				new TextNodeContentModel(),
				[ 'foo' ],
				StatusValue::newFatal( 'unexpected-node', 'foo' )
			],
			[
				new TextNodeContentModel(),
				[ '#text', '#text' ],
				StatusValue::newFatal( 'unexpected-text' )
			],
			[
				new SequenceContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[ 'foo', 'bar' ],
				StatusValue::newGood()
			],
			[
				new SequenceContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[ 'foo' ],
				StatusValue::newFatal( 'too-short' )
			],
			[
				new SequenceContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[ 'foo', 'baz' ],
				StatusValue::newFatal( 'unexpected-node', 'baz' )
			],
			[
				new SequenceContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[ 'foo', 'bar', 'baz' ],
				StatusValue::newFatal( 'unexpected-node', 'baz' )
			],
			[
				new AlternateContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[ 'foo' ],
				StatusValue::newGood()
			],
			[
				new AlternateContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[ 'bar' ],
				StatusValue::newGood()
			],
			[
				new AlternateContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[],
				StatusValue::newFatal( 'too-short' )
			],
			[
				new AlternateContentModel(
					new ElementRefContentModel( 'foo' ),
					new ElementRefContentModel( 'bar' )
				),
				[ 'foo', 'bar' ],
				StatusValue::newFatal( 'unexpected-node', 'bar' )
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, 1 ),
				[],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, 1 ),
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, 1 ),
				[ '#text', '#text' ],
				StatusValue::newFatal( 'unexpected-text' )
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, 1 ),
				[ 'foo' ],
				StatusValue::newFatal( 'unexpected-node', 'foo' )
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, null ),
				[],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, null ),
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, null ),
				[ '#text', '#text' ],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 0, null ),
				[ 'foo' ],
				StatusValue::newFatal( 'unexpected-node', 'foo' )
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 1, null ),
				[],
				StatusValue::newFatal( 'too-short' )
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 1, null ),
				[ '#text' ],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 1, null ),
				[ '#text', '#text' ],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 1, null ),
				[ '#text', '#text', '#text' ],
				StatusValue::newGood()
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 1, null ),
				[ '#text', 'foo' ],
				StatusValue::newFatal( 'unexpected-node', 'foo' )
			],
			[
				new RepeatableContentModel( new TextNodeContentModel(), 1, null ),
				[ 'foo', '#text' ],
				StatusValue::newFatal( 'unexpected-node', 'foo' )
			],
		];
	}

	/**
	 * @dataProvider validationProvider
	 */
	public function testValidation(
		ContentModel $contentModel, array $labels, StatusValue $expectedStatus
	) {
		$validator = new ContentModelValidator( '', function ( $groupName ) {
			return $this->groups[$groupName];
		} );
		$this->assertEquals( $expectedStatus, $validator->validate( $contentModel, $labels ) );
	}
}
