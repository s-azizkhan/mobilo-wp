( function( $ ) {

	$( 'body' ).on( 'click.wcpt', '.et_pb_toggle_title', function() {
		if ( $( this ).closest( '.et_pb_toggle' ).hasClass( 'et_pb_toggle_close' ) ) {
			setTimeout( function() {
				$.fn.dataTable
					.tables( { visible: true, api: true } )
					.columns.adjust();
			}, 705 );
		}
	} );

} )( jQuery );