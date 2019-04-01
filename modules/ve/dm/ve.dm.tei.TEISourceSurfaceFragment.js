/*!
 * Derived from VisualEditor DataModel MWWikitextSurfaceFragment class.
 *
 * @copyright 2011-2019 VisualEditor Team and others; see http://ve.mit-license.org
 */

/**
 * DataModel TEISourceSurfaceFragment.
 *
 * @class
 * @extends ve.dm.SourceSurfaceFragment
 *
 * @constructor
 * @param {ve.dm.Document} doc
 */
ve.dm.tei.TEISourceSurfaceFragment = function VeDmTEISourceSurfaceFragment() {
	// Parent constructors
	ve.dm.tei.TEISourceSurfaceFragment.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.dm.tei.TEISourceSurfaceFragment, ve.dm.SourceSurfaceFragment );

/* Methods */

/**
 * @inheritdoc
 */
ve.dm.tei.TEISourceSurfaceFragment.prototype.convertToSource = function ( doc ) {
	if ( !doc.data.hasContent() ) {
		return ve.createDeferred().resolve( '' ).promise();
	} else {
		return ve.init.tei.teiContentConverter.getTeiFromHtml( ve.properInnerHtml(
			ve.dm.converter.getDomFromModel( doc ).body
		), false );
	}
};

/**
 * @inheritdoc
 */
ve.dm.tei.TEISourceSurfaceFragment.prototype.convertFromSource = function ( source ) {
	var self = this;
	if ( !source ) {
		return $.Deferred().resolve(
			ve.dm.Document.static.newBlankDocument()
		).promise();
	} else {
		return ve.init.tei.teiContentConverter.getHtmlFromTei( source, false ).then( function ( source ) {
			return ve.dm.tei.TEISourceSurfaceFragment.super.prototype.convertFromSource.call( self, source );
		} );
	}
};
