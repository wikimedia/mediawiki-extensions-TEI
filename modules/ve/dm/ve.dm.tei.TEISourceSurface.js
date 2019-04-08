/*!
 * Derived from VisualEditor DataModel MWWikitextSurface class.
 *
 * @copyright 2011-2019 VisualEditor Team and others; see http://ve.mit-license.org
 */

/**
 * TEI surface.
 *
 * @class
 * @extends ve.dm.Surface
 *
 * @constructor
 * @param {ve.dm.Document} doc
 * @param {Object} [config]
 */
ve.dm.tei.TEISourceSurface = function VeDmTEISourceSurface( doc, config ) {
	// Parent constructors
	ve.dm.tei.TEISourceSurface.super.call( this, doc, ve.extendObject( config, { sourceMode: true } ) );
};

/* Inheritance */

OO.inheritClass( ve.dm.tei.TEISourceSurface, ve.dm.Surface );

/**
 * @inheritdoc
 */
ve.dm.tei.TEISourceSurface.prototype.getFragment = function ( selection, noAutoSelect, excludeInsertions ) {
	return new ve.dm.tei.TEISourceSurfaceFragment( this, selection || this.selection, noAutoSelect, excludeInsertions );
};
