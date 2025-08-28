<?php
namespace Mobilo\WpTheme\Interfaces;

defined('ABSPATH') || exit;
interface HookableInterface
{
    public function addFilter($hook, $callback, $priority = 10, $acceptedArgs = 1);
    public function addAction($hook, $callback, $priority = 10, $acceptedArgs = 1);
}
