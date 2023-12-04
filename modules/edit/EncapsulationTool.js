/**
 * Tools to encapsulate the selection.
 *
 * @class
 * @abstract
 * @extends OO.ui.Tool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */
mw.teiEditor.EncapsulationTool = function () {
	mw.teiEditor.EncapsulationTool.super.apply( this, arguments );
};

OO.inheritClass( mw.teiEditor.EncapsulationTool, OO.ui.Tool );

/**
 * Encapsulation the tool should apply
 *
 * @abstract
 * @static
 * @property {Object}
 * @inheritable
 */
mw.teiEditor.EncapsulationTool.static.encapsulate = {
	pre: '',
	post: ''
};

/**
 * @inheritdoc
 */
mw.teiEditor.EncapsulationTool.prototype.onSelect = function () {
	// eslint-disable-next-line no-jquery/no-global-selector
	$( '#wpTextbox1' ).textSelection( 'encapsulateSelection', this.constructor.static.encapsulate );
	this.setActive( false );
};

/**
 * @inheritdoc
 */
mw.teiEditor.EncapsulationTool.prototype.onUpdateState = function () {};

/**
 * UserInterface bold tool.
 *
 * @class
 * @extends mw.teiEditor.EncapsulationTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */
mw.teiEditor.BoldAnnotationTool = function () {
	mw.teiEditor.BoldAnnotationTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.BoldAnnotationTool, mw.teiEditor.EncapsulationTool );
mw.teiEditor.BoldAnnotationTool.static.name = 'bold';
mw.teiEditor.BoldAnnotationTool.static.group = 'textStyle';
mw.teiEditor.BoldAnnotationTool.static.icon = 'bold';
mw.teiEditor.BoldAnnotationTool.static.title = OO.ui.deferMsg( 'visualeditor-annotationbutton-bold-tooltip' );
mw.teiEditor.BoldAnnotationTool.static.encapsulate = { pre: '<hi rend="bold">', post: '</hi>' };
mw.teiEditor.toolFactory.register( mw.teiEditor.BoldAnnotationTool );

/**
 * UserInterface italic tool.
 *
 * @class
 * @extends mw.teiEditor.EncapsulationTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */
mw.teiEditor.ItalicAnnotationTool = function () {
	mw.teiEditor.ItalicAnnotationTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.ItalicAnnotationTool, mw.teiEditor.EncapsulationTool );
mw.teiEditor.ItalicAnnotationTool.static.name = 'italic';
mw.teiEditor.ItalicAnnotationTool.static.group = 'textStyle';
mw.teiEditor.ItalicAnnotationTool.static.icon = 'italic';
mw.teiEditor.ItalicAnnotationTool.static.title = OO.ui.deferMsg( 'visualeditor-annotationbutton-italic-tooltip' );
mw.teiEditor.ItalicAnnotationTool.static.encapsulate = { pre: '<hi rend="italic">', post: '</hi>' };
mw.teiEditor.toolFactory.register( mw.teiEditor.ItalicAnnotationTool );

/**
 * UserInterface superscript tool.
 *
 * @class
 * @extends mw.teiEditor.EncapsulationTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */
mw.teiEditor.SuperscriptAnnotationTool = function () {
	mw.teiEditor.SuperscriptAnnotationTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.SuperscriptAnnotationTool, mw.teiEditor.EncapsulationTool );
mw.teiEditor.SuperscriptAnnotationTool.static.name = 'superscript';
mw.teiEditor.SuperscriptAnnotationTool.static.group = 'textStyle';
mw.teiEditor.SuperscriptAnnotationTool.static.icon = 'superscript';
mw.teiEditor.SuperscriptAnnotationTool.static.title = OO.ui.deferMsg( 'visualeditor-annotationbutton-superscript-tooltip' );
mw.teiEditor.SuperscriptAnnotationTool.static.encapsulate = { pre: '<hi rend="sup">', post: '</hi>' };
mw.teiEditor.toolFactory.register( mw.teiEditor.SuperscriptAnnotationTool );

/**
 * UserInterface subscript tool.
 *
 * @class
 * @extends mw.teiEditor.EncapsulationTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */
mw.teiEditor.SubscriptAnnotationTool = function () {
	mw.teiEditor.SubscriptAnnotationTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.SubscriptAnnotationTool, mw.teiEditor.EncapsulationTool );
mw.teiEditor.SubscriptAnnotationTool.static.name = 'subscript';
mw.teiEditor.SubscriptAnnotationTool.static.group = 'textStyle';
mw.teiEditor.SubscriptAnnotationTool.static.icon = 'subscript';
mw.teiEditor.SubscriptAnnotationTool.static.title = OO.ui.deferMsg( 'visualeditor-annotationbutton-subscript-tooltip' );
mw.teiEditor.SubscriptAnnotationTool.static.encapsulate = { pre: '<hi rend="sub">', post: '</hi>' };
mw.teiEditor.toolFactory.register( mw.teiEditor.SubscriptAnnotationTool );

/**
 * UserInterface small tool.
 *
 * @class
 * @extends mw.teiEditor.EncapsulationTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */
mw.teiEditor.SmallAnnotationTool = function () {
	mw.teiEditor.SmallAnnotationTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.SmallAnnotationTool, mw.teiEditor.EncapsulationTool );
mw.teiEditor.SmallAnnotationTool.static.name = 'small';
mw.teiEditor.SmallAnnotationTool.static.group = 'textStyle';
mw.teiEditor.SmallAnnotationTool.static.icon = 'smaller';
mw.teiEditor.SmallAnnotationTool.static.title = OO.ui.deferMsg( 'visualeditor-annotationbutton-small-tooltip' );
mw.teiEditor.SubscriptAnnotationTool.static.encapsulate = { pre: '<hi rend="small">', post: '</hi>' };
mw.teiEditor.toolFactory.register( mw.teiEditor.SmallAnnotationTool );

/**
 * UserInterface language tool.
 *
 * @class
 * @extends mw.teiEditor.EncapsulationTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Configuration options
 */
mw.teiEditor.LanguageAnnotationTool = function () {
	mw.teiEditor.LanguageAnnotationTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.LanguageAnnotationTool, mw.teiEditor.EncapsulationTool );
mw.teiEditor.LanguageAnnotationTool.static.name = 'language';
mw.teiEditor.LanguageAnnotationTool.static.group = 'meta';
mw.teiEditor.LanguageAnnotationTool.static.icon = 'language';
mw.teiEditor.LanguageAnnotationTool.static.title = OO.ui.deferMsg( 'visualeditor-annotationbutton-language-tooltip' );
mw.teiEditor.LanguageAnnotationTool.static.encapsulate = { pre: '<foreign xml:lang="">', post: '</foreign>' };
mw.teiEditor.toolFactory.register( mw.teiEditor.LanguageAnnotationTool );
