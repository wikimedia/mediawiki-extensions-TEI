/**
 * Initialization TEI editor target.
 *
 * @class
 * @extends ve.init.Target
 *
 * @constructor
 * @param {Object} [config] Configuration options
 * @cfg {Object} [toolbarConfig] Configuration options for the toolbar
 * @cfg {mw.Title} [pageTitle] the title of the page to edit
 * @cfg {integer} [revId] the ID of the revision to edit
 * @cfg {string} [lang] Page language
 * @cfg {string} [dir] Page language direction
 * @cfg {boolean} [readOnly] If page is not editable
 */
ve.init.tei.TEIPageTarget = function VeInitSaTarget( config ) {
	config = config || {};
	config.toolbarConfig = ve.extendObject( { actions: true }, config.toolbarConfig );

	this.pageTitle = config.pageTitle;
	this.revId = config.revId;
	this.lang = config.lang;
	this.dir = config.dir;
	this.readOnly = config.readOnly;
	this.api = new mw.Api();

	// Parent constructor
	ve.init.tei.TEIPageTarget.super.call( this, config );

	this.$element
		.addClass( 've-init-teiDesktopPageTarget' )
		.attr( 'lang', ve.init.platform.getUserLanguages()[ 0 ] );
};

/* Inheritance */

OO.inheritClass( ve.init.tei.TEIPageTarget, ve.init.Target );

/* Static properties */

ve.init.tei.TEIPageTarget.static.modes = [ 'visual' ];

ve.init.tei.TEIPageTarget.static.toolbarGroups = [
	{
		name: 'history',
		include: [ 'undo', 'redo' ]
	},
	{
		name: 'format',
		type: 'menu',
		title: OO.ui.deferMsg( 'visualeditor-toolbar-format-tooltip' ),
		include: [ { group: 'format' } ],
		promote: [ 'paragraph' ],
		exclude: [ 'preformatted' ]
	},
	{
		name: 'style',
		type: 'list',
		icon: 'textStyle',
		title: OO.ui.deferMsg( 'visualeditor-toolbar-style-tooltip' ),
		include: [ { group: 'textStyle' }, 'language', 'clear' ],
		forceExpand: [ 'bold', 'italic', 'clear' ],
		promote: [ 'bold', 'italic' ]
	},
	{
		name: 'link',
		include: [ 'link' ]
	},
	{
		name: 'structure',
		type: 'list',
		icon: 'listBullet',
		title: OO.ui.deferMsg( 'visualeditor-toolbar-structure' ),
		include: [ { group: 'structure' } ],
		demote: [ 'outdent', 'indent' ]
	},
	{
		name: 'insert',
		label: OO.ui.deferMsg( 'visualeditor-toolbar-insert' ),
		title: OO.ui.deferMsg( 'visualeditor-toolbar-insert' ),
		include: [ 'insertTable' ],
		forceExpand: [ 'insertTable' ],
		promote: [ 'insertTable' ]
	},
	{
		name: 'specialCharacter',
		include: [ 'specialCharacter' ]
	}
];

ve.init.mw.Target.static.importRules = {
	external: {
		blacklist: [
			// Annotations
			'link/mwExternal', 'textStyle/span', 'textStyle/font', 'textStyle/underline', 'meta/language', 'textStyle/datetime',
			// Nodes
			'article', 'section', 'div', 'alienInline', 'alienBlock', 'comment'
		],
		htmlBlacklist: {
			// Remove reference numbers copied from MW read mode (T150418)
			remove: [ 'sup.reference:not( [typeof] )' ],
			unwrap: [ 'fieldset', 'legend' ]
		},
		removeOriginalDomElements: true,
		nodeSanitization: true
	},
	all: null
};

ve.init.tei.TEIPageTarget.static.actionGroups = [
	{
		name: 'pageMenu',
		type: 'list',
		icon: 'menu',
		indicator: null,
		title: ve.msg( 'visualeditor-pagemenu-tooltip' ),
		include: [ 'codeMirror', 'findAndReplace', 'commandHelp' ]
	},
	{
		name: 'editMode',
		type: 'list',
		icon: 'edit',
		title: ve.msg( 'visualeditor-mweditmode-tooltip' ),
		include: [ 'editModeVisual', 'editModeSource' ]
	}
];

/**
 * @event error
 */

/* Methods */

/**
 * @inheritdoc
 */
ve.init.tei.TEIPageTarget.prototype.createSurface = function () {
	return ve.init.tei.TEIPageTarget.super.prototype.createSurface.apply( this, arguments );
};

/**
 * Adds a new surface for the page
 *
 * @param {string} [mode] Edit mode
 * @param {string|undefined} [content] Content to edit
 * @return {Promise<ve.ui.Surface>}
 */
ve.init.tei.TEIPageTarget.prototype.generateSurface = function ( mode, content ) {
	var self = this;

	return ve.init.tei.teiContentConverter.getHtmlFromTei( content, true, this.pageTitle ).then( function ( content ) {
		return self.generateSurfaceForContent( content, mode );
	}, function ( error ) {
		self.emit( 'error', error );
	} );
};

/**
 * Adds a new surface for the content
 *
 * @param {string} [pageContent]
 * @param {string} [mode] Edit mode
 * @return {ve.ui.Surface}
 */
ve.init.tei.TEIPageTarget.prototype.generateSurfaceForContent = function ( pageContent, mode ) {
	var surface = this.addSurface(
		this.constructor.static.createModelFromDom(
			this.constructor.static.parseDocument( pageContent, mode ),
			mode,
			{ lang: this.lang, dir: this.dir }
		),
		{ placeholder: 'Start your document', mode: mode }
	);
	surface.setReadOnly( this.readOnly );

	if ( this.getSurface() ) {
		this.getSurface().destroy();
	}
	this.setSurface( surface );
	return surface;
};

/**
 * @inheritdoc
 */
ve.init.tei.TEIPageTarget.prototype.addSurface = function () {
	var surface = ve.init.tei.TEIPageTarget.super.prototype.addSurface.apply( this, arguments );
	this.$element.append( $( '<div>' ).addClass( 've-init-tei-target-surfaceWrapper' ).append( surface.$element ) );
	surface.initialize();
	return surface;
};

/**
 * Saves the surface of the page
 * @return {Promise}
 */
ve.init.tei.TEIPageTarget.prototype.saveSurface = function () {
	var self = this, surface = this.getSurface();

	switch ( surface.getMode() ) {
		case 'visual':
			return ve.init.tei.teiContentConverter.getTeiFromHtml(
				this.getSurface().getHtml(), true, this.pageTitle
			).then( function ( pageContent ) {
				return self.saveTeiContent( pageContent );
			} );
		case 'source':
			return self.saveTeiContent( this.getSurface().getHtml() );
	}
};

/**
 * @param {string} [pageContent] content to save
 * @return {Promise}
 */
ve.init.tei.TEIPageTarget.prototype.saveTeiContent = function ( pageContent ) {
	this.submit( pageContent );
	return ve.createDeferred().resolve().promise();
};

ve.init.tei.TEIPageTarget.prototype.editSource = function () {
	var self = this;

	ve.init.tei.teiContentConverter.getTeiFromHtml(
		this.getSurface().getHtml(), true, this.pageTitle
	).then( function ( pageContent ) {
		self.submit( pageContent, { wpDiff: true } );
	}, function ( error ) {
		self.emit( 'error', error );
	} );
};

ve.init.tei.TEIPageTarget.prototype.submit = function ( wikitext, params ) {
	var key, $form = $( '<form>' ).attr( { method: 'post', enctype: 'multipart/form-data' } ).addClass( 'oo-ui-element-hidden' );
	params = ve.extendObject( {
		format: 'application/tei+xml',
		model: 'tei',
		oldid: this.revId,
		wpTextbox1: wikitext,
		wpEditToken: mw.user.tokens.get( 'csrfToken' ),
		wpUnicodeCheck: 'ℳ𝒲♥𝓊𝓃𝒾𝒸ℴ𝒹ℯ',
		wpUltimateParam: true
	}, params );
	for ( key in params ) {
		$form.append( $( '<input>' ).attr( { type: 'hidden', name: key, value: params[ key ] } ) );
	}
	$form.attr( 'action', mw.util.getUrl( this.pageTitle.toString(), {
		action: 'submit'
	} ) ).appendTo( 'body' ).trigger( 'submit' );
	return true;
};

ve.init.tei.TEIPageTarget.prototype.getPageName = function () {
	return this.pageTitle.toString();
};

ve.init.tei.TEIPageTarget.prototype.getContentApi = function ( doc, options ) {
	options = options || {};
	options.parameters = ve.extendObject( { formatversion: 2 }, options.parameters );
	return new mw.Api( options );
};
