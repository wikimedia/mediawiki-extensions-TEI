/**
 * MediaWiki UserInterface edit mode visual tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeVisualTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
ve.ui.MWEditModeVisualTool = function VeUiMWEditModeVisualTool() {
	ve.ui.MWEditModeVisualTool.super.apply( this, arguments );
};
OO.inheritClass( ve.ui.MWEditModeVisualTool, mw.libs.ve.MWEditModeVisualTool );

/**
 * @inheritdoc
 */
ve.ui.MWEditModeVisualTool.prototype.getMode = function () {
	return this.toolbar.getSurface().getMode();
};

ve.ui.toolFactory.register( ve.ui.MWEditModeVisualTool );
