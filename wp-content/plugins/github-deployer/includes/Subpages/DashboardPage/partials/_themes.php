<?php
use GithubDeployer\ApiRequests\PackageUpdate;
use GithubDeployer\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h3>
	<span class="dashicons dashicons-admin-appearance"></span>
	<?php echo esc_attr__( 'Installed Themes', 'github-deployer' ); ?> (<?php echo count( $theme_list ); ?>)
</h3>

<?php if ( empty( $theme_list ) ) : ?>
	<?php
		$theme_istall_page_url = \GithubDeployer\Helper::install_theme_url();
	?>
	<div><?php echo esc_attr__( 'No themes installed yet...  Go and install one.', 'github-deployer' ); ?></div>
	<div class="gd_mt_10" >
		<a href="<?php echo esc_url( $theme_istall_page_url ); ?>" class="button">
			<?php echo esc_attr__( 'Install Theme', 'github-deployer' ); ?>
		</a>
	</div>
<?php endif; ?>

<div class="gd_package_boxes">
	<?php foreach ( $theme_list as $theme_info ) : ?>
		<?php $endpoint_url = PackageUpdate::package_update_url( $theme_info['slug'], 'theme' ); ?>
		<div class="gd_package_box">
			<div>
				<h3>
					<?php
					$provider_logo_url = plugin_dir_url( GD_FILE ) . 'assets/img/' . $theme_info['provider'] . '-logo.svg';
					?>
					<img src="<?php echo esc_url( $provider_logo_url ); ?>" alt="<?php echo esc_attr( GithubDeployer\Helper::available_providers()[ $theme_info['provider'] ] ); ?>" >
					<?php echo esc_attr( $theme_info['slug'] ); ?>
					<?php if ( $theme_info['is_private_repository'] ) : ?>

						<i class="dashicons dashicons-lock" title="<?php echo esc_attr__( 'Private Repo', 'github-deployer' ); ?>"></i>

						<?php if ( 'github' === $theme_info['provider'] ) : ?>
							<?php $pat_token_type = Helper::is_github_pat_token( $theme_info['options']['access_token'] ); ?>
							<?php if ( $pat_token_type ) : ?>
								<i class="dashicons dashicons-post-status" title="<?php echo $pat_token_type ? esc_attr__( 'Fine-grained personal access token used', 'github-deployer' ) : ''; ?>"></i>
							<?php endif; ?>
						<?php endif; ?>

					<?php else : ?>
						<i class="dashicons dashicons-unlock" title="<?php echo esc_attr__( 'Public Repo', 'github-deployer' ); ?>"></i>
					<?php endif; ?>
					<?php
					$current_theme = wp_get_theme();
					if ( $current_theme->get_template() === $theme_info['slug'] ) :
						?>
						| <span><?php echo esc_attr__( 'Active', 'github-deployer' ); ?></span>
						<?php
					endif;
					?>
				</h3>
				<ul>
					<li>
					<?php echo esc_attr__( 'Branch', 'github-deployer' ); ?>
						<i class="dashicons dashicons-randomize" title="<?php echo esc_attr__( 'Branch', 'github-deployer' ); ?>"></i>:
						<code><?php echo esc_attr( $theme_info['branch'] ); ?></code>
					</li>
					<li>
						<pre style="white-space: normal;"><?php echo esc_attr( $theme_info['repo_url'] ); ?></pre>
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
						<button data-package-type="theme"
						data-package-slug="<?php echo esc_attr( $theme_info['slug'] ); ?>"
						data-trigger-ptd-btn="<?php echo esc_url( $endpoint_url ); ?>"
						title="<?php echo esc_attr__( 'Update Theme', 'github-deployer' ); ?>"
						class="button button-primary update-package-btn" >
							<span class="dashicons dashicons-update"></span>&nbsp;
							<span class="text" ><?php echo esc_attr__( 'Update Theme', 'github-deployer' ); ?></span>
						</button>

						<button data-package-type="theme"
						data-package-slug="<?php echo esc_attr( $theme_info['slug'] ); ?>"
						title="<?php echo esc_attr__( 'Update with Options', 'github-deployer' ); ?>"
						class="button update-package-btn gd-update-with-options-btn" >
							<!-- <span class="dashicons dashicons-admin-tools"></span>&nbsp; -->
							<span class="dashicons dashicons-update"></span>&nbsp;

							<span class="text" ><?php echo esc_attr__( 'Update with Options', 'github-deployer' ); ?></span>
						</button>

						<?php
						$themes_url = is_multisite() ? network_admin_url( 'themes.php' ) : admin_url( 'themes.php' );
						$theme_url  = add_query_arg( 'theme', $theme_info['slug'], $themes_url );
						?>
						<a href="<?php echo esc_url( $theme_url ); ?>"
							title="<?php echo esc_attr__( 'Remove', 'github-deployer' ); ?>"
							class="delete-theme button" ><span class="dashicons dashicons-trash"></span></a>
					</li>
				</ul>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<!-- Enhanced Update Modal -->
<div id="gd-update-modal" class="gd-modal">
	<div class="gd-modal-content">
		<div class="gd-modal-header">
			<h3><?php echo esc_attr__( 'Update Theme with Options', 'github-deployer' ); ?></h3>
			<span class="gd-modal-close">&times;</span>
		</div>
		<div class="gd-modal-body">
			<div class="gd-update-loading">
				<?php echo esc_attr__( 'Loading repository data...', 'github-deployer' ); ?>
			</div>
			
			<div class="gd-update-options gd_hidden">
				<div class="gd-update-section">
					<h4><?php echo esc_attr__( 'Current Branch', 'github-deployer' ); ?></h4>
					<p class="gd-current-branch"></p>
				</div>
				
				<div class="gd-update-section">
					<h4><?php echo esc_attr__( 'Select Branch', 'github-deployer' ); ?></h4>
					<select id="gd-update-branch" class="gd-update-select">
						<option value=""><?php echo esc_attr__( 'Loading branches...', 'github-deployer' ); ?></option>
					</select>
				</div>
				
				<div class="gd-update-section gd-commit-section gd_hidden">
					<h4><?php echo esc_attr__( 'Select Specific Commit (Optional)', 'github-deployer' ); ?></h4>
					<select id="gd-update-commit" class="gd-update-select">
						<option value=""><?php echo esc_attr__( 'Use latest commit', 'github-deployer' ); ?></option>
					</select>
					<p class="description"><?php echo esc_attr__( 'Leave empty to use the latest commit from the selected branch', 'github-deployer' ); ?></p>
				</div>
			</div>
			
			<div class="gd-update-error gd_hidden">
				<span class="dashicons dashicons-warning"></span>
				<span class="gd-error-message"></span>
			</div>
		</div>
		<div class="gd-modal-footer">
			<button type="button" class="button gd-modal-cancel"><?php echo esc_attr__( 'Cancel', 'github-deployer' ); ?></button>
			<button type="button" class="button button-primary gd-update-confirm" disabled><?php echo esc_attr__( 'Update Theme', 'github-deployer' ); ?></button>
		</div>
	</div>
</div>

<!-- Add nonces for AJAX -->
<script type="text/javascript">
var gd_update_repo_data_nonce = '<?php echo wp_create_nonce( 'gd_update_repo_data' ); ?>';
var gd_update_package_nonce = '<?php echo wp_create_nonce( 'gd_update_package' ); ?>';
</script>
