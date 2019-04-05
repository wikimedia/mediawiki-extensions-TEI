/*!
 * Derived from VisualEditor UserInterface MWWikitextSurface class.
 *
 * @copyright 2011-2019 VisualEditor Team and others; see http://ve.mit-license.org
 */

/**
 * @class
 * @extends ve.ui.Surface
 *
 * @constructor
 * @param {HTMLDocument|Array|ve.dm.LinearData|ve.dm.Document} dataOrDoc Document data to edit
 * @param {Object} [config] Configuration options
 */
ve.ui.tei.TEISourceSurface = function VeUiTEISourceSurface() {
	ve.ui.tei.TEISourceSurface.super.apply( this, arguments );

	this.$element.addClass( 've-ui-teiSourceSurface' );
	this.getView().$element.addClass( 'mw-editfont-' + mw.user.options.get( 'editfont' ) );
	this.$placeholder.addClass( 'mw-editfont-' + mw.user.options.get( 'editfont' ) );
};

/* Inheritance */

OO.inheritClass( ve.ui.tei.TEISourceSurface, ve.ui.Surface );

/* Methods */

/**
 * @inheritdoc
 */
ve.ui.tei.TEISourceSurface.prototype.createModel = function ( doc ) {
	return new ve.dm.tei.TEISourceSurface( doc );
};
