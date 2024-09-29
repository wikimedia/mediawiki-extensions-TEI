/**
 * The TEI XML editor
 *
 * @class
 *
 * @constructor
 * @param {Object} [config] Configuration options
 * @cfg {jQuery} [$textarea] The <textarea> to edit
 */
mw.teiEditor.Editor = function ( config ) {
	var textarea = config.$textarea[ 0 ],
		dir = textarea.dir,
		readOnly = textarea.readOnly,
		codeMirror,
		toolGroupFactory = new OO.ui.ToolGroupFactory(),
		toolbar = new OO.ui.Toolbar( mw.teiEditor.toolFactory, toolGroupFactory, {
			actions: true
		} ),
		actions = new OO.ui.Toolbar( mw.teiEditor.toolFactory, toolGroupFactory );

	mw.teiEditor.Editor.super.bind( this )( {
		disabled: readOnly
	} );

	this.api = new mw.Api();

	// Toolbar
	config.$textarea.before( toolbar.$element );
	toolbar.setup( mw.teiEditor.Editor.static.toolbarGroups );
	toolbar.initialize();
	toolbar.emit( 'updateState' );
	toolbar.$actions.append( actions.$element );
	actions.setup( mw.teiEditor.Editor.static.actionGroups );
	actions.initialize();
	actions.emit( 'updateState' );

	// Code mirror autocomplete
	function completeAfter( cm, pred ) {
		if ( !pred || pred() ) {
			setTimeout( () => {
				if ( !cm.state.completionActive ) {
					cm.showHint( { completeSingle: false } );
				}
			}, 100 );
		}
		return CodeMirror.Pass;
	}

	function completeIfAfterLt( cm ) {
		return completeAfter( cm, () => {
			var cur = cm.getCursor();
			return cm.getRange( CodeMirror.Pos( cur.line, cur.ch - 1 ), cur ) === '<';
		} );
	}

	function completeIfInTag( cm ) {
		return completeAfter( cm, () => {
			var tok = cm.getTokenAt( cm.getCursor() ),
				inner = CodeMirror.innerMode( cm.getMode(), tok.state ).state;
			if ( tok.type === 'string' && ( !/['"]/.test( tok.string.charAt( tok.string.length - 1 ) ) || tok.string.length === 1 ) ) {
				return false;
			}
			return inner.tagName;
		} );
	}

	// CodeMirror itself
	codeMirror = CodeMirror.fromTextArea( textarea, {
		mode: 'xml',
		lineNumbers: true,
		direction: dir,
		readOnly: readOnly,
		spellcheck: true,
		extraKeys: {
			"'<'": completeAfter,
			"'/'": completeIfAfterLt,
			"' '": completeIfInTag,
			"'='": completeIfInTag,
			'Ctrl-Space': 'autocomplete'
		},
		gutters: [ 'CodeMirror-lint-markers' ],
		hintOptions: {
			schemaInfo: mw.teiEditorSchema
		},
		lint: {
			getAnnotations: mw.teiEditor.Editor.prototype.lint.bind( this ),
			async: true
		}
	} );
	config.$textarea.textSelection( 'register', {
		getContents: function () {
			return codeMirror.doc.getValue();
		},
		setContents: function ( content ) {
			codeMirror.doc.setValue( content );
			return this;
		},
		getSelection: function () {
			return codeMirror.doc.getSelection();
		},
		setSelection: function ( options ) {
			codeMirror.focus();
			codeMirror.doc.setSelection( codeMirror.doc.posFromIndex( options.start ), codeMirror.doc.posFromIndex( options.end ) );
			return this;
		},
		replaceSelection: function ( value ) {
			codeMirror.doc.replaceSelection( value );
			return this;
		},
		getCaretPosition: function ( options ) {
			var caretPos = codeMirror.doc.indexFromPos( codeMirror.doc.getCursor( true ) ),
				endPos = codeMirror.doc.indexFromPos( codeMirror.doc.getCursor( false ) );
			if ( options.startAndEnd ) {
				return [ caretPos, endPos ];
			}
			return caretPos;
		},
		scrollToCaretPosition: function () {
			codeMirror.scrollIntoView( null );
			return this;
		}
	} );
};

OO.inheritClass( mw.teiEditor.Editor, OO.ui.Widget );

mw.teiEditor.Editor.static.toolbarGroups = [
	{
		name: 'style',
		type: 'list',
		icon: 'textStyle',
		title: OO.ui.deferMsg( 'visualeditor-toolbar-style-tooltip' ),
		include: [ { group: 'textStyle' } ],
		forceExpand: [ 'bold', 'italic' ],
		promote: [ 'bold', 'italic' ],
		demote: [ 'strikethrough', 'code', 'underline', 'language', 'big', 'small', 'clear' ]
	}
];

mw.teiEditor.Editor.static.actionGroups = [
	{
		name: 'editMode',
		type: 'list',
		icon: 'edit',
		title: OO.ui.deferMsg( 'visualeditor-mweditmode-tooltip' ),
		include: [ 'teiEditModeVisual', 'teiEditModeSource' ]
	}
];

mw.teiEditor.Editor.prototype.lint = function ( text, callback ) {
	return this.api.post( {
		action: 'teivalidate',
		text: text
	} ).done( ( data ) => {
		callback( data.validation.map( ( message ) => ( {
			from: CodeMirror.Pos( ( message.line || 1 ) - 1, 0 ),
			to: CodeMirror.Pos( ( message.line || 1 ) - 1, 0 ),
			message: message.message,
			severity: message.type
		} ) ) );
	} );
};
mw.teiEditor.Editor.prototype.lint.async = true;
