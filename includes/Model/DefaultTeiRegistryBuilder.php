<?php

namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\Model\ContentModel\AlternateContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ClassRefContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ElementRefContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\EmptyContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\RepeatableContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\SequenceContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\TextNodeContentModel;
use MediaWiki\Extension\Tei\Model\Datatype\CountDatatype;
use MediaWiki\Extension\Tei\Model\Datatype\EnumerationDatatype;
use MediaWiki\Extension\Tei\Model\Datatype\IDDatatype;
use MediaWiki\Extension\Tei\Model\Datatype\LanguageDatatype;

/**
 * @license GPL-2.0-or-later
 *
 * Builds TEI registry filled with the tags supported by the MediaWiki extension
 */
class DefaultTeiRegistryBuilder {

	/**
	 * @return TeiRegistry
	 */
	public function build() {
		$registry = new TeiRegistry();

		$registry->registerClass( 'att.global.rendition', [] );
		$registry->registerClass( 'att.global.linking', [] );
		$registry->registerClass( 'att.global.analytic', [] );
		$registry->registerClass( 'att.global.facs', [] );
		$registry->registerClass( 'att.global.responsibility', [] );
		$registry->registerClass( 'att.global.source', [] );
		$registry->registerClass( 'att.global', [
			'att.global.rendition',
			'att.global.linking',
			'att.global.analytic',
			'att.global.facs',
			'att.global.responsibility',
			'att.global.source'
		], [
			new AttributeDef( 'xml:id', new IDDatatype() ),
			new AttributeDef( 'xml:lang', new LanguageDatatype() )
		] );
		$registry->registerClass( 'att.breaking', [] );
		$registry->registerClass( 'att.declaring', [] );
		$registry->registerClass( 'att.ranging', [] );
		$registry->registerClass( 'att.dimensions', [ 'att.ranging' ] );
		$registry->registerClass( 'att.edition', [] );
		$registry->registerClass( 'att.fragmentable', [] );
		$registry->registerClass( 'att.sortable', [] );
		$registry->registerClass( 'att.spanning', [] );
		$registry->registerClass( 'att.tableDecoration', [] );
		$registry->registerClass( 'att.transcriptional', [] );
		$registry->registerClass( 'att.typed', [] );
		$registry->registerClass( 'att.written', [] );
		$registry->registerClass( 'att.divLike', [ 'att.fragmentable' ] );

		$registry->registerClass( 'model.common', [] );
		$registry->registerClass( 'model.choicePart', [] );
		$registry->registerClass( 'model.phrase', [] );
		$registry->registerClass( 'model.limitedPhrase', [] );
		$registry->registerClass( 'model.frontPart', [] );
		$registry->registerClass( 'model.divBottom', [] );
		$registry->registerClass( 'model.divBottomPart', [ 'model.divBottom' ] );
		$registry->registerClass( 'model.divGenLike', [] );
		$registry->registerClass( 'model.divLike', [] );
		$registry->registerClass( 'model.div1Like', [] );
		$registry->registerClass( 'model.divPart', [ 'model.common' ] );
		$registry->registerClass( 'model.divTop', [] );
		$registry->registerClass( 'model.global', [] );
		$registry->registerClass( 'model.gLike', [] );
		$registry->registerClass( 'model.highlighted', [ 'model.phrase' ] );
		$registry->registerClass( 'model.hiLike', [ 'model.highlighted', 'model.limitedPhrase' ] );
		$registry->registerClass( 'model.inter', [ 'model.common' ] );
		$registry->registerClass( 'model.listLike', [ 'model.inter' ] );
		$registry->registerClass( 'model.lLike', [ 'model.divPart' ] );
		$registry->registerClass( 'model.milestoneLike', [ 'model.global' ] );
		$registry->registerClass( 'model.pLike', [ 'model.divPart' ] );
		$registry->registerClass( 'model.pLike.front', [] );
		$registry->registerClass( 'model.pPart.edit', [ 'model.phrase' ] );
		$registry->registerClass(
			'model.pPart.editorial',
			[ 'model.pPart.edit', 'model.limitedPhrase' ]
		);
		$registry->registerClass( 'model.pPart.transcriptional', [ 'model.pPart.edit' ] );
		$registry->registerClass( 'model.qLike', [ 'model.inter' ] );
		$registry->registerClass( 'model.resourceLike', [] );

		// macro.paraContent
		$macroParaContent = new RepeatableContentModel( new AlternateContentModel(
			new TextNodeContentModel(),
			new ClassRefContentModel( 'model.gLike' ),
			new ClassRefContentModel( 'model.phrase' ),
			new ClassRefContentModel( 'model.inter' ),
			new ClassRefContentModel( 'model.global' ),
			// TODO: introduce new ElementRefContentModel('lg'),
			new ClassRefContentModel( 'model.lLike' )
		), 0, null );

		// macro.specialPara
		$macroSpecialPara = new RepeatableContentModel( new AlternateContentModel(
			new TextNodeContentModel(),
			new ClassRefContentModel( 'model.gLike' ),
			new ClassRefContentModel( 'model.phrase' ),
			new ClassRefContentModel( 'model.inter' ),
			new ClassRefContentModel( 'model.divPart' ),
			new ClassRefContentModel( 'model.global' )
		), 0, null );

		$macroPhraseSeq = new RepeatableContentModel( new AlternateContentModel(
			new TextNodeContentModel(),
			new ClassRefContentModel( 'model.gLike' ),
			new ClassRefContentModel( 'model.qLike' ),
			new ClassRefContentModel( 'model.phrase' ),
			new ClassRefContentModel( 'model.global' )
		), 0, null );

		// <classRef key="model.global" minOccurs="0" maxOccurs="unbounded"/>
		$anyTimeModelGlobal = new RepeatableContentModel(
			new ClassRefContentModel( 'model.global' ),
			0, null
		);

		$registry->registerElement(
			'abbr',
			[ 'att.global', 'model.pPart.editorial', 'model.choicePart', 'att.typed' ],
			$macroPhraseSeq
		);

		$registry->registerElement(
			'back',
			[ 'att.global', 'att.declaring' ],
			new SequenceContentModel(
				new RepeatableContentModel( new AlternateContentModel(
					new ClassRefContentModel( 'model.frontPart' ),
					new ClassRefContentModel( 'model.pLike.front' ),
					new ClassRefContentModel( 'model.pLike' ),
					new ClassRefContentModel( 'model.listLike' ),
					new ClassRefContentModel( 'model.global' )
				), 0, null ),
				new RepeatableContentModel( new AlternateContentModel(
					new SequenceContentModel(
						new ClassRefContentModel( 'model.div1Like' ),
						new RepeatableContentModel( new AlternateContentModel(
							new ClassRefContentModel( 'model.frontPart' ),
							new ClassRefContentModel( 'model.div1Like' ),
							new ClassRefContentModel( 'model.global' )
						), 0, null )
					),
					new SequenceContentModel(
						new ClassRefContentModel( 'model.divLike' ),
						new RepeatableContentModel( new AlternateContentModel(
							new ClassRefContentModel( 'model.divLike' ),
							new ClassRefContentModel( 'model.frontPart' ),
							new ClassRefContentModel( 'model.global' )
						), 0, null )
					)
				), 0, 1 ),
				new RepeatableContentModel( new AlternateContentModel(
					new ClassRefContentModel( 'model.divBottomPart' ),
					new RepeatableContentModel( new AlternateContentModel(
						new ClassRefContentModel( 'model.divBottomPart' ),
						new ClassRefContentModel( 'model.global' )
					), 0, null )
				), 0, 1 )
			)
		);

		$registry->registerElement(
			'body',
			[ 'att.global', 'att.declaring' ],
			new SequenceContentModel(
				$anyTimeModelGlobal,
				new RepeatableContentModel( new SequenceContentModel(
					new ClassRefContentModel( 'model.divTop' ),
					new RepeatableContentModel( new AlternateContentModel(
						new ClassRefContentModel( 'model.global' ),
						new ClassRefContentModel( 'model.divTop' )
					), 0, null )
				), 0, 1 ),
				new RepeatableContentModel( new SequenceContentModel(
					new ClassRefContentModel( 'model.divGenLike' ),
					new RepeatableContentModel( new AlternateContentModel(
						new ClassRefContentModel( 'model.global' ),
						new ClassRefContentModel( 'model.divGenLike' )
					), 0, null )
				), 0, 1 ),
				new AlternateContentModel(
					new RepeatableContentModel( new SequenceContentModel(
						new ClassRefContentModel( 'model.divLike' ),
						new RepeatableContentModel( new AlternateContentModel(
							new ClassRefContentModel( 'model.global' ),
							new ClassRefContentModel( 'model.divGenLike' )
						), 0, null )
					), 1, null ),
					new RepeatableContentModel( new SequenceContentModel(
						new ClassRefContentModel( 'model.div1Like' ),
						new RepeatableContentModel( new AlternateContentModel(
							new ClassRefContentModel( 'model.global' ),
							new ClassRefContentModel( 'model.divGenLike' )
						), 0, null )
					), 1, null ),
					new SequenceContentModel(
						new RepeatableContentModel( new SequenceContentModel(
							new ClassRefContentModel( 'model.common' ),
							$anyTimeModelGlobal
						), 1, null ),
						new RepeatableContentModel( new AlternateContentModel(
							new RepeatableContentModel( new SequenceContentModel(
								new ClassRefContentModel( 'model.divLike' ),
								new RepeatableContentModel( new AlternateContentModel(
									new ClassRefContentModel( 'model.global' ),
									new ClassRefContentModel( 'model.divGenLike' )
								), 0, null )
							), 1, null ),
							new RepeatableContentModel( new SequenceContentModel(
								new ClassRefContentModel( 'model.div1Like' ),
								new RepeatableContentModel( new AlternateContentModel(
									new ClassRefContentModel( 'model.global' ),
									new ClassRefContentModel( 'model.divGenLike' )
								), 0, null )
							), 1, null )
						), 0, 1 )
					)
				),
				new RepeatableContentModel( new SequenceContentModel(
					new ClassRefContentModel( 'model.divBottom' ),
					$anyTimeModelGlobal
				), 0, null )
			)
		);

		$registry->registerElement(
			'cell',
			[ 'att.global', 'att.tableDecoration' ],
			$macroSpecialPara,
			[
				new AttributeDef( 'role', new EnumerationDatatype( 'data', 'label' ) ),
				new AttributeDef( 'cols', new CountDatatype() ),
				new AttributeDef( 'rows', new CountDatatype() )
			]
		);

		$registry->registerElement(
			'del',
			[
				'att.global',
				'model.pPart.transcriptional',
				'att.transcriptional',
				'att.typed',
				'att.dimensions'
			],
			$macroParaContent
		);

		$registry->registerElement(
			'div',
			[ 'att.global', 'att.divLike', 'att.typed', 'att.declaring', 'att.written', 'model.divLike' ],
			new SequenceContentModel(
				new RepeatableContentModel( new AlternateContentModel(
					new ClassRefContentModel( 'model.divTop' ),
					new ClassRefContentModel( 'model.global' )
				), 0, null ),
				new RepeatableContentModel( new SequenceContentModel(
					new AlternateContentModel(
						new RepeatableContentModel( new SequenceContentModel(
							new AlternateContentModel(
								new ClassRefContentModel( 'model.divLike' ),
								new ClassRefContentModel( 'model.divGenLike' )
							),
							$anyTimeModelGlobal
						), 1, null ),
						new SequenceContentModel(
							new RepeatableContentModel( new SequenceContentModel(
								new ClassRefContentModel( 'model.common' ),
								$anyTimeModelGlobal
							), 1, null ),
							new RepeatableContentModel( new SequenceContentModel(
								new AlternateContentModel(
									new ClassRefContentModel( 'model.divLike' ),
									new ClassRefContentModel( 'model.divGenLike' )
								),
								$anyTimeModelGlobal
							), 0, null )
						)
					),
					new RepeatableContentModel( new SequenceContentModel(
						new ClassRefContentModel( 'model.divBottom' ),
						$anyTimeModelGlobal
					), 0, null )
				), 0, 1 )
			)
		);

		$registry->registerElement(
			'front',
			[ 'att.global', 'att.declaring' ],
			new SequenceContentModel(
				new RepeatableContentModel( new AlternateContentModel(
					new ClassRefContentModel( 'model.frontPart' ),
					new ClassRefContentModel( 'model.pLike' ),
					new ClassRefContentModel( 'model.pLike.front' ),
					new ClassRefContentModel( 'model.global' )
				), 0, null ),
				new RepeatableContentModel( new SequenceContentModel(
					new AlternateContentModel(
						new SequenceContentModel(
							new ClassRefContentModel( 'model.div1Like' ),
							new RepeatableContentModel( new AlternateContentModel(
								new ClassRefContentModel( 'model.div1Like' ),
								new ClassRefContentModel( 'model.frontPart' ),
								new ClassRefContentModel( 'model.global' )
							), 0, null )
						),
						new SequenceContentModel(
							new ClassRefContentModel( 'model.divLike' ),
							new RepeatableContentModel( new AlternateContentModel(
								new ClassRefContentModel( 'model.divLike' ),
								new ClassRefContentModel( 'model.frontPart' ),
								new ClassRefContentModel( 'model.global' )
							), 0, null )
						)
					),
					new RepeatableContentModel( new AlternateContentModel(
						new ClassRefContentModel( 'model.divBottom' ),
						new RepeatableContentModel( new AlternateContentModel(
							new ClassRefContentModel( 'model.divBottom' ),
							new ClassRefContentModel( 'model.global' )
						), 0, null )
					), 0, 1 )
				), 0, 1 )
			)
		);

		$registry->registerElement(
			'hi',
			[ 'att.global', 'model.hiLike', 'att.written' ],
			$macroParaContent,
			[
				new AttributeDef( 'rend', new EnumerationDatatype(
					'bold', 'italic', 'small', 'sub', 'sup', 'var'
				) )
			]
		);

		$registry->registerElement(
			'item',
			[ 'att.global', 'att.sortable' ],
			$macroSpecialPara
		);

		$registry->registerElement(
			'lb',
			[
				'att.global',
				'model.milestoneLike',
				'att.typed',
				'att.edition',
				'att.spanning',
				'att.breaking'
			],
			new EmptyContentModel()
		);

		$registry->registerElement(
			'list',
			[ 'att.global', 'att.sortable', 'att.typed', 'model.listLike' ],
			// TODO: limited: HTML <ul> only supports the child <li>
			new RepeatableContentModel( new ElementRefContentModel( 'item' ), 1, null ),
			[
				new AttributeDef( 'type', new EnumerationDatatype( 'ordered', 'unordered' ) )
			]
		);

		$registry->registerElement(
			'p',
			[ 'att.global', 'model.pLike', 'att.declaring', 'att.fragmentable', 'att.written' ],
			$macroParaContent
		);

		$registry->registerElement(
			'row',
			[ 'att.global', 'att.tableDecoration' ],
			new RepeatableContentModel( new ElementRefContentModel( 'cell' ), 1, null )
		);

		$registry->registerElement(
			'table',
			[ 'att.global', 'model.listLike', 'att.typed' ],
			// TODO: limited: HTML <table> only supports one <caption> and then <tr>s
			new SequenceContentModel(
				// TODO <caption>
				new RepeatableContentModel( new ElementRefContentModel( 'row' ), 1, null )
			)
		);

		$registry->registerElement(
			'text',
			[ 'att.global','att.declaring', 'att.typed', 'att.written', 'model.resourceLike' ],
			new SequenceContentModel(
				$anyTimeModelGlobal,
				new RepeatableContentModel( new SequenceContentModel(
						new ElementRefContentModel( 'front' ),
						$anyTimeModelGlobal
					), 0, 1 ),
				new AlternateContentModel(
					new ElementRefContentModel( 'body' )
					// TODO: support? new ElementRefContentModel('group')
				),
				$anyTimeModelGlobal,
				new RepeatableContentModel( new SequenceContentModel(
					new ElementRefContentModel( 'back' ),
					$anyTimeModelGlobal
				), 0, 1 )
			)
		);

		return $registry;
	}
}
