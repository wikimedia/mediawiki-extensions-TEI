/**
 * TEI editing dialog.
 *
 * @class
 * @extends  OO.ui.ProcessDialog
 *
 * @constructor
 * @param {ve.init.tei.TEIPageTarget} [target] Target
 * @param {Object} [config] Configuration options
 */
ve.init.tei.TeiPageEditDialog = function ( target, config ) {
	var self = this;
	this.target = target;

	ve.init.tei.TeiPageEditDialog.super.call( this, config );

	target.on( 'error', function ( error ) {
		self.showErrors( [ error ] );
	} );
};

OO.inheritClass( ve.init.tei.TeiPageEditDialog, OO.ui.ProcessDialog );

ve.init.tei.TeiPageEditDialog.static.name = 've-tei-editor';
ve.init.tei.TeiPageEditDialog.static.title = function () {
	var message = mw.config.get( 'wgArticleId' ) === 0 ? 'creating' : 'editing';
	return ve.msg( message, mw.config.get( 'wgPageName' ) );
};
ve.init.tei.TeiPageEditDialog.static.actions = [
	{
		action: 'save',
		label: 'Save',
		flags: 'primary'
	},
	{
		label: 'Cancel',
		flags: 'safe'
	}
];

/**
 * @inheritdoc
 */
ve.init.tei.TeiPageEditDialog.prototype.initialize = function () {
	ve.init.tei.TeiPageEditDialog.super.prototype.initialize.call( this );

	this.$body.append( this.target.$element );
};

/**
 * @inheritdoc
 *
 * @param {Object} [data] Configuration options
 * @cfg {string} [mode] Edit mode
 */
ve.init.tei.TeiPageEditDialog.prototype.getSetupProcess = function ( data ) {
	var self = this;

	return ve.init.tei.TeiPageEditDialog.super.prototype.getSetupProcess.call( this, data ).next( function () {
		return self.target.generateSurface( data.mode, data.content );
	} );
};

// Use the getActionProcess() method to specify a process to handle the
// actions (for the 'save' action, in this example).
ve.init.tei.TeiPageEditDialog.prototype.getActionProcess = function ( action ) {
	if ( action === 'save' ) {
		return new OO.ui.Process( this.target.saveSurface().then( function () {
			document.location.reload();
			// TODO: avoid a page reload
		} ) );
	}
	return ve.init.tei.TeiPageEditDialog.super.prototype.getActionProcess.call( this, action );
};
