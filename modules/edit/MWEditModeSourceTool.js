/**
 * MediaWiki UserInterface edit mode source tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeSourceTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
mw.teiEditor.MWEditModeSourceTool = function () {
	mw.teiEditor.MWEditModeSourceTool.super.apply( this, arguments );
};
OO.inheritClass( mw.teiEditor.MWEditModeSourceTool, mw.libs.ve.MWEditModeSourceTool );

mw.teiEditor.MWEditModeSourceTool.static.name = 'teiEditModeSource';

mw.teiEditor.toolFactory.register( mw.teiEditor.MWEditModeSourceTool );
