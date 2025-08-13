<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_list  = $data_manager->get_theme_list();
$plugin_list = $data_manager->get_plugin_list();
?>

<div class="wrap">
	<h1><?php echo esc_attr__( 'Dashboard', 'github-deployer' ); ?></h1>

	<?php require_once 'partials/_themes.php'; ?>
	<hr>
	<!-- TODO: Uncomment this when plugin support is added -->
	<!-- <?php require_once 'partials/_plugins.php'; ?> -->
</div>
