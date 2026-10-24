( function ( $ ) {
	'use strict';

	$( function () {
		$( '.gsc-provider-card input[type="radio"]' ).on( 'change', function () {
			$( '.gsc-provider-card' ).removeClass( 'is-selected' );
			$( this ).closest( '.gsc-provider-card' ).addClass( 'is-selected' );
			updateProviderContext();
		} );

		function updateProviderContext() {
			const selected = $( '.gsc-provider-card input[name="wpfc_settings[genialsouls_provider]"]:checked' ).val();
			$( '[data-provider-context="wordpress"]' ).prop( 'hidden', 'wordpress' !== selected );
		}

		updateProviderContext();

		const button = $( '#genialsouls-social-comments-save' );
		if ( ! button.length ) {
			return;
		}

		button.on( 'click', function () {
			const spinner = $( '#genialsouls-social-comments-spinner' );
			const result = $( '#genialsouls-social-comments-result' );

			spinner.addClass( 'is-active' );
			result.text( '' );

			$.post(
				ajaxurl,
				{
					action: genialSoulsSocialCommentsAdmin.action,
					nonce: button.data( 'nonce' ),
					post_id: button.data( 'post-id' ),
					enabled: $( '#genialsouls-social-comments-enabled' ).prop( 'checked' ) ? '1' : '0'
				}
			).done( function ( response ) {
				if ( response.success ) {
					result.text( '✓' );
					return;
				}

				const message = response.data && response.data.message ? response.data.message : genialSoulsSocialCommentsAdmin.errorMessage;
				result.text( message );
			} ).fail( function ( response ) {
				const message = response.responseJSON && response.responseJSON.data && response.responseJSON.data.message
					? response.responseJSON.data.message
					: genialSoulsSocialCommentsAdmin.errorMessage;
				result.text( message );
			} ).always( function () {
				spinner.removeClass( 'is-active' );
			} );
		} );
	} );
} )( jQuery );
