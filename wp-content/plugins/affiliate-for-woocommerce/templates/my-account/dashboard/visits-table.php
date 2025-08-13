<?php
/**
 * My Account > Affiliate > Visits
 *
 * @see      This template can be overridden by: https://woocommerce.com/document/affiliate-for-woocommerce/how-to-override-templates/
 * @package  affiliate-for-woocommerce/templates/my-account/dashboard/
 * @since    8.37.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Note: We do not recommend removing existing id & classes in HTML.

$visits_colspan = ( true === $is_show_user_agent_column ) ? 5 : 4;
$allowed_html   = afwc_get_allowed_html_with_svg();
?>
<table class="afwc_visits afwc-reports afwc-visits-table">
	<thead>
		<?php
		if ( ! empty( $visits_headers ) && is_array( $visits_headers ) ) {
			echo '<tr>';
			foreach ( $visits_headers as $column_name => $column_label ) {
				?>
				<th class="<?php echo esc_attr( $column_name ); ?>"><?php echo esc_html( $column_label ); ?></th>
				<?php
			}
			echo '</tr>';
		}
		?>
	</thead>
	<tbody>
		<?php
		if ( is_array( $visits_data ) && ! empty( $visits_data['rows'] ) && is_array( $visits_data['rows'] ) && ! empty( $visits_headers ) && is_array( $visits_headers ) ) {
			foreach ( $visits_data['rows'] as $visit ) {
				echo '<tr>';
				foreach ( $visits_headers as $column_name => $column_label ) {
					?>
					<td class="<?php echo esc_attr( $column_name ); ?>" data-title="<?php echo esc_attr( $column_label ); ?>">
						<?php
						if ( 'referring_url' === $column_name ) {
							echo ! empty( $visit[ $column_name ] ) ? '<a href="' . esc_url( $visit[ $column_name ] ) . '">' . esc_html( $visit[ $column_name ] ) . '</a>' : '-';
						} elseif ( 'is_converted' === $column_name ) {
							$is_converted = ! empty( $visit[ $column_name ] ) ? 'yes' : 'no';
							echo wp_kses( AFWC_Visits::afwc_get_is_converted_svg( $is_converted ), $allowed_html );
						} elseif ( 'user_agent_info' === $column_name && is_array( $visit[ $column_name ] ) && ! empty( $visit[ $column_name ] ) ) {
							?>
							<div class="afwc-visits-device-wrapper">
								<span class="afwc-visits-info-label"><?php echo esc_html_x( 'Device:', 'Device type label in visits dashboard', 'affiliate-for-woocommerce' ); ?></span>
								<span class="afwc-visits-info-value afwc-visits-device">
									<?php
									$device_type = ! empty( $visit[ $column_name ]['device_type'] ) ? $visit[ $column_name ]['device_type'] : '';
									echo wp_kses( AFWC_Visits::afwc_get_device_type_svg( $device_type ), $allowed_html );
									?>
								</span>
							</div>
							<div class="afwc-visits-browser-wrapper">
								<span class="afwc-visits-info-label"><?php echo esc_html_x( 'Browser:', 'Browser type label in visits dashboard', 'affiliate-for-woocommerce' ); ?></span>
								<span class="afwc-visits-info-value afwc-visits-browser">
									<?php
									$browser = ! empty( $visit[ $column_name ]['browser'] ) ? $visit[ $column_name ]['browser'] : '';
									echo ( ! empty( $browser ) && 'Unknown' !== $browser ) ? esc_html( $browser ) : '-';
									?>
								</span>
							</div>
							<div class="afwc-visits-os-wrapper">
								<span class="afwc-visits-info-label"><?php echo esc_html_x( 'OS:', 'OS type label in visits dashboard', 'affiliate-for-woocommerce' ); ?></span>
								<span class="afwc-visits-info-value afwc-visits-os">
									<?php
									$os = ! empty( $visit[ $column_name ]['os'] ) ? $visit[ $column_name ]['os'] : '';
									echo ( ! empty( $os ) && 'Unknown' !== $os ) ? esc_html( $os ) : '-';
									?>
								</span>
							</div>
							<div class="afwc-visits-country-wrapper">  
								<span class="afwc-visits-info-label"><?php echo esc_html_x( 'Country:', 'Country type label in visits dashboard', 'affiliate-for-woocommerce' ); ?></span>
								<?php
								if ( is_array( $visit['country'] ) && ! empty( $visit['country']['code'] ) ) {
									$country_title = ! empty( $visit['country']['name'] ) ? $visit['country']['name'] : $visit['country']['code'];
									?>
									<span
										class="afwc-visits-info-value afwc-visits-country"
										title="<?php echo esc_attr( $country_title ); ?>"
										data-country_name="<?php echo esc_attr( $visit['country']['name'] ); ?>"
										data-country_code="<?php echo esc_attr( $visit['country']['code'] ); ?>"
									>
										<?php echo esc_attr( $visit['country']['code'] ); ?>
									</span>
									<?php
								} else {
									echo '<span class="afwc-visits-info-value">-</span>';
								}
								?>
							</div>
							<?php
						} else {
							echo ! empty( $visit[ $column_name ] ) ? wp_kses_post( $visit[ $column_name ] ) : '';
						}
						?>
					</td>
					<?php
				}
				echo '</tr>';
			}
		} else {
			?>
			<tr>
				<td class="empty-table" colspan="<?php echo esc_attr( $visits_data_colspan ); ?>">
					<?php echo esc_html_x( 'No data to display', 'message to show when no visits data available', 'affiliate-for-woocommerce' ); ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
	<?php if ( ! empty( $table_footer ) && true === $table_footer ) { ?>
		<tfoot>
			<tr>
				<td colspan="<?php echo esc_attr( $visits_colspan ); ?>">
					<div class="afwc-table-footer-container">
						<a class="afwc-back-button-wrapper" href="<?php echo esc_attr( esc_url( $dashboard_link ) ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
							</svg>
							<span><?php echo esc_html_x( 'back to dashboard', 'Link to affiliate my account dashboard', 'affiliate-for-woocommerce' ); ?></span>
						</a>
						<?php if ( ! empty( $visits_data['total_count'] ) && ! empty( $visits_data ) && ( intval( $visits_data['total_count'] ) > count( $visits_data ) ) ) { ?>
							<span class="afwc-visits-load-more-wrapper">
								<span class="afwc-loader" style="display: none;">
									<img src="<?php echo esc_url( WC()->plugin_url() ) . '/assets/images/wpspin-2x.gif'; ?>" class="afwc-table-loader" />
									<span><?php echo esc_html_x( 'Loading...', 'Visits table load more loading text', 'affiliate-for-woocommerce' ); ?></span>
								</span>
								<a id="afwc_load_more_visits" class="afwc-load-more-text" data-max_record="<?php echo esc_attr( $visits_data['total_count'] ); ?>">
									<span><?php echo esc_html_x( 'Load more', 'Visits table load more link text in my account', 'affiliate-for-woocommerce' ); ?></span>
								</a>
								<span class="afwc-no-load-more-text" style="display: none;"><?php echo esc_html_x( 'No more data to load', 'Text for no data to load', 'affiliate-for-woocommerce' ); ?></span>
							</span>
						<?php } ?>
					</div>
				</td>
			</tr>
		</tfoot>
	<?php } ?>
</table>
<?php
