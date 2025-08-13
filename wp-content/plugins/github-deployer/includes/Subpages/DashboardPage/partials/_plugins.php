<?php
use GithubDeployer\ApiRequests\PackageUpdate;
use GithubDeployer\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h3>
	<span class="dashicons dashicons-admin-plugins" style="color: #0073aa;"></span>
	<?php echo esc_attr__( 'Installed Plugins', 'github-deployer' ); ?> (<?php echo count( $plugin_list ); ?>)
</h3>

<?php if ( empty( $plugin_list ) ) : ?>
	<?php
		$plugin_istall_page_url = \GithubDeployer\Helper::install_plugin_url();
	?>
	<div><?php echo esc_attr__( 'No plugins installed yet... Go and install one.', 'github-deployer' ); ?></div>
	<div class="gd_mt_10" >
		<a href="<?php echo esc_url( $plugin_istall_page_url ); ?>" class="button">
			<?php echo esc_attr__( 'Install Plugin', 'github-deployer' ); ?>
		</a>
	</div>
<?php endif; ?>

<div class="gd_package_boxes">
	<?php foreach ( $plugin_list as $plugin_info ) : ?>
		<?php $endpoint_url = PackageUpdate::package_update_url( $plugin_info['slug'], 'plugin' ); ?>
		<div class="gd_package_box">
			<div>
				<h3>
					<?php
					$provider_logo_url = plugin_dir_url( GD_FILE ) . 'assets/img/' . $plugin_info['provider'] . '-logo.svg';
					?>
					<img src="<?php echo esc_url( $provider_logo_url ); ?>" alt="<?php echo esc_attr( GithubDeployer\Helper::available_providers()[ $plugin_info['provider'] ] ); ?>" >
					<?php echo esc_attr( $plugin_info['slug'] ); ?>
					<?php if ( $plugin_info['is_private_repository'] ) : ?>
						<i class="dashicons dashicons-lock" title="<?php echo esc_attr__( 'Private Repo', 'github-deployer' ); ?>"></i>

						<?php if ( 'github' === $plugin_info['provider'] ) : ?>
							<?php $pat_token_type = Helper::is_github_pat_token( $plugin_info['options']['access_token'] ); ?>
							<?php if ( $pat_token_type ) : ?>
								<i class="dashicons dashicons-post-status" title="<?php echo $pat_token_type ? esc_attr__( 'Fine-grained personal access token used', 'github-deployer' ) : ''; ?>"></i>
							<?php endif; ?>
						<?php endif; ?>

					<?php else : ?>
						<i class="dashicons dashicons-unlock" title="<?php echo esc_attr__( 'Public Repo', 'github-deployer' ); ?>"></i>
					<?php endif; ?>
				</h3>
				<ul>
					<li>
						<i class="dashicons dashicons-randomize" title="<?php echo esc_attr__( 'Branch', 'github-deployer' ); ?>"></i>
						<code><?php echo esc_attr( $plugin_info['branch'] ); ?></code>
					</li>
					<li>
						<pre style="white-space: normal;"><?php echo esc_attr( $plugin_info['repo_url'] ); ?></pre>
					</li>
					<li>
						<a href="#" data-show-ptd-btn >
							<?php echo esc_attr__( '» Show Push-to-Deploy URL', 'github-deployer' ); ?>
						</a>
						<div class="gd_package_box_action">
							<input type="url" disabled value="<?php echo esc_url( $endpoint_url ); ?>">
							<a data-copy-url-btn="<?php echo esc_url( $endpoint_url ); ?>" class="button" href="#" aria-label="<?php echo esc_attr__( 'Copy URL', 'github-deployer' ); ?>">
								<span class="dashicons dashicons-admin-page"></span>
								<span class="text" ><?php echo esc_attr__( 'Copy URL', 'github-deployer' ); ?></span>
							</a>
						</div>
					</li>
					<li class="gd_package_box_buttons" >
						<button data-package-type="plugin"
						data-trigger-ptd-btn="<?php echo esc_url( $endpoint_url ); ?>"
						title="<?php echo esc_attr__( 'Remove', 'github-deployer' ); ?>"
						class="button button-primary update-package-btn" >
							<span class="dashicons dashicons-update"></span>&nbsp;
							<span class="text" ><?php echo esc_attr__( 'Update Plugin', 'github-deployer' ); ?></span>
						</button>

						<?php
						$plugins_url = is_multisite() ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );
						?>
						<a href="<?php echo esc_url( $plugins_url ); ?>"
							title="<?php echo esc_attr__( 'Remove', 'github-deployer' ); ?>"
							class="delete-theme button" ><span class="dashicons dashicons-trash"></span></a>
					</li>
				</ul>
			</div>
		</div>
	<?php endforeach; ?>
</div>
