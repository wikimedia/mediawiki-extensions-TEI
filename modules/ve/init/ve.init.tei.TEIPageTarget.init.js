$( function () {
	var editMessage = mw.config.get( 'wgArticleId' ) > 0 ? 'edit' : 'create',
		$editSource = $( '#ca-edit' ),
		$editSourceLink = $( '#ca-edit a' ).text( mw.message( 'visualeditor-ca-' + editMessage + 'source' ).plain() ),
		$editLink = $( '<a>' ).text( mw.message( editMessage ).plain() ),
		$edit = $( '<li>' ).append( $( '<span>' ).append( $editLink ) ),
		windowManager = null,
		editDialog = null,
		isLoading = false,
		$loading = null;

	/*!
	 * Code from VisualEditor MediaWiki DesktopArticleTarget init.
	 * @copyright 2011-2019 VisualEditor Team and others; see AUTHORS.txt
	 * @license The MIT License (MIT); see LICENSE.txt
	 */
	function showLoading( mode ) {
		var $content, windowHeight, clientTop, top, bottom, middle, progressBar;

		if ( isLoading ) {
			return;
		}

		isLoading = true;

		$( 'html' ).addClass( 've-tei-loading' );
		if ( !$loading ) {
			progressBar = new OO.ui.ProgressBarWidget();
			$loading = $( '<div>' )
				.addClass( 've-init-mw-teiPageTarget-loading-overlay' )
				.append( progressBar.$element );
		}

		$content = $( '#content' );
		if ( mode !== 'source' ) {
			// Center within visible part of the target
			windowHeight = window.innerHeight;
			clientTop = $content[ 0 ].offsetTop - window.pageYOffset;
			top = Math.max( clientTop, 0 );
			bottom = Math.min( clientTop + $content[ 0 ].offsetHeight, windowHeight );
			middle = ( bottom - top ) / 2;
			$loading.css( 'top', middle + Math.max( -clientTop, 0 ) );
		} else {
			$loading.css( 'top', '' );
		}

		$content.prepend( $loading );
	}

	function clearLoading() {
		isLoading = false;
		$( 'html' ).removeClass( 've-tei-loading' );
		if ( $loading ) {
			$loading.detach();
		}
	}

	function createEditDialog() {
		var target = new ve.init.tei.TEIPageTarget( {
			pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			revId: mw.config.get( 'wgRevisionId' ),
			lang: mw.config.get( 'wgPageContentLanguage' ),
			dir: 'ltr', // TODO: configure
			readOnly: !mw.config.get( 'wgIsProbablyEditable' )
		} );
		windowManager = new OO.ui.WindowManager();
		editDialog = new ve.init.tei.TeiPageEditDialog( target, {
			size: 'full'
		} );
		$( document.body ).append( windowManager.$element );
		windowManager.addWindows( [ editDialog ] );
	}

	function openEditDialog( mode ) {
		showLoading();
		mw.loader.using( 'ext.tei.ve.pageTarget', function () {
			if ( editDialog === null ) {
				createEditDialog();
			}

			windowManager.openWindow( editDialog, {
				mode: mode
			} );
			clearLoading();
		} );
	}

	// Setup edit tabs
	$editSourceLink.on( 'click', function ( event ) {
		event.preventDefault();
		openEditDialog( 'source' );
	} );

	$edit.insertBefore( $editSource );
	$editLink.on( 'click', function ( event ) {
		event.preventDefault();
		openEditDialog( 'visual' );
	} );
} );
