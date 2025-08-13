<?php

use GithubDeployer\Helper;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( isset( $install_result ) ) {
    ?>
	<?php 
    if ( !is_wp_error( $install_result ) ) {
        ?>
		<div class="updated">
			<p>
				<?php 
        echo esc_attr__( 'Theme was successfully installed', 'github-deployer' );
        ?>
			</p>
		</div>
	<?php 
    } else {
        ?>
		<div class="error">
			<p>
				<!-- <strong><?php 
        echo esc_attr( $install_result->get_error_code() );
        ?></strong> -->
				<?php 
        echo esc_attr( $install_result->get_error_message() );
        ?>
			</p>
		</div>
	<?php 
    }
}
?>

<div class="wrap">
	<h1><?php 
echo esc_attr__( 'Install Theme', 'github-deployer' );
?></h1>
	<form class="gd_install_package_form" method="post" action="">
		<input type="hidden" name="<?php 
echo esc_attr( GD_SLUG . '_install_package_submitted' );
?>" value="1">
		<input type="hidden" name="package_type" value="theme">

		<?php 
wp_nonce_field( GD_SLUG . '_install_package_form', GD_SLUG . '_nonce' );
?>
		
		<!-- Repository Information Group -->
		<div class="gd_repo_group">
			<h3><?php echo esc_attr__( 'Repository Information', 'github-deployer' ); ?></h3>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php 
echo esc_attr__( 'Provider Type', 'github-deployer' );
?></th>
					<td>
						<select name='provider_type' id="provider_type">
							<option value="" selected disabled><?php 
echo esc_attr__( 'Choose a provider', 'github-deployer' );
?></option>
							<?php 
foreach ( Helper::available_providers() as $provider_id => $name ) {
    ?>
								<option value="<?php 
    echo esc_attr( $provider_id );
    ?>"><?php 
    echo esc_attr( $name );
    ?></option>
							<?php 
}
?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php 
echo esc_attr__( 'Repository URL', 'github-deployer' );
?></th>
					<td>
						<div class="gd_repo_url_container">
							<input type="url" class="regular-text code" name="repository_url" id="repository_url" value="" />
							<button type="button" class="button gd_validate_btn" id="validate_repo_btn">
								<span class="dashicons dashicons-search"></span>
								<?php echo esc_attr__( 'Validate', 'github-deployer' ); ?>
							</button>
						</div>
						<div class="gd_validation_result" id="validation_result"></div>
						<p class="description gd_repo_url_description gd_hidden" id="bitbucket-repo-url-description"><?php 
echo esc_attr__( 'Example: https://bitbucket.org/owner/wordpress-theme-name', 'github-deployer' );
?></p>
						<p class="description gd_repo_url_description gd_hidden" id="github-repo-url-description"><?php 
echo esc_attr__( 'Example: https://github.com/owner/wordpress-theme-name', 'github-deployer' );
?></p>
						<p class="description gd_repo_url_description gd_hidden" id="gitea-repo-url-description"><?php 
echo esc_attr__( 'Example: https://gitea.com/owner/wordpress-theme-name', 'github-deployer' );
?></p>
						<p class="description gd_repo_url_description gd_hidden" id="gitlab-repo-url-description"><?php 
echo esc_attr__( 'Example: https://gitlab.com/owner/wordpress-theme-name', 'github-deployer' );
?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php 
echo esc_attr__( 'Is Private Repository', 'github-deployer' );
?> <span class="dashicons dashicons-lock" style="color: #0073aa;"></span>
					</th>
					<td>
						<input type="checkbox" name="is_private_repository" id="is_private_repository" value="1">
						<label for="is_private_repository"><?php echo esc_attr__( 'This is a private repository', 'github-deployer' ); ?></label>
					</td>
				</tr>
				<tr valign="top" class="gd_access_token_row gd_hidden">
					<th scope="row"><?php 
echo esc_attr__( 'Access Token', 'github-deployer' );
?></th>
					<td>
						<input type="text" class="regular-text" placeholder="***" name="access_token" id="access_token" value="" />
						<p class="description"><?php 
echo esc_attr__( 'Enter your access token for private repositories', 'github-deployer' );
?></p>
					</td>
				</tr>
			</table>
		</div>

		<!-- Deployment Options Group -->
		<div class="gd_deployment_group gd_hidden" id="deployment_options">
			<h3><?php echo esc_attr__( 'Deployment Options', 'github-deployer' ); ?></h3>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php 
echo esc_attr__( 'Branch', 'github-deployer' );
?> <span class="dashicons dashicons-randomize"></span></th>
					<td>
						<select name="repository_branch" id="repository_branch">
							<option value=""><?php echo esc_attr__( 'Loading branches...', 'github-deployer' ); ?></option>
						</select>
						<button type="button" class="button gd_fetch_data_btn" id="fetch_repo_data_btn">
							<span class="dashicons dashicons-update"></span>
							<?php echo esc_attr__( 'Fetch Repository Data', 'github-deployer' ); ?>
						</button>
						<p class="description"><?php 
echo esc_attr__( 'Select the branch to deploy from', 'github-deployer' );
?></p>
					</td>
				</tr>
				<tr valign="top" class="gd_commit_selection gd_hidden">
					<th scope="row"><?php 
echo esc_attr__( 'Specific Commit (Optional)', 'github-deployer' );
?> <span class="dashicons dashicons-git-commit"></span></th>
					<td>
						<select name="specific_commit" id="specific_commit">
							<option value=""><?php echo esc_attr__( 'Use latest commit', 'github-deployer' ); ?></option>
						</select>
						<p class="description"><?php 
echo esc_attr__( 'Select a specific commit to deploy (optional)', 'github-deployer' );
?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="submit">
			<input type="submit" class="button-primary" value="<?php 
echo esc_attr__( 'Install Theme', 'github-deployer' );
?>" id="install_theme_btn" disabled />
			<br><br>
			<p class="description"><i><?php 
echo esc_attr__( 'Note that if a theme with specified slug is already installed, this action will overwrite the already existing theme.', 'github-deployer' );
?><i></p>
		</div>
	</form>
</div>

<!-- Add nonces for AJAX -->
<script type="text/javascript">
var gd_validation_nonce = '<?php echo wp_create_nonce( 'gd_repo_validation' ); ?>';
var gd_repo_data_nonce = '<?php echo wp_create_nonce( 'gd_repo_data' ); ?>';
</script>
