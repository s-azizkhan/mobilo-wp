( function( $ ) {

	$( '.wc-product-table' ).on( 'draw.wcpt', function() {
		// Add Astra +/- quantity buttons on draw events.
		if ( typeof astrawpWooQuantityButtons === 'function' ) {
			astrawpWooQuantityButtons();
		}
	} );

} )( jQuery );

