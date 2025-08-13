/* phpcs:ignoreFile */
jQuery(function(){

	const { __ } = wp.i18n;

	if (jQuery('#afwc_action_row').length > 0) {
		jQuery('#afwc_action_row').next('#afwc_is_affiliate_row').hide();
	}

	if (jQuery('#afwc_actions').length > 0) {
		jQuery(document).on( 'click', '#afwc_actions', function(e) {
			e.preventDefault();
			jQuery('#afwc_is_affiliate_row input[name="afwc_is_affiliate"]')
				.val(jQuery(this).attr('data-affiliate-status') || '')
				.prop('checked',true);
			jQuery('input:submit#submit').click();
		});
	}

	let afwcSec = jQuery('.afwc-settings-wrap');
	jQuery(document).on( 'change', 'input[name="afwc_is_affiliate"]', function() {
		if( ! jQuery( this ).is( ':checked' ) ){
			alert( __( 'Are you sure you want to remove this user as an affiliate? Doing this will remove this affiliate and its entire chain from part of the parent-chain relationship of any other affiliate in a multi-tier commission distribution. This change is irreversible.', 'affiliate-for-woocommerce') );
			afwcSec.find('table#afwc tr:not(#afwc_is_affiliate_row)').hide('slow');
			afwcSec.find('.afwc-update-desc').show('slow');
		} else {
			jQuery(this).closest('td').find('p.description').hide( 'slow' );
			afwcSec.find('table#afwc tr:not(#afwc_is_affiliate_row)').show('slow');
		}
	});
});
