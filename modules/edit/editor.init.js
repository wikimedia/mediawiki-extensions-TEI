$( function () {
	var $textarea = $( '#wpTextbox1' ),
		$container = $textarea.parent();
	$( document.body ).append( ( new mw.teiEditor.Editor( {
		$element: $container,
		$textarea: $textarea
	} ) ).$element );
} );
