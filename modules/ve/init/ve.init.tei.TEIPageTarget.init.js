$( () => {
	var windowManager = null,
		editDialog = null,
		isLoading = false,
		$loading = null;

	/*!
	 * Code from VisualEditor MediaWiki DesktopArticleTarget init.
	 * @copyright 2011-2019 VisualEditor Team and others; see AUTHORS.txt
	 * @license The MIT License (MIT); see LICENSE.txt
	 */
	function showLoading() {
		var $content, windowHeight, clientTop, top, bottom, middle, progressBar;

		if ( isLoading ) {
			return;
		}

		isLoading = true;

		$( document.documentElement ).addClass( 've-tei-loading' );
		if ( !$loading ) {
			progressBar = new OO.ui.ProgressBarWidget();
			$loading = $( '<div>' )
				.addClass( 've-init-mw-teiPageTarget-loading-overlay' )
				.append( progressBar.$element );
		}

		// eslint-disable-next-line no-jquery/no-global-selector
		$content = $( '#content' );
		// Center within visible part of the target
		windowHeight = window.innerHeight;
		clientTop = $content[ 0 ].offsetTop - window.pageYOffset;
		top = Math.max( clientTop, 0 );
		bottom = Math.min( clientTop + $content[ 0 ].offsetHeight, windowHeight );
		middle = ( bottom - top ) / 2;
		$loading.css( 'top', middle + Math.max( -clientTop, 0 ) );

		$content.prepend( $loading );
	}

	function clearLoading() {
		isLoading = false;
		$( document.documentElement ).removeClass( 've-tei-loading' );
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

	mw.openVeTeiEditDialog = function ( mode, content ) {
		showLoading();
		mw.loader.using( 'ext.tei.ve.pageTarget', () => {
			if ( editDialog === null ) {
				createEditDialog();
			}

			windowManager.openWindow( editDialog, {
				mode: mode,
				content: content
			} );
			clearLoading();
		} );
	};
} );
