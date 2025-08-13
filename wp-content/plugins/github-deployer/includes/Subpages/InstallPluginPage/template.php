<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php 
if ( isset( $install_result ) ) {
    ?>
	<?php 
    if ( !is_wp_error( $install_result ) ) {
        ?>
		<div class="updated">
			<p>
				<?php 
        echo esc_attr__( 'Plugin was successfully installed', 'github-deployer' );
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
echo esc_attr__( 'Install Plugin', 'github-deployer' );
?></h1>
	<form class="gd_install_package_form" method="post" action="">
		<input type="hidden" name="<?php 
echo esc_attr( GD_SLUG . '_install_package_submitted' );
?>" value="1">
		<input type="hidden" name="package_type" value="plugin">

		<?php 
wp_nonce_field( GD_SLUG . '_install_package_form', GD_SLUG . '_nonce' );
?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<?php 
echo esc_attr__( 'Provider Type', 'github-deployer' );
?>
				</th>
				<td>
					<select name='provider_type'>
						<option value="" selected disabled><?php 
echo esc_attr__( 'Choose a provider', 'github-deployer' );
?></option>
						<?php 
foreach ( \GithubDeployer\Helper::available_providers() as $provider_id => $name ) {
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
					<input type="url" class="regular-text code" name="repository_url" value="" />
					<p class="description gd_repo_url_description gd_hidden" id="bitbucket-repo-url-description"><?php 
echo esc_attr__( 'Example: https://bitbucket.org/owner/wordpress-plugin-name', 'github-deployer' );
?></p>
					<p class="description gd_repo_url_description gd_hidden" id="github-repo-url-description"><?php 
echo esc_attr__( 'Example: https://github.com/owner/wordpress-plugin-name', 'github-deployer' );
?></p>
					<p class="description gd_repo_url_description gd_hidden" id="gitea-repo-url-description"><?php 
echo esc_attr__( 'Example: https://gitea.com/owner/wordpress-plugin-name', 'github-deployer' );
?></p>
					<p class="description gd_repo_url_description gd_hidden" id="gitlab-repo-url-description"><?php 
echo esc_attr__( 'Example: https://gitlab.com/owner/wordpress-plugin-name', 'github-deployer' );
?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php 
echo esc_attr__( 'Branch', 'github-deployer' );
?> <span class="dashicons dashicons-randomize"></span></th>
				<td>
					<input type="text" class="" placeholder="master" name="repository_branch" value="" />
					<p class="description"><?php 
echo esc_attr__( 'default is "master"', 'github-deployer' );
?></p>
				</td>
			</tr>

			<?php 
$private_repository_row_class = 'free';
?>

			<tr valign="top" class="gd_hidden gd_is_private_repository_row <?php 
echo esc_attr( $private_repository_row_class );
?>">
				<th scope="row">
					<?php 
echo esc_attr__( 'Is Private Repository', 'github-deployer' );
?>

					<?php 
?>
						<br>
						<small><?php 
echo esc_attr__( '[Available in PRO version]', 'github-deployer' );
?></small>
					<?php 
?>

					<span class="dashicons dashicons-lock"></span>
				</th>
				<td><input type="checkbox" name="is_private_repository" value="1"></td>
			</tr>

			<?php 
?>
		</table>
		<div class="submit">
			<input type="submit" class="button-primary" value="<?php 
echo esc_attr__( 'Install Plugin', 'github-deployer' );
?>" /><br><br>
			<p class="description"><i><?php 
echo esc_attr__( 'Note that if a plugin with specified slug is already installed, this action will overwrite the already existing plugin.', 'github-deployer' );
?><i></p>
		</div>
	</form>
</div>
