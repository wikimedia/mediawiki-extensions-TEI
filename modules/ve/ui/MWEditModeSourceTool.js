/**
 * MediaWiki UserInterface edit mode source tool.
 *
 * @class
 * @extends mw.libs.ve.MWEditModeSourceTool
 * @constructor
 * @param {OO.ui.ToolGroup} toolGroup
 * @param {Object} [config] Config options
 */
ve.ui.MWEditModeSourceTool = function VeUiMWEditModeSourceTool() {
	ve.ui.MWEditModeSourceTool.super.apply( this, arguments );
};
OO.inheritClass( ve.ui.MWEditModeSourceTool, mw.libs.ve.MWEditModeSourceTool );

/**
 * @inheritdoc
 */
ve.ui.MWEditModeSourceTool.prototype.getMode = function () {
	return this.toolbar.getSurface().getMode();
};

/**
 * @inheritdoc
 */
ve.ui.MWEditModeSourceTool.prototype.switch = function () {
	this.toolbar.getTarget().editSource();
};

ve.ui.toolFactory.register( ve.ui.MWEditModeSourceTool );
