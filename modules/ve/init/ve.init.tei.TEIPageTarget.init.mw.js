$( function () {
	var editMessage = mw.config.get( 'wgArticleId' ) > 0 ? 'edit' : 'create',
		$editSource = $( '#ca-edit' ),
		$editLink = $( '<a>' ).text( mw.message( editMessage ).plain() ),
		$edit = $( '<li>' ).append( $( '<span>' ).append( $editLink ) );

	// Setup edit tabs
	$editSource.find( 'a' ).text( mw.message( 'visualeditor-ca-' + editMessage + 'source' ).plain() );

	$edit.insertBefore( $editSource );
	$editLink.on( 'click', function ( event ) {
		event.preventDefault();
		mw.openVeTeiEditDialog( 'visual' );
	} );
} );
