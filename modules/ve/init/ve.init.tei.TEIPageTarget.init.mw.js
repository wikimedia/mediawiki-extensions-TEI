$( () => {
	var editMessage = mw.config.get( 'wgArticleId' ) > 0 ? 'edit' : 'create',
		// eslint-disable-next-line no-jquery/no-global-selector
		$editSource = $( '#ca-edit' ),
		// eslint-disable-next-line mediawiki/msg-doc
		$editLink = $( '<a>' ).text( mw.message( editMessage ).plain() ),
		$edit = $( '<li>' ).append( $( '<span>' ).append( $editLink ) );

	// Setup edit tabs
	// eslint-disable-next-line mediawiki/msg-doc
	$editSource.find( 'a' ).text( mw.message( 'visualeditor-ca-' + editMessage + 'source' ).plain() );

	$edit.insertBefore( $editSource );
	$editLink.on( 'click', ( event ) => {
		event.preventDefault();
		mw.openVeTeiEditDialog( 'visual' );
	} );
} );
