<?php
function mobilo_optimize_scripts()
{
    wp_enqueue_style("mobilo-optimize-style", get_stylesheet_uri());
}
add_action("wp_enqueue_scripts", "mobilo_optimize_scripts");
