<?php

  defined( 'ABSPATH' ) || exit;
add_action( 'init', 'stop_heartbeat', 1 );
function stop_heartbeat() {
wp_deregister_script('heartbeat');
}