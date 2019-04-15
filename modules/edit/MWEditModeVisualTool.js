/**
 * MediaWiki UserInterface edit mode visual tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeVisualTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
mw.teiEditor.MWEditModeVisualTool = function () {
	mw.teiEditor.MWEditModeVisualTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.MWEditModeVisualTool, mw.libs.ve.MWEditModeVisualTool );

mw.teiEditor.MWEditModeVisualTool.static.name = 'teiEditModeVisual';

/**
 * @inheritdoc
 */
mw.teiEditor.MWEditModeVisualTool.prototype.switch = function () {
	var content = $( '#wpTextbox1' ).textSelection( 'getContents' );
	mw.openVeTeiEditDialog( 'visual', content );
};

mw.teiEditor.toolFactory.register( mw.teiEditor.MWEditModeVisualTool );
