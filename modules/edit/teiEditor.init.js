$( () => {
	// eslint-disable-next-line no-jquery/no-global-selector
	var $textarea = $( '#wpTextbox1' ),
		$container = $textarea.parent();
	$( document.body ).append( ( new mw.teiEditor.Editor( {
		$element: $container,
		$textarea: $textarea
	} ) ).$element );
} );
